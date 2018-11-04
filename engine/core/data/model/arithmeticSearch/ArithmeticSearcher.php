<?php
namespace wfw\engine\core\data\model\arithmeticSearch;

use wfw\engine\core\data\model\ICrossModelAccess;
use wfw\engine\core\data\model\IModelSearcher;

/**
 *  Implementation d'un repositorySearcher avec des expressions arithmetiques de base.
 */
final class ArithmeticSearcher implements IModelSearcher {

	/** @var IArithmeticSolver $_solver */
	private $_solver;

	/**
	 * ArithmeticSearcher constructor.
	 *
	 * @param IArithmeticSolver $solver Solver d'expression
	 */
	public function __construct(IArithmeticSolver $solver) {
		$this->_solver = $solver;
	}

	/**
	 *  Effectue une recherche dans $all et $indexed des objets correspondants à $expr
	 *
	 *        L'expression arithmétique $expr doit être écrite en notation INFIXE. Elle est ensuite traduite en notation POSTFIXE
	 *        par le parser.
	 *
	 *        Supporte les opérateurs suivants :
	 *        + : Combine les indexes/resultats des tableaux de droite et de gauche en conservant les doublons
	 *        - : Retire les résultats/indexes contenus dans le tableaux de droite du tableau de gauche
	 *        & : Ne garde que les éléments communs entre les resultats/indexes de gauche et de droite.
	 *        | : Combine les indexes/resultats des tableaux de droite et de gauche en supprimant les doublons
	 *        () : Permettent de prioriser les calculs. Toute parenthése ouverte doit être fermée. Les parenthéses peuvent être imbriquées.
	 *         : : Permet d'effectuer une recherche sur un sous ensemble : 'expr1 : expr2'
	 *
	 *        Priorité des opérateurs :
	 *        + , - , | : 1
	 *        &         : 10
	 *        ( ... )   : 100
	 *
	 *        Les indexes doivent être des indexes de tableau valides
	 *          OU des objets dérivés de Specification castés en chaine de caractère
	 *          OU des objets implémentant la ISpecification, IModelSorter ou ICrossModelQuery castés en chaine de caractère grâce à unpack("H*",$obj)
	 *        Attentions : Les objets étendant la classe Specification (ou les ISpecification castés à l'aide de unpack()
	 *                     font l'objet d'une recherche dans $all. Les indexes sont donc à privillégier pour des soucis de performances,
	 *                     ou alors privilégier l'opérateur : pour appliquer la specification à un sous ensemble
	 *
	 *        Commandes spéciales concernant l'index ID :
	 *        Si un index ID est défini, il est possible de définir une liste d'identifiants comme ceci:
	 *          "... id=xxx-xxxx-xxxxxxxxx-xxxx,... ..."
	 *          Il peut y avoir autant d'identifiants que souhaités, séparés par des virgules.
	 *
	 * @code
	 *      final class MoreThanTenSpecification extends LeafSpecification{
	 *          public function isSatisfiedBy($candidate):bool{
	 *              return $candidate > 10;
	 *          }
	 *      }
	 *      $searcher = new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser()));
	 *      $moreThanTen = new MoreThanTenSpecification();
	 *      $res = $searcher->search("$moreThanTen & (b-c) | a",
	 *              [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16],
	 *              [
	 *                  "a" => [1,3,5,7,9,11,12,13,15],
	 *                  "b" => [2,4,6,8,10,12,14,16],
	 *                  "c" => [1,2,5,6,8,9,13,14,16]
	 *              ]);
	 *      //$res = [12,1,3,5,7,9,11,13,15];
	 * @endcode
	 *
	 * @param mixed                  $expr Requête (expression arithmétique)
	 * @param array                  $all
	 * @param array                  $indexed
	 * @param null|ICrossModelAccess $access
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function search($expr,array $all,array $indexed,?ICrossModelAccess $access = null):array{
		if(is_string($expr)){
			return $this->_solver->solve($expr,$all,$indexed,$access);
		}else{
			throw new \InvalidArgumentException(
				"ArithmeticSercher can only deal with arithmetic string expression !"
			);
		}
	}
}