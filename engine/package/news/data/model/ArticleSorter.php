<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/06/18
 * Time: 17:39
 */

namespace wfw\engine\package\news\data\model;


use wfw\engine\core\data\model\ArraySorter;
use wfw\engine\core\data\model\IModelObject;

/**
 * Trie les article du plus récent au plus ancien
 */
final class ArticleSorter extends ArraySorter {
	/**
	 * @param IModelObject $o1
	 * @param IModelObject $o2
	 * @return int
	 */
	public function compare(IModelObject $o1, IModelObject $o2): int {
		return -1 * parent::compare($o1, $o2);
	}
}