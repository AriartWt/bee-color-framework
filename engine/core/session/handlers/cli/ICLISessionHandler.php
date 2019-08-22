<?php

namespace wfw\engine\core\session\handlers\cli;

/**
 * CLISession handler that can be used to read/write sessions from cli.
 */
interface ICLISessionHandler {
	/**
	 * Open the connexion / the file.
	 * @param string $sessId
	 */
	public function open(string $sessId):void;

	/**
	 * Read the session and return data
	 *
	 * @param string $sessId Session id
	 * @return array Session data
	 */
	public function read(string $sessId):array;

	/**
	 * Save the current session state
	 *
	 * @param string $sessId  Session id
	 * @param array  $session Session data
	 */
	public function write(string $sessId, array $session):void;

	/**
	 * Close the connexion, the file.
	 *
	 * @param string $sessId Session id
	 */
	public function close(string $sessId):void;
}