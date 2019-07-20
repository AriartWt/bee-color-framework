<?php
namespace wfw\engine\core\session;

use wfw\engine\core\session\handlers\errors\SessionFailure;
use wfw\engine\core\session\handlers\PHPSessionHandler;

/**
 * Session
 */
final class Session implements ISession {
	protected const LAST_ACTIVITY = "@-SESSION_LAST_ACTIVITY-@";
	/** @var string $_logKey */
	private $_logKey;
	/** @var int $_timeout */
	private $_timeout;
	/** @var bool $_firstStart */
	private $_firstStart;

	/**
	 * @param null|string              $logKey  (default : user) User key where all session data of a logged user
	 *                                          are stored until logout.
	 * @param \SessionHandlerInterface $handler (optionnel) Session handler to register
	 * @param int|null                 $timeout
	 */
	public function __construct(
		string $logKey = "user",
		\SessionHandlerInterface $handler = null,
		?int $timeout = null
	){
		if(!isset($_SESSION) && !is_null($handler) && !( $handler instanceof PHPSessionHandler)){
			ini_set('session.use_strict_mode',true);
			session_set_save_handler($handler,true);
		}
		if(is_null($timeout)){
			$timeout = ini_get("session.gc_maxlifetime");
			ini_set("session.gc_maxlifetime",$timeout);
		}
		ini_set("session.cookie_lifetime",$timeout);
		$this->_timeout = $timeout;
		$this->_firstStart = true;
		$this->_logKey = $logKey;
	}

	/**
	 * Check the current session timeout and reset the session if expired.
	 */
	protected function checkTimeout():void{
		$time = microtime(true);
		if($this->exists(self::LAST_ACTIVITY) && ($time - $this->get(self::LAST_ACTIVITY)) > $this->_timeout){
			$this->destroy();
		}
		$this->set(self::LAST_ACTIVITY,$time);
	}

	/**
	 * Détruit la session
	 */
	public function destroy():void{
		session_unset();
		session_destroy();
		$this->start();
	}

	private function clearDuplicateCookies():void{
		if (headers_sent()) return;
		$cookies = [];
		foreach (headers_list() as $header) {
			if (strpos($header, 'Set-Cookie:') === 0) $cookies[] = $header;
		}
		header_remove('Set-Cookie');
		foreach(array_unique($cookies) as $cookie) header($cookie, false);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set(string $key,$value):void{
		$_SESSION[$key]=$value;
	}

	/**
	 * @param null|string $key Key to get. If null, return the sessiond ata as array
	 * @return mixed|null
	 */
	public function get(?string $key=null){
		if(!is_null($key)) return $_SESSION[$key]?? null;
		else return $_SESSION;
	}

	/**
	 * @param string $key Key to remove
	 */
	public function remove($key):void{
		if(isset($_SESSION[$key])) unset($_SESSION[$key]);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists(string $key): bool {
		return isset($_SESSION[$key]);
	}

	/**
	 * @return bool
	 */
	public function isLogged(): bool {
		return isset($_SESSION[$this->_logKey]);
	}

	/**
	 * Close the session
	 */
	public function close():void {
		if(session_status() === PHP_SESSION_ACTIVE) session_write_close();
		else throw new SessionFailure("Can't close not open session !");
	}

	/**
	 * Start the session
	 */
	public function start():void {
		if(session_status() !== PHP_SESSION_ACTIVE){
			session_start();
			if($this->_firstStart) $this->_firstStart = false;
			else $this->clearDuplicateCookies();
			$this->checkTimeout();
		}else throw new SessionFailure("Can't start an active session !");
	}
}