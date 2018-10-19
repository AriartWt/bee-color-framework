<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/01/18
 * Time: 06:19
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\params\client;

use wfw\daemons\modelSupervisor\server\IMSServerRequest;

/**
 *  Paramètre d'un client du WriteModelWorker
 */
final class WriteModelClientWorkerParams
{
    private $_workerSocketAddr;
    private $_serverRequest;

    /**
     * WriteModelClientWorkerParams constructor.
     *
     * @param string                                                            $workerSocketAddr
     * @param null|\wfw\daemons\modelSupervisor\server\IMSServerRequest $request
     */
    public function __construct(string $workerSocketAddr,?IMSServerRequest $request=null)
    {
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