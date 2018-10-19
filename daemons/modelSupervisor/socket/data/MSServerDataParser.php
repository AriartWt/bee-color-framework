<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/01/18
 * Time: 11:18
 */

namespace wfw\daemons\modelSupervisor\socket\data;

use wfw\daemons\modelSupervisor\server\components\responses\IMSServerComponentResponse;
use wfw\daemons\modelSupervisor\server\IMSServerInternalRequest;
use wfw\daemons\modelSupervisor\server\IMSserverMessage;
use wfw\daemons\modelSupervisor\server\IMSServerRequest;
use wfw\daemons\modelSupervisor\server\IMSServerResponse;
use wfw\daemons\modelSupervisor\socket\data\errors\MSServerDataParsingFailure;

use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\data\IDataParser;
use wfw\engine\lib\PHP\types\Type;

/**
 * Parser de données des messages gérés par le MSServer.
 */
final class MSServerDataParser implements IDataParser
{
    private const SEPARATOR = "@";
    private const ARGS_SEPARATOR = "/";

    private $_serializer;

    /**
     * KVSDataParser constructor.
     *
     * @param ISerializer $serializer Objet permettant la serialisation/deserialisation
     */
    public function __construct(ISerializer $serializer)
    {
        $this->_serializer = $serializer;
    }

    /**
     * @param string $data Parse les données reçues d'une socket.
     *
     * @return mixed Resultat du parsing
     */
    public function parseData(string $data):MSServerDataParserResult
    {
        $d = explode(self::SEPARATOR,$data,3);

        if(count($d)===3){
            $class = $d[0];
            $args = $d[1];
            $decodedData = $this->decodeData($d[2]);
            if(count($decodedData)>1){
                $params = $decodedData[0];
                $finalData = $decodedData[1];
            }else{
                $params = "";
                $finalData = $decodedData[0];
            }

            $args = explode(self::ARGS_SEPARATOR,$args);

            return new MSServerDataParserResult(
                $class,
                $finalData,
                $args[0]??"",
                $args[1]??"",
                $args[2]??"",
                $args[3]??"",
                $params);
        }else{
            throw new MSServerDataParsingFailure("Invalid data  : $data");
        }
    }

    /**
     * @param mixed $message Données à linéariser pour être envoyées dans une socket.
     *
     * @return string
     */
    public function lineariseData($message): string
    {
        $res = get_class($message).self::SEPARATOR;
        if($message instanceof IMSServerInternalRequest){
            $res.=self::ARGS_SEPARATOR;//skip sessId
            $res.=$message->getQueryId().self::ARGS_SEPARATOR;//set queryId
            $res.=$message->getServerKey().self::ARGS_SEPARATOR;//set serverKey
            $res.=$message->getUserName();//set userName
            $res.=self::SEPARATOR;
            $res.= $this->formatData([$message->getParams(),$message->getData()]);
        }else if($message instanceof IMSServerRequest){
            $res.=$message->getSessionId().self::ARGS_SEPARATOR; //set sessId
            if($message instanceof IMSServerComponentResponse){
                $res.=$message->getQueryId(); //set queryId
                $message = $this->formatData([$this->lineariseData($message->getResponse())]);
            }
            $res.=self::ARGS_SEPARATOR; //skip serverKey
            $res.=self::ARGS_SEPARATOR; //skip userName
            $res.=self::SEPARATOR;
            if(is_string($message)){
                $res.=$message;
            }else{
                $res.=$this->lineariseMessage($message);
            }
        }else if($message instanceof IMSServerResponse){
            $res.=self::ARGS_SEPARATOR;//skip sessId
            $res.=self::ARGS_SEPARATOR;//skip serverKey
            $res.=self::ARGS_SEPARATOR;//skip username
            $res.=self::SEPARATOR;
            $res.=$this->lineariseMessage($message);
        }else{
            throw new \InvalidArgumentException("data have to be an instanceof of ".IMSServerRequest::class." but ".(new Type($message))->get()." given !");
        }
        return $res;
    }

    /**
     * Si getData() et getParams() de $message renvoient null, alors on renvoie la serialisation du message.
     * Cela permet, si la requpete contient une ou des données trs volumieuses sérialisée ou à sérialiser, de
     * les extraire de la requête.
     *
     * @param IMSserverMessage $message Message à linéariser
     *
     * @return null|string message linéarisé.
     */
    private function lineariseMessage(IMSserverMessage $message):?string{
        $params = $message->getParams();
        $data = $message->getData();

        if(is_null($data) && is_null($params)){
            return $this->formatData([$this->_serializer->serialize($message)]);
        }else{
            $toFormat = [];
            if(!is_null($params)){
                $toFormat[] = $this->_serializer->serialize($params);
            }
            $toFormat[] = $data;
            return $this->formatData($toFormat);
        }
    }

    /**
     * @param string[] $data Formate des données pour la linearisation.
     * @return string
     */
    private function formatData(array $data):string{
        $res = '';
        foreach($data as $d){
            $res.=strlen($d).self::ARGS_SEPARATOR.$d;
        }
        return $res;
    }

    /**
     * @param string $data Donnée à décoder (encodées avec formatData
     * @return string[]
     */
    private function decodeData(string $data):array{
        $totalSize = strlen($data);
        if($totalSize>0){
            $tmp = explode("/",$data,2);
            if(count($tmp) === 2){
                $length = intval($tmp[0]);
                if($length>0){
                    $res = [substr($tmp[1],0,$length)];
                    if($totalSize > $length+strlen($tmp[0])+1){
                        $res = array_merge(
                            $res,
                            $this->decodeData(substr($data,$length+strlen($tmp[0])+1))
                        );
                    }
                    return $res;
                }else{
                    return [""];
                }
            }else{
                return [$data];
            }
        }else{
            return [""];
        }
    }
}