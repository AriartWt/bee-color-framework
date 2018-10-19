<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/06/18
 * Time: 16:24
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\engine\core\data\model\IModel;

/**
 * Reconstruit les models spécifiés
 *
 * ATTENTION : Cette opération est effectuée de manière synchrone et peut être lente en
 * fonction du nombre de models, de leurs algorythme d'application des événements,
 * de leurs indexes et du nombre d'événements à réappliquer.
 */
final class RebuildModels implements IWriterAdminRequest
{
    /**
     * @var string $_sessId
     */
    private $_sessId;
    /**
     * @var string[] $_models
     */
    private $_models;

    /**
     * RebuildModels constructor.
     *
     * @param string $_sessId   Identifiant de la session de l'utilisateur courant
     * @param string ...$models Liste des models à reconstruire.
     * @throws \InvalidArgumentException
     */
    public function __construct(string $_sessId,string... $models)
    {
        foreach($models as $m){
            if(!is_a($m,IModel::class,true))
                throw new \InvalidArgumentException("$m doesn't extends ".IModel::class);
        }
        $this->_models = $models;
        $this->_sessId = $_sessId;
    }

    /**
     * @return string[]
     */
    public function getModels():array{
        return $this->_models;
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