<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/01/18
 * Time: 08:12
 */

namespace wfw\daemons\kvstore\server\containers\params\client;
use wfw\daemons\kvstore\server\environment\IKVSContainer;

/**
 *  Paramètres attendus par le client d'un container worker
 */
final class ContainerWorkerClientParams
{
    private $_container;
    private $_socketDir;

    /**
     * ContainerWorkerClientParams constructor.
     *
     * @param IKVSContainer $container Container géré par le worker
     * @param string                $socketDir Repertoire de la socket du worker
     */
    public function __construct(IKVSContainer $container,string $socketDir="/tmp")
    {
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