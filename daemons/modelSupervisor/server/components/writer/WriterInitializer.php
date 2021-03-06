<?php
namespace wfw\daemons\modelSupervisor\server\components\writer;

use wfw\daemons\modelSupervisor\server\components\IMSServerComponentsInitializer;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponentEnvironment;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponent;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;

use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\data\IDataParser;

/**
 *  Permet d'initialiser le composant WriteComponent
 */
final class WriterInitializer implements IMSServerComponentsInitializer {
	/**
	 * @param string                         $socket_path           Chemin vers la socket du MSServer
	 * @param string                         $serverKey             Clé du serveur.
	 * @param array                          $modelList             Liste des models gérés par le serveur
	 * @param ISerializer                    $serializer            Serializer utilisé par le MSServer pour les communcations (serialisation/deserialisation)
	 * @param IDataParser                    $dataParser
	 * @param IMSServerComponentEnvironment  $environment           Configurations du composant
	 * @param IMSServerRequestHandlerManager $requestHandlerManager Gestionnaire de requête handler du serveur
	 *
	 * @param ILogger                        $logger                Logger
	 * @param array                          $params
	 * @return IMSServerComponent Le composant initialisé.
	 * @throws \InvalidArgumentException
	 */
	public function init(
		string $socket_path,
		string $serverKey,
		array $modelList,
		ISerializer $serializer,
		IDataParser $dataParser,
		IMSServerComponentEnvironment $environment,
		IMSServerRequestHandlerManager $requestHandlerManager,
		ILogger $logger,
		array $params=[]
	): IMSServerComponent {
		$component = new Writer(
			$socket_path,
			$serverKey,
			$modelList,
			$serializer,
			$dataParser,
			$environment,
			$requestHandlerManager,
			$logger,
			$params
		);
		return $component;
	}
}