<?php
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