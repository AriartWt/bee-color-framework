<?php
namespace wfw\daemons\modelSupervisor\server\components;

use wfw\daemons\modelSupervisor\server\environment\IMSServerComponent;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponentEnvironment;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;

use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\data\IDataParser;

/**
 *  Permet d'initiliser les composants d'un MSServer
 */
interface IMSServerComponentsInitializer {
	/**
	 * @param string                         $socket_path           Chemin vers la socket du MSServer
	 * @param string                         $serverKey             Clé du serveur.
	 * @param array                          $modelList             Liste des models gérés par le serveur
	 * @param ISerializer                    $serializer            Serializer utilsié pour la sérialisation/deserialisation
	 *                                                              des données par le MSServer.
	 * @param IDataParser                    $parser                Parseur de données pour les IO socket.
	 * @param IMSServerComponentEnvironment  $environment           Configurations du composant
	 * @param IMSServerRequestHandlerManager $requestHandlerManager Gestionnaire de requête handler
	 *                                                              du serveur
	 *
	 * @param ILogger                        $logger
	 * @param array                          $params
	 * @return IMSServerComponent Le composant initialisé.
	 */
	public function init(
		string $socket_path,
		string $serverKey,
		array $modelList,
		ISerializer $serializer,
		IDataParser $parser,
		IMSServerComponentEnvironment $environment,
		IMSServerRequestHandlerManager $requestHandlerManager,
		ILogger $logger,
		array $params=[]
	):IMSServerComponent;
}