<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 13:59
 */

namespace wfw\daemons\rts\server\environment;

/**
 * Environment d'un RTS
 */
interface IRTSEnvironment {
	/**
	 * @return string Repertoire de travail du serveur.
	 */
	public function getWorkingDir(): string ;

	/**
	 *  Retourne un utilisateur grâce à son nom.
	 *
	 * @param string $name Nom de l'utilisateur
	 *
	 * @return IRTSUser
	 */
	public function getUser(string $name): IRTSUser;

	/**
	 *  Teste l'existence d'un utilisateur
	 *
	 * @param string $name Nom de l'utilisateur à tester
	 *
	 * @return bool
	 */
	public function existsUser(string $name): bool;

	/**
	 *  Retourne un groupe d'utilisateur
	 *
	 * @param string $name Nom du groupe
	 *
	 * @return IRTSUserGroup
	 */
	public function getUserGroup(string $name): IRTSUserGroup;

	/**
	 *  Teste l'existence d'un groupe d'utilisateur
	 *
	 * @param string $name Nom du groupe à tester
	 *
	 * @return bool
	 */
	public function existsUserGroup(string $name): bool;

	/**
	 *  Crée une session pour un utilisateur si ses informations de connexion sont valides.
	 *
	 * @param string $login    Login de l'utilisateur
	 * @param string $password Mot de passe de l'utilisateur
	 *
	 * @return null|string Identifiant de session si la session a été créée, null sinon.
	 */
	public function createSessionForUser(string $login, string $password): ?string;

	/**
	 *  Retourne une session grace à son identifiant.
	 *
	 * A chaque fois que la fonction est appelée, le temps avant suppression de la session doit être remis à 0.
	 *
	 * @param string $sessionId Identifiant de session
	 *
	 * @return IMSServerSession|null
	 */
	public function getUserSession(string $sessionId): ?IRTSSession;

	/**
	 *  Remet à 0 le compteur de suppression de la session.
	 *
	 * @param string $sessionId Identifiant de la session.
	 */
	public function touchUserSession(string $sessionId): void;

	/**
	 *  Teste l'existence d'une session
	 *
	 * @param string $sessionId Identifiant de la session à tester
	 *
	 * @return bool
	 */
	public function existsUserSession(string $sessionId): bool;

	/**
	 *  Détruit la session d'un utilisateur
	 *
	 * @param string $sessionId Session à détruire.
	 */
	public function destroyUserSession(string $sessionId);

	/**
	 *  Supprime les sessions inactives depuis un certain temps.
	 */
	public function destroyOutdatedSessions(): void;
}