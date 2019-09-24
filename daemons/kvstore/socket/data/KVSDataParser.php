<?php
namespace wfw\daemons\kvstore\socket\data;

use wfw\daemons\kvstore\server\IKVSInternalRequest;
use wfw\daemons\kvstore\server\IKVSMessage;
use wfw\daemons\kvstore\server\requests\IKVSRequest;
use wfw\daemons\kvstore\server\responses\IKVSContainerResponse;
use wfw\daemons\kvstore\server\responses\IKVSResponse;

use wfw\daemons\kvstore\socket\data\errors\KVSDataParsingFailure;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\data\IDataParser;
use wfw\engine\lib\PHP\types\Type;

/**
 * Permet de parser/lineariser de requêtes du KVS.
 */
final class KVSDataParser implements IDataParser {
	private const SEPARATOR = "@";
	private const ARGS_SEPARATOR = "/";
	/** @var ISerializer $_serializer */
	private $_serializer;

	/**
	 * KVSDataParser constructor.
	 *
	 * @param ISerializer $serializer Objet permettant la serialisation/deserialisation
	 */
	public function __construct(ISerializer $serializer) {
		$this->_serializer = $serializer;
	}

	/**
	 * @param string $data Parse les données reçues d'une socket.
	 *
	 * @return mixed Resultat du parsing
	 */
	public function parseData(string $data):KVSDataParserResult {
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

			return new KVSDataParserResult(
				$class,
				$finalData,
				$args[0]??"",
				$args[1]??"",
				$args[2]??"",
				$args[3]??"",
				$params);
		}else{
			throw new KVSDataParsingFailure("Invalid data  : $data");
		}
	}

	/**
	 * @param mixed $message Données à linéariser pour être envoyées dans une socket.
	 *
	 * @return string
	 */
	public function lineariseData($message): string {
		$res = get_class($message).self::SEPARATOR;
		if($message instanceof IKVSInternalRequest){
			$res.=self::ARGS_SEPARATOR;//skip sessId
			$res.=$message->getQueryId().self::ARGS_SEPARATOR;//set queryId
			$res.=$message->getServerKey().self::ARGS_SEPARATOR;//set serverKey
			$res.=$message->getUserName();//set userName
			$res.=self::SEPARATOR;
			$res.= $this->formatData([$message->getParams(),$message->getData()]);
		}else if($message instanceof IKVSRequest){
			$res.=$message->getSessionId().self::ARGS_SEPARATOR; //set sessId
			if($message instanceof IKVSContainerResponse){
				//$res = get_class($data->getResponse()).self::SEPARATOR;
				$res.=$message->getQueryId(); //set queryId
				//$data = $this->lineariseData($data->getResponse()); //modify data
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
		}else if($message instanceof IKVSResponse){
			$res.=self::ARGS_SEPARATOR;//skip sessId
			$res.=self::ARGS_SEPARATOR;//skip serverKey
			$res.=self::ARGS_SEPARATOR;//skip username
			$res.=self::SEPARATOR;
			$res.=$this->lineariseMessage($message);
			//$res.=((is_string($data))?$data:$this->_serializer->serialize($data));
		}else{
			throw new \InvalidArgumentException("data have to be an instanceof of ".IKVSRequest::class." but ".(new Type($message))->get()." given !");
		}
		return $res;
	}

	/**
	 * Si getData() et getParams() de $message renvoient null, alors on renvoie la serialisation du message.
	 * Cela permet, si la requpete contient une ou des données trs volumieuses sérialisée ou à sérialiser, de
	 * les extraire de la requête.
	 * @param IKVSMessage $message Message à linéariser
	 *
	 * @return null|string message linéarisé.
	 */
	private function lineariseMessage(IKVSMessage $message):?string{
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
						$res = array_merge($res,$this->decodeData(substr($data,$length+strlen($tmp[0])+1)));
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