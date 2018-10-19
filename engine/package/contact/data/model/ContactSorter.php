<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/10/18
 * Time: 11:17
 */

namespace wfw\engine\package\contact\data\model;

use wfw\engine\core\data\model\ArraySorter;
use wfw\engine\core\data\model\IModelObject;

/**
 * Trie les prise de contact de la plus récente à la plus ancienne
 */
class ContactSorter extends ArraySorter{
	/**
	 * @param IModelObject $o1
	 * @param IModelObject $o2
	 * @return int
	 */
	public function compare(IModelObject $o1, IModelObject $o2): int {
		return -1 * parent::compare($o1, $o2);
	}
}