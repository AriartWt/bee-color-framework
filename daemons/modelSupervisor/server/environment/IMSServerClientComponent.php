<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;

use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\data\IDataParser;

/**
 *  Permet d'initialiser un composant.
 */
interface IMSServerClientComponent extends IMSServerComponent {
	/**
	 * @param string  $socket_path  Chemin vers la socket du MSServer
	 * @param string  $serverKey    Clé du serveur.
	 * @param IMSServerRequestHandlerManager $requestHandlerManager Gestionnaire de requête handler
	 *                                                              du serveur
	 * @param ISerializer $serializer Objet permettant la serialisation/déserialisation
	 *                                utilisé par le MSServer pour les communications.
	 * @param IDataParser $dataParser Parseur de données pour les IO des sockets
	 */
	public function init(
		string $socket_path,
		string $serverKey,
		IMSServerRequestHandlerManager $requestHandlerManager,
		ISerializer $serializer,
		IDataParser $dataParser
	):void;
}