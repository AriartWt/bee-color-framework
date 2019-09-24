<?php

namespace wfw\engine\core\session\handlers\cli;

use wfw\engine\core\session\handlers\errors\SessionIOFailure;
use wfw\engine\core\session\handlers\errors\SessionNotFound;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Allow to read/write in a session in CLI environment.
 * Usefull for the RTS.
 */
class CLIFileSessionHandler implements ICLISessionHandler {
	/** @var string $_dir */
	private $_dir;
	/** @var resource[] $_opened */
	private $_opened;
	/** @var ISerializer $_serializer */
	private $_serializer;

	/**
	 * FileCLISessionHandler constructor.
	 *
	 * @param string      $sessionPath Path to the folder where session files will be stored.
	 * @param ISerializer $serializer  Serializer for session data
	 * @param bool $bypassCliCheck     Set to true to unlock the cli environment check at
	 *                                 construction
	 * @throws IllegalInvocation
	 * @throws SessionNotFound
	 */
	public function __construct(
		string $sessionPath,
		ISerializer $serializer,
		bool $bypassCliCheck = false
	){
		if(!$bypassCliCheck && $this->inCli()) throw new IllegalInvocation(
			static::class." can only be used in a cli php script !"
		);
		if(!is_dir($sessionPath)) throw new SessionNotFound(
			"Session directory $sessionPath not found."
		);
		$this->_opened = [];
		$this->_dir = $sessionPath;
		$this->_serializer = $serializer;
	}

	/**
	 * @return bool True if in CLI contect, false if in a webserver context
	 */
	protected function inCli():bool{
		if(defined('STDIN')) return true;
		if(php_sapi_name() === 'cli') return true;
		if(array_key_exists('SHELL', $_ENV)) return true;
		if(empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0)
			return true;
		if(!array_key_exists('REQUEST_METHOD', $_SERVER) ) return true;
		return false;
	}

	/**
	 * Open the connexion / the file.
	 *
	 * @param string $sessId
	 */
	public function open(string $sessId): void {
		if(!isset($this->_opened[$sessId])){
			if(file_exists("$this->_dir/sess_$sessId")){
				$this->_opened[$sessId] = fopen("$this->_dir/sess_$sessId",'r+');
				if(!is_resource($this->_opened[$sessId])){
					unset($this->_opened[$sessId]);
					throw new SessionIOFailure("Unable to open session file for $sessId !");
				}else if(!flock($this->_opened[$sessId],LOCK_EX))
					throw new SessionIOFailure("Unable to acquire the lock for $sessId");
			}else throw new SessionNotFound("Unable to find session $sessId !");
		}
	}

	/**
	 * Read the session and return data
	 *
	 * @param string $sessId Session id
	 * @return array Session data
	 */
	public function read(string $sessId): array {
		$handle = $this->getResource($sessId);
		if(!is_null($handle)){
			$handle = $this->_opened[$sessId];
			$data = '';
			while(!feof($handle)) $data.=fgets($handle);
			return $this->_serializer->unserialize($data);
		}else throw new SessionIOFailure("Trying to read an unknown session !");
	}

	/**
	 * Save the current session state
	 *
	 * @param string $sessId  Session id
	 * @param array  $session Session data
	 */
	public function write(string $sessId, array $session): void {
		$handle = $this->getResource($sessId);
		if(!is_null($handle)){
			$handle = $this->_opened[$sessId];
			ftruncate($handle,0);
			fwrite($handle,$this->_serializer->serialize($session));
		}else throw new SessionIOFailure("Trying to write in an unknown session !");
	}

	/**
	 * Close the connexion, the file.
	 *
	 * @param string $sessId Session id
	 */
	public function close(string $sessId): void {
		$handle = $this->getResource($sessId);
		if(!is_null($handle)){
			if(!flock($this->_opened[$sessId],LOCK_UN)){
				fclose($this->_opened[$sessId]);
			}else throw new SessionIOFailure("Unable to realease lock on $sessId");
		} else throw new SessionIOFailure("Trying to close an unknown session for $sessId !");
	}

	/**
	 * @param string $sessId
	 * @return mixed|null|resource
	 */
	private function getResource(string $sessId){
		if(isset($this->_opened[$sessId])){
			if(is_resource($this->_opened[$sessId])) return $this->_opened[$sessId];
			else unset($this->_opened[$sessId]);
		}
		return null;
	}

	public function __destruct() {
		foreach($this->_opened as $id=>$handle) $this->close($id);
	}
}