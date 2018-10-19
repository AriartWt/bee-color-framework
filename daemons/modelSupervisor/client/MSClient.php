<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/01/18
 * Time: 09:48
 */

namespace wfw\daemons\modelSupervisor\client;

use wfw\daemons\modelSupervisor\client\errors\AlreadyLogged;
use wfw\daemons\modelSupervisor\client\errors\MSClientFailure;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RebuildAllModels;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RebuildModels;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RemoveIndex;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\SetIndex;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\UpdateSnapshot;
use wfw\daemons\modelSupervisor\server\components\writer\requests\read\QueryModel;
use wfw\daemons\modelSupervisor\server\components\writer\requests\write\ApplyEvents;
use wfw\daemons\modelSupervisor\server\components\writer\requests\write\SaveChangedModels;
use wfw\daemons\modelSupervisor\server\errors\MustBeLogged;
use wfw\daemons\modelSupervisor\server\IMSServerRequest;
use wfw\daemons\modelSupervisor\server\IMSServerResponse;
use wfw\daemons\modelSupervisor\server\requests\LoginRequest;
use wfw\daemons\modelSupervisor\server\requests\LogoutRequest;
use wfw\daemons\modelSupervisor\server\responses\AccessGranted;
use wfw\daemons\modelSupervisor\server\responses\DoneResponse;
use wfw\daemons\modelSupervisor\server\responses\RequestError;
use wfw\daemons\modelSupervisor\socket\data\MSServerDataParser;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\types\Type;

/**
 * Client d'un MSServer.
 */
class MSClient implements IMSClient
{
	/** @var string $_addr */
	private $_addr;
	/** @var string $_login */
	private $_login;
	/** @var null|AccessGranted $_session */
	private $_session;
	/** @var string $_password */
	private $_password;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var nullISerializer $_serializer */
	private $_serializer;
	/** @var MSServerDataParser $_dataParser */
	private $_dataParser;

	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>15,"usec"=>0);

	/**
	 * MSClient constructor.
	 *
	 * @param string               $addr     Chemin d'accès à la socket du MSServer
	 * @param string               $login    Login de l'utilisateur
	 * @param string               $password Mot de passe de l'utilisateur
	 * @param null|ISerializer     $serializer
	 * @param null|ISocketProtocol $protocol (optionnel) Protocol de communication avec le MSServer
	 */
	public function __construct(
		string $addr,
		string $login,
		string $password,
		?ISerializer $serializer = null,
		?ISocketProtocol $protocol = null)
	{
		$this->_addr = $addr;
		$this->_login = $login;
		$this->_password = $password;
		$this->_protocol = $protocol ?? new MSServerSocketProtocol();
		$this->_serializer = $serializer ?? new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
		);
		$this->_dataParser = new MSServerDataParser($this->_serializer);
	}

	/**
	 * Obtient une session au client auprès du MSServer
	 */
	public function login(): void
	{
		if(!$this->isLogged()){
			$response = $this->sendRequest(new LoginRequest($this->_login,$this->_password));
			if($response instanceof AccessGranted){
				$this->_session = $response;
			}else{
				throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
			}
		}else{
			throw new AlreadyLogged("You're already logged to the MSServer.");
		}
	}

	/**
	 * @return bool True si le client est connecté au MSServer.
	 */
	public function isLogged(): bool
	{
		return $this->_session instanceof AccessGranted;
	}

	/**
	 * Demande la destruction de la session du client auprés du MSServer
	 */
	public function logout(): void
	{
		$this->checkRequestAbility();
		$this->sendRequest(
			new LogoutRequest($this->_session->getSessionId()),
			false
		);
		$this->_session=null;
	}

	/**
	 * Applique une liste d'événements au jeu de models gérés par le MSServer.
	 *
	 * @param \wfw\engine\core\domain\events\EventList $eventList Liste d'événements à appliquer
	 */
	public function applyEvents(EventList $eventList): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new ApplyEvents($this->_session->getSessionId(),$this->_serializer->serialize($eventList)));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}

	/**
	 * Envoie une requete au MSServer pour obtenir les objets d'un model correspondant à la requête.
	 * La requête doit être lisible par le model.
	 * Nécessite les droits de lecture.
	 *
	 * @param string $class Classe du model concerné
	 * @param string $query Requête sur le model
	 *
	 * @return array
	 * @throws MSClientFailure
	 * @throws \Exception
	 */
	public function query(string $class,string $query): array
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new QueryModel(
			$this->_session->getSessionId(),
			$class,
			$query
		));
		if(is_array($response)){
			return $response;
		}else{
			throw new MSClientFailure("Unexpected server's response : ".((new Type($response))->get()));
		}
	}

	/**
	 * Envoie une requête forçant le serveur à sauvegarder les modifications sur tous les models.
	 * Nécessite les droits d'écriture.
	 * Action synchrone, bloque toutes les autres requêtes vers le writer jusqu'à la fin de la sauvegarde.
	 * Cette action peut-être lente en fonction du nombre et de la taille des models importés.
	 * Il est conseillé de ne l'utiliser que dans les parties les plus critiques de l'application, ou de régler
	 * une sauvegarde périodique plus courte.
	 */
	public function triggerSave(): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(
			new SaveChangedModels($this->_session->getSessionId())
		);
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected server's response : ".get_class($response));
		}
	}

	/**
	 * Ajoute ou modifie un index sur un model.
	 *
	 * @param string                 $class          Classe du model concerné
	 * @param string                 $name           Nom de l'index à créer ou modifier
	 * @param ISpecification $spec           Specification de l'index (mode de tri)
	 * @param bool                   $modifyIfExists (optionnel defaut : true) Si true : si l'index existe il est modifié.
	 */
	public function setIndex(string $class, string $name, ISpecification $spec, bool $modifyIfExists = true): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new SetIndex($this->_session->getSessionId(),$class,$name,$spec,$modifyIfExists));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}

	/**
	 * Supprime un index d'un model
	 *
	 * @param string $class Classe du model concerné
	 * @param string $name  Nom de l'index à supprimer
	 */
	public function removeIndex(string $class, string $name): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new RemoveIndex($this->_session->getSessionId(),$class,$name));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}

	/**
	 * @throws MustBeLogged
	 */
	private function checkRequestAbility():void{
		if(!$this->isLogged()){
			throw new MustBeLogged("You must be logged to perform this action. Please call MSClient::login !");
		}
	}

	/**
	 *  Envoie une requête au serveur.
	 *
	 * @param IMSServerRequest $request      Requête à envoyer au serveur
	 * @param bool                     $waitResponse Attendre une réponse du serveur
	 *
	 * @return mixed Réponse du serveur
	 * @throws MSClientFailure
	 * @throws \Exception
	 */
	private function sendRequest(IMSServerRequest $request, bool $waitResponse=true){
		$socket = $this->createClientSocket($this->_addr);
		$this->_protocol->write($socket,$this->_dataParser->lineariseData($request));
		if($waitResponse){
			$data = $this->_protocol->read($socket);
			socket_close($socket);
			if(strlen($data)>0){
				$response = $this->_dataParser->parseData($data);
				if($response->instanceOf(IMSServerResponse::class)){
					if($response->instanceOf(RequestError::class)){
						if($response->instanceOf(MustBeLogged::class)){
							$this->_session = null;
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
				}else{
					throw new MSClientFailure("Invalid server response : $data");
				}
			}else{
				throw new MSClientFailure("No data recieved : socket timed out");
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
	 * Remet à zéro la session pour permettre une reconnexion.
	 */
	protected function resetLogin():void{
		$this->_session = false;
	}

	/**
	 * Met à jour le snapshot des models.
	 * Nécessite les droits d'administration
	 */
	public function updateSnapshot(): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new UpdateSnapshot($this->_session->getSessionId()));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}

	/**
	 * Reconstruit tous les models en réappliquant tous les événements.
	 * Nécessite les droits d'administration
	 *
	 * ATTENTION : Cette opération peut-être vraiment très lente en fonction du nombre de models,
	 * de leurs indexes, de la complexité de l'algorythme d'application des événements et du
	 * nombre d'événements à appliquer.
	 */
	public function rebuildAllModels(): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new RebuildAllModels($this->_session->getSessionId()));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}

	/**
	 * Reconstruit les models spécifiés en réappliquant tous les événements.
	 * Nécessite les droits d'administration
	 *
	 * ATTENTION : Cette opération peut-être vraiment très lente en fonction du nombre de models,
	 * de leurs indexes, de la complexité de l'algorythme d'application des événements et du
	 * nombre d'événements à appliquer.
	 *
	 * @param string[] $classes Liste des models à reconstruire.
	 */
	public function rebuildModels(string... $classes): void
	{
		$this->checkRequestAbility();
		$response = $this->sendRequest(new RebuildModels($this->_session->getSessionId(),...$classes));
		if(!($response instanceof DoneResponse)){
			throw new MSClientFailure("Unexpected MSServer's response : ".get_class($response));
		}
	}
}