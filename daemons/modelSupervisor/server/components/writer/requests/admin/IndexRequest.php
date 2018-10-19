<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/01/18
 * Time: 08:30
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\engine\core\data\model\IModel;

/**
 * Implementation de base pour une requete concernant les indexs d'un model
 */
abstract class IndexRequest implements IWriterAdminRequest
{
    /**
     * @var null|string $_name
     */
    private $_name;
    /**
     * @var string $_modelName
     */
    private $_modelName;

    /**
     * @var string $_sessId
     */
    private $_sessId;

    /**
     * IndexRequest constructor.
     *
     * @param string      $sessId
     * @param string      $modelName
     * @param null|string $indexName
     */
    public function __construct(string $sessId,string $modelName,?string $indexName=null)
    {
        $this->_name = $indexName;
        $this->_sessId = $sessId;
        if(is_a($modelName,IModel::class,true)){
            $this->_modelName = $modelName;
        }else{
            throw new \InvalidArgumentException("$modelName is not a valide model class name !");
        }
    }

    /**
     * @return null|string
     */
    public function getName():?string{
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->_modelName;
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

    /**
     * @return null|string Identifiant de session
     */
    public function getSessionId(): ?string
    {
        return $this->_sessId;
    }
}