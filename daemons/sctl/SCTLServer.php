<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/04/18
 * Time: 12:26
 */

namespace wfw\daemons\sctl;

use wfw\daemons\sctl\conf\SCTLConf;
use wfw\daemons\sctl\errors\SCTLFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Serveur sctl
 */
final class SCTLServer implements ISCTLServer
{
    public const SCTL = 'sctl';
    /**
     * @var SCTLConf $_conf
     */
    private $_conf;

    /**
     * @var resource $_semFile
     */
    private $_semFile;

    /**
     * @var UUID $_pwd
     */
    private $_pwd;

    /**
     * @var resource $_socket
     */
    private $_socket;

    /**
     * @var ISocketProtocol $_protocol
     */
    private $_protocol;

    /**
     *  Timeout des socket sur RCV et SND
     * @var array $_socketTimeout
     */
    private $_socketTimeout = array("sec"=>10,"usec"=>0);

    /**
     * SCTLServer constructor.
     *
     * @param SCTLConf        $conf Configuration du serveur
     * @param ISocketProtocol $protocol Protocol de communication client/serveur
     * @throws IllegalInvocation
     */
    public function __construct(SCTLConf $conf,ISocketProtocol $protocol){
        $this->_conf = $conf;
        $semFile = $conf->getWorkingDir()."/sem_file.semaphore";
        if(!file_exists($semFile))
            touch($semFile);

        $id = ftok($semFile,'A');
        $this->_semFile  = sem_get($id,1,0666,0);
        $res = sem_acquire($this->_semFile ,true);

        if(!$res)
            throw new IllegalInvocation("Another instance of sctl is already running in ".dirname($semFile));
        else{
            $this->_protocol = $protocol;
            $this->_pwd = new UUID(UUID::V4);
            file_put_contents($conf->getWorkingDir()."/sctl.pid",getmypid());
            $this->exec('chmod 0555 "'.$conf->getWorkingDir()."/sctl.pid".'"');
            file_put_contents($conf->getWorkingDir()."/auth.pwd",$this->_pwd);
            $user = $conf->getUser();
            $this->exec("chown $user \"".$conf->getWorkingDir()."/auth.pwd\"");
            $this->exec("chmod 0500 \"".$conf->getWorkingDir()."/auth.pwd\"");

            $socketPath = $conf->getWorkingDir()."/sctl.socket";
            $this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
            if(file_exists($socketPath)){
                unlink($socketPath);
            }
            socket_bind($this->_socket,$socketPath);
            $this->exec("chmod 0666 \"$socketPath\"");
            socket_listen($this->_socket);
        }
    }

    public function start(): void
    {
        while(true){
            $socket = socket_accept($this->_socket);
            $this->configureSocket($socket);
            try{
                $this->process($socket);
            }catch(\Exception $e){
                try{
                    $this->write($socket,[
                        "code" => 6,
                        "message" => $e->getMessage()." ".$e->getFile()." ".$e->getLine().$e->getTraceAsString()
                    ]);
                }catch(\Exception $err){
                    error_log(
                        "Error : ".$e.PHP_EOL.$err.PHP_EOL,
                        3,
                        $this->_conf->getWorkingDir()."/errors.log"
                    );
                }
            }
        }
    }

    /**
     * @param resource $socket Socket à traiter
     */
    private function process($socket):void{
        $command = $this->getAndSanitizeCommand($socket);
        if(!is_null($command)){
            $sctl = false;
            $cmd = $command['cmd'];
            $daemons = $command['daemons'];
            if($command['all']) $daemons = $this->_conf->getDaemons();
            if(count($daemons) === 0){
                $this->write($socket,[
                    "code" => 5,
                    "message" => "Nothing to do !"
                ]);
                socket_close($socket);
            }else{
                $outputs = [];
                $errors=[];
                foreach($daemons as $d){
                    if($d === self::SCTL){
                        $sctl = true;
                        if($cmd==="status")
                            $outputs[] = $this->exec("systemctl $cmd wfw-$d.service",true);
                    }else{
                        try{
                            $outputs[] = $this->exec("systemctl $cmd wfw-$d.service",$cmd==="status");
                        }catch(SCTLFailure $e){
                            $errors[]=$e->getMessage();
                        }
                    }
                }
                if(count($errors) > 0){
                    $this->write($socket,[
                        "code" => 7,
                        "message" => "Partial execution, some commands failed : ".PHP_EOL
                            .implode(PHP_EOL,$errors)
                    ]);
                    socket_close($socket);
                }else{
                    if($sctl){
                        if($cmd==="restart"){
                            $this->write($socket,[
                                "code" => 0,
                                "message" => "All commands sended, sctl will now restart..."
                            ]);
                            socket_close($socket);
                            $this->exec("nohup systemctl restart wfw-sctl.service > \"".
                                $this->_conf->getWorkingDir()."/errors.log\" 2>&1 &");
                        }else if(!$command['all'] && $cmd!=="status"){
                            $this->write($socket,[
                                "code" => -1,
                                "message" => "Warning : only restart command is allowed for sctl !"
                            ]);
                            socket_close($socket);
                        }else{
                            $this->write($socket,[
                                "code" => 0,
                                "message" => "Done",
                                "res" =>$outputs
                            ]);
                            socket_close($socket);
                        }
                    }else{
                        $this->write($socket,[
                            "code" => 0,
                            "message" => "Done",
                            "res" => $outputs
                        ]);
                        socket_close($socket);
                    }
                }
            }
        }
    }

    /**
     * @param resource $socket Socket de destination
     * @param mixed $data   Données à écrire
     */
    private function write($socket,$data):void{
        $this->_protocol->write($socket,json_encode($data));
    }

    /**
     * Obtient la commande du client, la parse et verifie que tout correspond.
     * Si les arguments attendus sont présents et corrects, retourne un tableau
     * contenant trois clés : "cmd" => restart|start|stop, "all" => bool, "daemons" => string[]
     * contenant uniquement les daemons que le sctl serveur peut gérer.
     * Si les données reçues sont mal formattées, retourne null et ferme la connexion de la socket.
     * @param $socket
     * @return array|null
     */
    private function getAndSanitizeCommand($socket):?array{
        $command = json_decode($this->_protocol->read($socket),true);
        if(json_last_error() !== JSON_ERROR_NONE){
            $this->write($socket,[
                "code" => 1,
                "message" => "Unreadable json command : ".json_last_error()
                    ." - ".json_last_error_msg()
            ]);
            socket_close($socket);
        }else{
            if(!isset($command["pwd"]) || isset($command["pwd"]) && $command["pwd"] === $this->_pwd){
                $this->write($socket,[
                    "code" => 2,
                    "message" => "Access denied : wrong pwd"
                ]);
                socket_close($socket);
            }else{
                $cmd = $command['cmd'] ?? null;
                if(is_null($cmd) || is_string($cmd) && !preg_match("/^(start|stop|restart|status)$/",$cmd)){
                    $this->write($socket,[
                        "code" => 3,
                        "message" => "Invalid command $cmd ! Only start|stop|restart|status are accepted !"
                    ]);
                    socket_close($socket);
                }else{
                    $all = ((isset($command["all"]))
                        ? filter_var($command["all"],FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)
                        : false);
                    $daemons = $command['daemons'] ?? [];
                    if(is_array($daemons)){
                        $valid = true;
                        foreach($daemons as $d){ if(!is_string($d)){ $valid = false; break; } }
                        if($valid){
                            $ds = array_intersect($this->_conf->getDaemons(),$daemons);
                            return [
                                "cmd" => $cmd,
                                "all" => $all,
                                "daemons" => $ds
                            ];
                        }else{
                            $this->write($socket,[
                                "code" => 4,
                                "message" => "Invalid daemon arg : must be a string array !"
                            ]);
                        }
                    }else{
                        $this->write($socket,[
                            "code" => 4,
                            "message" => "Invalid daemon arg : must be a string array !"
                        ]);
                        socket_close($socket);
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string $cmd Commande à executer
     * @param bool   $disableFailure Desactive la levée d'exception en cas de non 0
     * @return array
     * @throws SCTLFailure
     */
    private function exec(string $cmd,bool $disableFailure = false):array{
        $outputs = []; $res = null;
        exec($cmd." 2>&1",$outputs,$res);
        if($res !== 0 && !$disableFailure)
            throw new SCTLFailure(
                "Error trying to exec '$cmd'".
                " code $res, outputs : ".implode("\n",$outputs)
            );
        return $outputs;
    }

    /**
     *  Configure une socket
     * @param resource $socket Socket à configurer
     */
    private function configureSocket($socket){
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
        socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
    }

    private function closeConnection():void{
        if(!is_null($this->_socket)){
            socket_close($this->_socket);
            unlink($this->_conf->getWorkingDir()."/sctl.socket");
        }
    }
    /**
     * @param int $signal Signal ayant éteint le serveur
     */
    public function shutdown(int $signal): void
    {
        $this->closeConnection();

        sem_release($this->_semFile);
        if(file_exists($this->_conf->getWorkingDir()."/sctl.pid"))
            unlink($this->_conf->getWorkingDir()."/sctl.pid");
        if(file_exists($this->_conf->getWorkingDir()."/auth.pwd"))
            unlink($this->_conf->getWorkingDir()."/auth.pwd");
        if(file_exists($this->_conf->getWorkingDir()."/sem_file.semaphore"))
            unlink($this->_conf->getWorkingDir()."/sem_file.semaphore");

        exit(0);
    }
}