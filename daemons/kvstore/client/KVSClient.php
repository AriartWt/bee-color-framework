<?php
namespace wfw\daemons\kvstore\client;

use wfw\daemons\kvstore\client\errors\AlreadyLogged;
use wfw\daemons\kvstore\client\errors\KVSClientFailure;
use wfw\daemons\kvstore\errors\KVSFailure;
use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;
use wfw\daemons\kvstore\server\containers\request\admin\PurgeContainerRequest;
use wfw\daemons\kvstore\server\containers\request\read\ExistsKeyRequest;
use wfw\daemons\kvstore\server\containers\request\read\GetKeyRequest;
use wfw\daemons\kvstore\server\containers\request\write\ChangeStorageModeRequest;
use wfw\daemons\kvstore\server\containers\request\write\RemoveRequest;
use wfw\daemons\kvstore\server\containers\request\write\SetRequest;
use wfw\daemons\kvstore\server\containers\request\write\SetTtlRequest;
use wfw\daemons\kvstore\server\containers\response\DoneResponse;
use wfw\daemons\kvstore\server\containers\response\ExistKeyResponse;
use wfw\daemons\kvstore\server\errors\MustBeLogged;
use wfw\daemons\kvstore\server\requests\IKVSRequest;
use wfw\daemons\kvstore\server\requests\LoginRequest;
use wfw\daemons\kvstore\server\requests\LogoutRequest;
use wfw\daemons\kvstore\server\responses\AccessGranted;
use wfw\daemons\kvstore\server\responses\IKVSResponse;
use wfw\daemons\kvstore\server\responses\RequestError;
use wfw\daemons\kvstore\socket\data\KVSDataParser;
use wfw\daemons\kvstore\socket\protocol\KVSSocketProtocol;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 *  Client du serveur KVS.
 */
class KVSClient implements IKVSClient {
	/** @var string $_addr */
	private $_addr;
	/** @var string $_login */
	private $_login;
	/** @var string $_password */
	private $_password;
	/** @var string $_container */
	private $_container;
	/** @var null|string $_defaultStorageMode */
	private $_defaultStorageMode;
	/** @var string $_currentNamespace */
	private $_currentNamespace;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;

	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>15,"usec"=>0);
	/** @var AccessGranted $_logged */
	private $_logged;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var KVSDataParser $_dataParser */
	private $_dataParser;

	/**
	 * KVSClient constructor.
	 *
	 * @param string                       $addr
	 * @param string                       $login
	 * @param string                       $password
	 * @param string                       $container
	 * @param null|string                  $defaultStorageMode
	 * @param null|ISerializer     $serializer
	 * @param null|ISocketProtocol $protocol
	 */
	public function __construct(
		string $addr,
		string $login,
		string $password,
		string $container,
		?string $defaultStorageMode = null,
		?ISerializer $serializer = null,
		?ISocketProtocol $protocol = null)
	{
		$this->_addr = $addr;
		$this->_login = $login;
		$this->_password = $password;
		$this->_container = $container;
		$this->_serializer = $serializer ?? new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
			);
		$this->_dataParser = new KVSDataParser($this->_serializer);
		$this->_defaultStorageMode = $defaultStorageMode;
		$this->_currentNamespace = "";
		if(!is_null($protocol)){
			$this->_protocol = $protocol;
		}else{
			$this->_protocol = new KVSSocketProtocol();
		}
	}

	/**
	 *  Obtient la valeur associée à une clé
	 *
	 * @param string $key Clé
	 *
	 * @return mixed
	 *
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function get(string $key)
	{
		$this->checkRequestAbility();
		return $this->sendRequest(new GetKeyRequest(
			$this->_logged->getSessionId(),
			new StorageKey($key)
		));
	}

	/**
	 * @param string   $key         Clé d'enregistrement
	 * @param mixed    $data        Données associées à la clé
	 * @param float    $ttl         (optionnel défaut : 0) Temps de vie de la clé. Si 0 : pas de limite.
	 * @param int|null $storageMode Nouveau mode de stockage de la clé.
	 *                              Si null, le stockage de la clé sera celui de l'instance courante.
	 *                              Si null aussi, ce sera le mode de stockage par défaut du container.
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function set(string $key, $data, float $ttl = 0, ?int $storageMode = null)
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new SetRequest(
			$this->_logged->getSessionId(),
			new StorageKey($key),
			$this->_serializer->serialize($data),
			$ttl,
			$storageMode ?? $this->getDefaultStorageMode()
		));
		if(!($response instanceof DoneResponse)){
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 *  Applique une durée de vie à une clé.
	 *
	 * @param string $key Clé concernée
	 * @param float  $ttl Nouveau temps de vie. Si <0 : la clé n'a plus de limite de vie.
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function setTtl(string $key, float $ttl)
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new SetTtlRequest(
			$this->_logged->getSessionId(),
			new StorageKey($key),
			$ttl
		));
		if(!($response instanceof DoneResponse)){
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 *  Change le mod ede stockage de la clé.
	 *
	 * @param string   $key         Clé concernée
	 * @param int|null $storageMode Nouveau mode de stockage de la clé.
	 *                              Si null, le stockage de la clé sera celui de l'instance courante.
	 *                              Si null aussi, ce sera le mode de stockage par défaut du container.
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function changeStorageMode(string $key, ?int $storageMode)
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new ChangeStorageModeRequest(
			$this->_logged->getSessionId(),
			new StorageKey($key),
			$storageMode ?? $this->getDefaultStorageMode()
		));
		if(!($response instanceof DoneResponse)){
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 *  Supprime une clé
	 *
	 * @param string $key Clé à supprimer
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function remove(string $key)
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new RemoveRequest(
			$this->_logged->getSessionId(),
			new StorageKey($key)
		));
		if(!($response instanceof DoneResponse)){
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 *  Teste l'existence d'une clé
	 *
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
	 */
	public function exists(string $key): bool
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new ExistsKeyRequest(
		   $this->_logged->getSessionId(),
		   new StorageKey($key)
		));
		if($response instanceof ExistKeyResponse){
			return $response->exists();
		}else{
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 *  Supprime toutes les données du container.
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 */
	public function purge(): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new PurgeContainerRequest(
			$this->_logged->getSessionId()
		));
		if(!($response instanceof DoneResponse)) {
			throw new KVSClientFailure("Unexpected response : ".get_class($response));
		}
	}

	/**
	 * @return null|int Mode de stockage par défaut de l'instance courante.
	 */
	public function getDefaultStorageMode(): ?int
	{
		return $this->_defaultStorageMode;
	}

	/**
	 *  Connecte l'instance courante au serveur KVS
	 *
	 * @throws AlreadyLogged
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 */
	public function login()
	{
		if(!$this->isLogged()){
			$response = $this->sendRequest(new LoginRequest(
				$this->_container,
				$this->_login,
				$this->_password,
				$this->_defaultStorageMode
			));
			if($response instanceof AccessGranted){
				$this->_logged = $response;
			} else {
				throw new KVSClientFailure("Unexpected KVSServer's response : ".get_class($response));
			}
		} else {
			throw new AlreadyLogged("You're already logged to the KVS server.");
		}
	}

	/**
	 *  Deconnecte l'instance courante du serveur KVS
	 *
	 * @throws KVSClientFailure
	 * @throws KVSFailure
	 */
	public function logout(): void {
		$this->checkRequestAbility();
		$this->sendRequest(new LogoutRequest(
			$this->_logged->getSessionId()
		),false);
		$this->_logged = null;
	}

	/**
	 *  Verifie que le client est bien connecté, lève une exception sinon.
	 *
	 * @throws MustBeLogged
	 */
	private function checkRequestAbility():void{
		if(!$this->isLogged()){
			throw new MustBeLogged("You must be logged to perform this action. Please call KVSClient::login !");
		}
	}

	/**
	 *  Envoie une requête au serveur.
	 *
	 * @param IKVSRequest $request      Requête à envoyer au serveur
	 * @param bool                $waitResponse Attendre une réponse du serveur
	 *
	 * @return mixed Réponse du serveur
	 * @throws KVSFailure
	 */
	private function sendRequest(IKVSRequest $request, bool $waitResponse=true){
		$socket = $this->createClientSocket($this->_addr);
		$this->_protocol->write($socket,$this->_dataParser->lineariseData($request));
		if($waitResponse){
			$data = $this->_protocol->read($socket);
			socket_close($socket);
			if(strlen($data)>0){
				$response = $this->_dataParser->parseData($data);
				if($response->instanceOf(IKVSResponse::class)){
					if($response->instanceOf(RequestError::class)){
						if($response->instanceOf(MustBeLogged::class)){
							$this->_logged = null;
						}
						$err = $this->_serializer->unserialize($response->getData());
						$errorClass = $err->getErrorClass();
						throw (new $errorClass($err->getError()));
					}else{
						if(strlen($response->getData())>0){
							return $this->_serializer->unserialize($response->getData());
						}else{
							return null;
						}
					}
				} else {
					throw new KVSClientFailure("Invalid server response : $data");
				}
			} else {
				throw new KVSClientFailure("No data recieved : socket timed out.");
			}
		}else{
			socket_close($socket);
			return null;
		}
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
	 * @return bool True si le client est loggé sur le serveur, false sinon.
	 */
	public function isLogged(): bool {
		return $this->_logged instanceof AccessGranted;
	}

	/**
	 * Supprime les données de session pour permettre une reconnexion.
	 */
	protected function resetLogin():void{
		$this->_logged = null;
	}
}