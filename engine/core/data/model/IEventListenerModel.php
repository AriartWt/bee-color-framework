<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/01/18
 * Time: 07:54
 */

namespace wfw\engine\core\data\model;

use wfw\engine\core\domain\events\IDomainEventListener;

/**
 * Interface IEventHandlerModel
 *
 * @package wfw\engine\core\data\model
 */
interface IEventListenerModel extends IModel,IDomainEventListener{
    /**
     *  Retourne la liste des classes des événements qui sont écoutés par le model
     * @return string[]
     */
    public function listenEvents():array;
}