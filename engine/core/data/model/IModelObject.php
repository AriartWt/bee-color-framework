<?php
namespace wfw\engine\core\data\model;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Description d'un model
 */
interface IModelObject extends IComparable {
	/**
	 * @return UUID
	 */
	public function getId():UUID;

	/**
	 * @param IModelObject $o Objet à comparer
	 * @return bool
	 */
	public function equals(IModelObject $o):bool;

	/**
	 *  Transforme l'objet courant en DTO pour garder la cohérence du Model
	 * @return IDTO
	 */
	public function toDTO():IDTO;
}