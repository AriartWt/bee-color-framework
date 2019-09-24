<?php
namespace wfw\engine\core\data\model;

/**
 * Permet de trier un tableau de IModelObject
 */
interface IArraySorter {
	/**
	 * @param array $arr Tableau à trier
	 * @return array Tableau trié
	 */
	public function sort(array $arr):array;

	/**
	 * @param IModelObject $o1 Objet à comparer à $o2
	 * @param IModelObject $o2 Objet de compraison
	 * @return int 1 si o1 > o2, -1 sinon. 0 si égaux
	 */
	public function compare(IModelObject $o1, IModelObject $o2):int;

	/**
	 * @return string
	 */
	public function __toString();
}