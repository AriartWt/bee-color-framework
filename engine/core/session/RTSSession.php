<?php

namespace wfw\engine\core\session;

use wfw\engine\core\session\handlers\cli\ICLISessionHandler;

/**
 * Class RTSSession
 *
 * @package wfw\engine\core\session
 */
final class RTSSession implements ISession {
	/** @var string $_id */
	private $_id;
	/** @var array $_data */
	private $_data;
	/** @var string $_userKey */
	private $_userKey;
	/** @var ICLISessionHandler $_handler */
	private $_handler;
	/** @var bool $_modified */
	private $_modified;

	/***
	 * RTSSession constructor.
	 *
	 * @param string             $sessId  Session Id
	 * @param ICLISessionHandler $handler Session handler crafted to perform simple operations on
	 *                                    web sessions from cli
	 * @param string             $userKey Key used to determine if a user is logged or not
	 */
	public function __construct(string $sessId, ICLISessionHandler $handler, string $userKey = 'user'){
		$this->_id = $sessId;
		$this->_data = [];
		$this->_modified = false;
		$this->_userKey = $userKey;
		$this->_handler = $handler;
	}

	/**
	 * Read a session key
	 *
	 * @param string|null $key Key to get. If null, return session data as array
	 * @return mixed|null data
	 **/
	public function get(?string $key = null) {
		if(!is_null($key)) return $this->_data[$key] ?? null;
		else return $this->_data;
	}

	/**
	 *    Add or modify a key in the current session
	 *
	 * @param string $key   Key
	 * @param mixed  $value value
	 **/
	public function set(string $key, $value): void {
		$this->_data[$key] = $value;
		$this->_modified = true;
	}

	/**
	 *  Remove a key from the session
	 *
	 * @param string $key Key to remove
	 */
	public function remove(string $key): void {
		if(isset($this->_data[$key])){
			$this->_modified = true;
			unset($this->_data[$key]);
		}
	}

	/**
	 * Test if a key is set in the current session.
	 *
	 * @param string $key Key to test
	 * @return bool
	 */
	public function exists(string $key): bool {
		return isset($this->_data[$key]);
	}

	/**
	 * @return bool Permet de savoir si un utilisateur loggé est enregistré.
	 */
	public function isLogged(): bool {
		return $this->exists($this->_userKey);
	}

	/**
	 * Empty the session data
	 */
	public function destroy():void {
		$this->_data = [];
		$this->_modified = true;
		$this->write();
	}

	private function write(){
		if($this->_modified){
			$this->_handler->write($this->_id,$this->_data);
			$this->_modified = false;
		}
	}

	/**
	 * Close the session
	 */
	public function close():void {
		$this->write();
		$this->_handler->close($this->_id);
	}

	public function __destruct() {
		$this->close();
	}

	/**
	 * Start the session
	 */
	public function start(): void {
		$this->_handler->open($this->_id);
		$this->_data = $this->_handler->read($this->_id);
	}
}