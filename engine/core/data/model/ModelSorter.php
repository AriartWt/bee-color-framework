<?php
namespace wfw\engine\core\data\model;

/**
 * Sorter de base. Permet de faire des tri combinés, et de limiter le résultat par un offset
 * et un index de départ.
 */
final class ModelSorter extends AbstractModelSorter {
	/** @var IArraySorter[] $_sorters */
	private $_sorters;
	/** @var int $_offset */
	private $_offset;
	/** @var int $_length */
	private $_length;

	/**
	 * ModelSorter constructor.
	 *
	 * @param int            $offset Retourne les résultat à partir de cet offset
	 * @param int            $length Nombre de résultats retournés. Si 0, ne limite pas
	 * @param IArraySorter[] $sorters Dans l'ordre, les fonctions compare seront executées.
	 *                                tant qu'elle renvoie 0 (égalité), passe au sorter suivant.
	 */
	public function __construct(int $offset=0, int $length=0,IArraySorter... $sorters) {
		$this->_sorters = $sorters;
		$this->_offset = $offset;
		$this->_length = $length;
	}

	/**
	 * @param array $arr Tableau à trier
	 * @return array Tableau trié
	 */
	public function sort(array $arr): array {
		usort($arr,[$this,"compare"]);
		return array_slice(
			$arr,
			$this->_offset,
			$this->_length > 0 ? $this->_length : null
		);
	}

	/**
	 * @param IModelObject $o1 Objet à comparer à $o2
	 * @param IModelObject $o2 Objet de compraison
	 * @return int 1 si o1 > o2, -1 sinon. 0 si égaux
	 */
	public function compare(IModelObject $o1, IModelObject $o2): int {
		if(count($this->_sorters) === 0) return $o1->compareTo($o2);
		else foreach($this->_sorters as $sorter){
			$res = $sorter->compare($o1,$o2);
			if($res !== 0) return $res;
		}
		return 0;
	}
}