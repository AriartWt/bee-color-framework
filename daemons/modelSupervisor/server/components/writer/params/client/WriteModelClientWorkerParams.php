<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\params\client;

use wfw\daemons\modelSupervisor\server\IMSServerRequest;

/**
 *  Paramètre d'un client du WriteModelWorker
 */
final class WriteModelClientWorkerParams {
	/** @var string $_workerSocketAddr */
	private $_workerSocketAddr;
	/** @var null|IMSServerRequest $_serverRequest */
	private $_serverRequest;

	/**
	 * WriteModelClientWorkerParams constructor.
	 *
	 * @param string                                                            $workerSocketAddr
	 * @param null|\wfw\daemons\modelSupervisor\server\IMSServerRequest $request
	 */
	public function __construct(string $workerSocketAddr,?IMSServerRequest $request=null) {
		$this->_workerSocketAddr = $workerSocketAddr;
		$this->_serverRequest=$request;
	}

	/**
	 * @return string
	 */
	public function getWorkerSocketAddr():string{
		return $this->_workerSocketAddr;
	}

	/**
	 * @return null|IMSServerRequest
	 */
	public function getServerRequest():?IMSServerRequest{
		return $this->_serverRequest;
	}
}