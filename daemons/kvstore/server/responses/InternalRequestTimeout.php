<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/01/18
 * Time: 10:33
 */

namespace wfw\daemons\kvstore\server\responses;

use wfw\daemons\kvstore\server\errors\InactiveKVSContainerWorker;

/**
 *  Impossible de joindre un worker de container.
 */
final class InternalRequestTimeout extends RequestError
{
    /**
     * InternalRequestTimeout constructor.
     *
     * @param string $containerName Nom du container
     */
    public function __construct(string $containerName)
    {
        parent::__construct(new InactiveKVSContainerWorker("The container $containerName'worker cannot starts !"));
    }
}