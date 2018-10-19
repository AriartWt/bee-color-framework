<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/01/18
 * Time: 09:04
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

/**
 *  Supprime l'index pour le model spécifié.
 */
final class RemoveIndex extends IndexRequest
{
    /**
     * RemoveIndexRequest constructor.
     *
     * @param string $sessId    Identifiant de session
     * @param string $modelName Nom du model contenant l'index
     * @param string $indexName Index à supprimer
     */
    public function __construct(string $sessId,string $modelName, string $indexName)
    {
        parent::__construct($sessId,$modelName, $indexName);
    }
}