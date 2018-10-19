<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 12/12/17
 * Time: 04:41
 */

namespace wfw\engine\core\data\model;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Objets internes utilisés par les models (repository)
 */
abstract class ModelObject implements IModelObject {
	/** @var UUID $_id */
	private $_id;

	/**
	 *  model constructor.
	 *
	 * @param UUID $id Identifiant de l'objet
	 */
	public function __construct(UUID $id){
		$this->_id = $id;
	}

	/**
	 * @return UUID
	 */
	public function getId():UUID{
		return $this->_id;
	}

	/**
	 * @param IModelObject $o
	 * @return bool
	 */
	public function equals(IModelObject $o): bool
	{
		return (string) $this->getId() === (string) $o->getId();
	}

	/**
	 * @param IModelObject $o
	 * @return int
	 */
	public function compareTo(IModelObject $o): int {
		return 0;
	}

	/**
	 *  Transforme l'objet courant en DTO pour garder la cohérence du Model
	 * @return IDTO
	 */
	public abstract function toDTO():IDTO;
}