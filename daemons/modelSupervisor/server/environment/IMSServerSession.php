<?php
namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Session du model supervizor server
 */
interface IMSServerSession {
	/**
	 * @return string Identifiant de la session
	 */
	public function getId():string;

	/**
	 * @return IMSServerUser Utilisateur associé à la session
	 */
	public function getUser():IMSServerUser;
}