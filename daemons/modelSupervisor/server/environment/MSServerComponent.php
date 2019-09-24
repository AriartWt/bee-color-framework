<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\daemons\modelSupervisor\server\components\IMSServerComponentsInitializer;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;

use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\data\IDataParser;

/**
 *  Décorateur d'un Composant du MSServer
 */
final class MSServerComponent implements IMSServerClientComponent {
	/** @var IMSServerComponentEnvironment $_environment */
	private $_environment;
	/** @var IMSServerComponentsInitializer $_initializer */
	private $_initializer;
	/** @var IMSServerComponent $_component */
	private $_component;
	/** @var array $_modelList */
	private $_modelList;

	/**
	 * MSServerComponent constructor.
	 *
	 * @param array                                  $modelList   Liste des models gérés par le serveur.
	 * @param IMSServerComponentEnvironment  $environment Environnement du composant
	 * @param IMSServerComponentsInitializer $initializer Permet d'initialiser un composant
	 */
	public function __construct(
		array $modelList,
		IMSServerComponentEnvironment $environment,
		IMSServerComponentsInitializer $initializer
	) {
		$this->_environment = $environment;
		$this->_initializer = $initializer;
		$this->_modelList = $modelList;
	}

	/**
	 * @param string                         $socket_path           Chemin vers la socket du MSServer
	 * @param string                         $serverKey             Clé du serveur.
	 * @param IMSServerRequestHandlerManager $requestHandlerManager Gestionnaire de requête handler du serveur
	 * @param ISerializer                    $serializer            Serializer utilisé pour la serialisation/deserialisation par le MSServer pour les communications avec ses workers
	 * @param IDataParser                    $dataParser            Parseur de données pour les IO des sockets
	 * @param ILogger                        $logger                Logger
	 * @param array                          $params
	 */
	public function init(
		string $socket_path,
		string $serverKey,
		IMSServerRequestHandlerManager $requestHandlerManager,
		ISerializer $serializer,
		IDataParser $dataParser,
		ILogger $logger,
		array $params=[]
	):void{
		$this->_component = $this->_initializer->init(
			$socket_path,
			$serverKey,
			$this->_modelList,
			$serializer,
			$dataParser,
			$this->_environment,
			$requestHandlerManager,
			$logger,
			$params
		);
	}

	/**
	 *  Appelé par le ModuleInitializer
	 */
	public function start(): void {
		$this->_component->start();
	}

	/**
	 *  Appelé par le ModelManagerServer juste avant qu'il ne quitte, si la fonction haveToBeShutdownGracefully renvoie true
	 */
	public function shutdown(): void {
		$this->_component->shutdown();
	}

	/**
	 * @return string Nom du composant
	 */
	public function getName(): string {
		return $this->_component->getName();
	}
}