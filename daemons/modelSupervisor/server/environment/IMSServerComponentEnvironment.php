<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\engine\core\conf\IConf;

/**
 *  Environnement de travail d'un composant du MSServer
 */
interface IMSServerComponentEnvironment extends IConf {
	/**
	 * @return string Nom du container
	 */
	public function getName():string;

	/**
	 * @return string Chemin d'accés au dossier de travail du component.
	 */
	public function getWorkingDir():string;

	/**
	 *  Teste l'accés d'un utilisater sur l'écriture, la lecture ou l'adminsitration du container.
	 *
	 * @param string $userName   Nom de l'utilisateur dont on souhaite tester les droits
	 * @param int    $permission Permission à tester
	 *
	 * @return bool
	 */
	public function isUserAccessGranted(string $userName, int $permission):bool;
}