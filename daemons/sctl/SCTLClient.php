<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/04/18
 * Time: 13:45
 */

namespace wfw\daemons\sctl;

use wfw\daemons\sctl\conf\SCTLConf;
use wfw\daemons\sctl\errors\SCTLFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Client du daemon sctl
 */
final class SCTLClient implements ISCTLClient
{
    /**
     * @var SCTLConf $_conf
     */
    private $_conf;

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
     * SCTLClient constructor.
     *
     * @param SCTLConf        $conf Configurations du daemon sctl
     * @param ISocketProtocol $socketProtocol
     */
    public function __construct(SCTLConf $conf,ISocketProtocol $socketProtocol){
        $this->_conf = $conf;
        $this->_protocol = $socketProtocol;
    }

    /**
     *  Crée une socket et la paramètre avec le timeout défini par $this->_socketTiemout
     * @param string $addr
     *
     * @return resource
     */
    private function createClientSocket(string $addr){
        $socket = socket_create(AF_UNIX,SOCK_STREAM,0);
        $this->configureSocket($socket);
        socket_connect($socket,$addr);
        return $socket;
    }

    /**
     *  Configure une socket
     * @param resource $socket Socket à configurer
     */
    private function configureSocket($socket){
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
        socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
    }

    /**
     * @param string $cmd        Commande à execute (start/stop/restart)
     * @param bool   $all        True pour executer la commande sur tous les daemons
     * @param string ...$daemons Daemons impactés
     * @return mixed
     * @throws SCTLFailure
     */
    private function sendRequest(string $cmd, bool $all, string ...$daemons){
        $socket = $this->createClientSocket($this->_conf->getWorkingDir().'/sctl.socket');
        $this->_protocol->write($socket,json_encode([
            "cmd" => $cmd,
            "all" => $all,
            "daemons" => $daemons,
            "pwd" => file_get_contents($this->_conf->getWorkingDir()."/auth.pwd")
        ]));
        $data = json_decode($d = $this->_protocol->read($socket),true);
        socket_close($socket);
        if(json_last_error() !== JSON_ERROR_NONE)
            throw new SCTLFailure(
                "Unreadable response recieved (json error code ".json_last_error()
                ." : ".json_last_error_msg().") : '$d' ");
        else{
            if($data["code"] !== 0)
                throw new SCTLFailure(
                    "SCTL returned an error (code ".$data["code"].") ".$data["message"]);
        }
        return $data;
    }

    /**
     * Ordonne l'arrêt de tous les daemons
     */
    public function stopAll(): void
    {
        $this->sendRequest("stop",true);
    }

    /**
     * Ordonne le démarrage de tous les daemons
     */
    public function startAll(): void
    {
        $this->sendRequest("start",true);
    }

    /**
     * Ordonne le redémarrage de tous les daemons
     */
    public function restartAll(): void
    {
        $this->sendRequest("restart",true);
    }

    /**
     * @param string ...$daemons Liste des daemons à arrêter
     */
    public function stop(string ...$daemons): void
    {
        $this->sendRequest("stop",false,...$daemons);
    }

    /**
     * @param string ...$daemons Liste des daemons à démarrer
     */
    public function start(string ...$daemons): void
    {
        $this->sendRequest("start",false,...$daemons);
    }

    /**
     * @param string ...$daemons Liste des daemons à redemarrer
     */
    public function restart(string ...$daemons): void
    {
        $this->sendRequest("restart",false,...$daemons);
    }

    /**
     * @return array
     */
    public function statusAll(): array
    {
        $res =  $this->sendRequest("status",true);
        if(is_array($res)) return $res["res"] ?? [];
        else return [];
    }

    /**
     * @param string ...$daemons Liste des daemons pour lequels obtenir les status
     * @return array
     */
    public function status(string ...$daemons): array
    {
        $res = $this->sendRequest("status",false,...$daemons);
        if(is_array($res)) return $res["res"] ?? [];
        else return [];
    }
}