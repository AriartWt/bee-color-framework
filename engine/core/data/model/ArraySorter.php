<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/06/18
 * Time: 12:12
 */

namespace wfw\engine\core\data\model;

/**
 * Utilise la fonction usort native pour comparer des IModelObjects
 * Etendre la classe ArraySorter permet d'overrider les deux méthodes sort qui agit sur
 * l'algorythme de tri, et compare qui agit sur la maniére dont on compare deux objets entre eux.
 */
class ArraySorter extends AbstractModelSorter{

	/**
	 * @param array $arr Tableau à trier
	 * @return array Tableau trié
	 */
	public function sort(array $arr): array {
		usort($arr,[$this,"compare"]);
		return $arr;
	}

	/**
	 * @param IModelObject $o1 Objet à comparer à $o2
	 * @param IModelObject $o2 Objet de compraison
	 * @return int 1 si o1 > o2, -1 sinon. 0 si égaux
	 */
	public function compare(IModelObject $o1, IModelObject $o2): int {
		return $o1->compareTo($o2);
	}
}