<?php
namespace wfw\engine\core\session;

/**
 * Session PHP
 */
interface ISession {
	/**
	 *	Read a session key
	 *	@param string|null $key Key to get. If null, return session data as array
	 * @return mixed|null data
	 **/
	public function get(?string $key=null);

	/**
	 *	Add or modify a key in the current session
	 *	@param string $key Key
	 *	@param mixed $value value
	 **/
	public function set(string $key,$value):void;
	/**
	 *  Remove a key from the session
	 * @param string $key Key to remove
	 */
	public function remove(string $key):void;

	/**
	 * Test if a key is set in the current session.
	 * @param string $key Key to test
	 * @return bool
	 */
	public function exists(string $key):bool;

	/**
	 * @return bool Permet de savoir si un utilisateur loggé est enregistré.
	 */
	public function isLogged():bool;

	/**
	 * Empty the session data
	 */
	public function destroy():void;

	/**
	 * Close the session
	 */
	public function close():void;

	/**
	 * Start the session
	 */
	public function start():void;
}