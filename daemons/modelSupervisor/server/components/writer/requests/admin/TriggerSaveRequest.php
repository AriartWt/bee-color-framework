<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/01/18
 * Time: 23:47
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\daemons\modelSupervisor\server\components\requests\IClientDeniedRequest;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Déclenche une sauvegarde des données en attente.
 */
final class TriggerSaveRequest implements IWriterAdminRequest,IClientDeniedRequest
{
    /**
     * @var string $_id
     */
    private $_id;

    /**
     * @var int $_pid
     */
    private $_pid;

    /**
     * TriggerSaveRequest constructor.
     *
     * @param int $pid PID du worker demandant la sauvegarde.
     */
    public function __construct(int $pid)
    {
        $this->_id = (string)new UUID(UUID::V4);
        $this->_pid = $pid;
    }

    /**
     * @return string
     */
    public function getSaveId():string{
        return $this->_id;
    }

    /**
     * @return int
     */
    public function getPID():int{
        return $this->_pid;
    }

    /**
     * @return null|string Identifiant de session
     */
    public function getSessionId(): ?string
    {
        return null;
    }

    /**
     * @return mixed Données du message.
     */
    public function getData()
    {
        return null;
    }

    /**
     * @return mixed Paramètres du message
     */
    public function getParams()
    {
        return null;
    }
}