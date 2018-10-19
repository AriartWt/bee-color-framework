<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/06/18
 * Time: 16:51
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

/**
 * Class UpdateSnapshot
 *
 * @package wfw\daemons\modelSupervisor\server\components\writer\requests\admin
 */
final class UpdateSnapshot implements IWriterAdminRequest
{
    /** @var string $_sessId */
    private $_sessId;

    /**
     * UpdateSnapshot constructor.
     *
     * @param string $sessId Identifiant de la session utilisateur
     */
    public function __construct(string $sessId)
    {
        $this->_sessId = $sessId;
    }

    /**
     * @return null|string Identifiant de session
     */
    public function getSessionId(): ?string
    {
        return $this->_sessId;
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