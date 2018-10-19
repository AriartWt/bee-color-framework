<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/12/17
 * Time: 10:28
 */

namespace wfw\engine\core\data\model;

/**
 *  Rapport de traitement d'un événement par un model
 */
class EventReceptionReport
{
    private $_created;
    private $_modified;
    private $_removed;
    /**
     * EventReceptionReport constructor.
     *
     * @param IModelObject[]|null $created
     * @param IModelObject[]|null $modified
     * @param IModelObject[]|null $removed
     */
    public function __construct(?array $created=[],?array $modified=[],?array $removed=[])
    {
        $this->_created = $created??[];
        $this->_modified = $modified??[];
        $this->_removed = $removed??[];
    }

    /**
     * @return IModelObject[]
     */
    public function getCreated():array{
        return $this->_created;
    }

    /**
     * @return IModelObject[]
     */
    public function getModified():array{
        return $this->_modified;
    }

    /**
     * @return IModelObject[]
     */
    public function getRemoved():array{
        return $this->_removed;
    }
}