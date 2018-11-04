<?php
namespace wfw\daemons\kvstore\server\containers\params\client;
use wfw\daemons\kvstore\server\environment\IKVSContainer;

/**
 *  Paramètres attendus par le client d'un container worker
 */
final class ContainerWorkerClientParams {
	/** @var IKVSContainer $_container */
	private $_container;
	/** @var string $_socketDir */
	private $_socketDir;

	/**
	 * ContainerWorkerClientParams constructor.
	 *
	 * @param IKVSContainer $container Container géré par le worker
	 * @param string                $socketDir Repertoire de la socket du worker
	 */
	public function __construct(IKVSContainer $container,string $socketDir="/tmp") {
		$this->_container = $container;
		$this->_socketDir = $socketDir;
	}

	/**
	 * @return string Repertoire de la socket
	 */
	public function getSocketDir():string{
		return $this->_socketDir;
	}

	/**
	 * @return IKVSContainer
	 */
	public function getContainer():IKVSContainer{
		return $this->_container;
	}
}