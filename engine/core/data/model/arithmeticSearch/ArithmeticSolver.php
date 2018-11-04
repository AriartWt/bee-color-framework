<?php
namespace wfw\engine\core\data\model\arithmeticSearch;

use wfw\engine\core\data\model\CrossModelQuery;
use wfw\engine\core\data\model\ICrossModelAccess;
use wfw\engine\core\data\model\ICrossModelQuery;
use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\specification\ISpecification;

/**
 *  Résoud une expression arithmetique
 */
final class ArithmeticSolver implements IArithmeticSolver {
	/** @var ArithmeticParser $_parser */
	private $_parser;

	/**
	 * ArithmeticSolver constructor.
	 *
	 * @param ArithmeticParser $parser Parser
	 */
	public function __construct(ArithmeticParser $parser) {
		$this->_parser = $parser;
	}

	/**
	 * @param string                 $expr
	 * @param array                  $all
	 * @param array                  $indexes
	 * @param null|ICrossModelAccess $access
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function solve(string $expr, array $all, array $indexes,?ICrossModelAccess $access = null):array{
		$expr = $this->_parser->parse($expr,$indexes);
		if(isset($indexes['+custom_asserts'])){
			$custom = $indexes['+custom_asserts'];
			unset($indexes['+custom_asserts']);
			foreach($custom as $assert){
				$indexes[$assert] = $this->solveExpr(
					$indexes[$assert][0],
					$all,
					$indexes,
					$access
				);
			}
		}
		return $this->solveExpr(
			$expr,
			$all,
			$indexes,
			$access
		);
	}

	/**
	 *  Résoud une expression arithmetique
	 *
	 * @param ArithmeticExpression   $expr    Expression à résoudre
	 *
	 * @param array                  $all
	 * @param array                  $indexes Liste des indexes
	 * @param null|ICrossModelAccess $access  Acces cross-model pour les cross-models queries
	 * @return array résultat
	 * @throws \InvalidArgumentException
	 */
	private function solveExpr(
		ArithmeticExpression $expr,
		array &$all,
		array &$indexes,
		?ICrossModelAccess $access = null
	):array{
		$expr = $expr->getExpression();
		return $this->process($expr,$all,$indexes,$access);
	}

	/**
	 *  Procéde à la résolution
	 *
	 * @param array                  $stack    Pile d'opérations
	 * @param array                  $all
	 * @param array                  $indexes  Liste des indexes
	 * @param null|ICrossModelAccess $access
	 * @param null|string            $operator Opérateur précédent
	 *
	 * @return array résultat
	 * @throws \InvalidArgumentException
	 */
	private function process(array &$stack,array &$all,array &$indexes=[],?ICrossModelAccess $access = null,?string $operator=null):array{
		if(count($stack)===0) return [];
		$first = array_pop($stack);
		if(!is_array($first)){
			$right = $this->process($stack,$all,$indexes,$access,$first);
		}else{
			$right = $this->ifExprReplace($indexes,$first);
		}
		if(!is_null($operator)){
			$second = array_pop($stack);
			if(!is_array($second)){
				$left = $this->process($stack,$all,$indexes,$access,$second);
			}else{
				$left = $this->ifExprReplace($indexes,$second);
			}
			return $this->doOperation($left,$operator,$right,$all,$access);
		}else{
			return $this->ifSpecApply($right,$all,$access);
		}
	}

	/**
	 *  Appliquer un operateur sur deux tableaux
	 *
	 * @param array                  $left     Operande de gauche
	 * @param string                 $operator Operateur
	 * @param array                  $right    Operande de droite
	 *
	 * @param array                  $all
	 * @param null|ICrossModelAccess $access
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	private function doOperation(
		array $left,
		string $operator,
		array $right,
		array &$all,
		?ICrossModelAccess $access = null
	):array{
		switch($operator){
			case "+":
				$left = $this->ifSpecApply($left,$all,$access);
				$right = $this->ifSpecApply($right,$all,$access);
				$res = array_merge($left,$right);
				break;
			case "-":
				$left = $this->ifSpecApply($left,$all,$access);
				$right = $this->ifSpecApply($right,$all,$access);
				$tmp = [];
				foreach ($left as $l){
					$tmp[spl_object_id($l)] = $l;
				}
				$len = count($tmp);
				foreach($right as $r){
					if($len > 0){
						if(isset($tmp[spl_object_id($r)])){
							unset($tmp[spl_object_id($r)]);
							$len--;
						}
					}else break;
				}
				$res = array_values($tmp);
				break;
			case "&":
				$left = $this->ifSpecApply($left,$all,$access);
				$right = $this->ifSpecApply($right,$all,$access);
				$tmp = [];
				foreach($left as $l){
					$tmp[spl_object_id($l)]=$l;
				}
				$res = [];
				foreach($right as $r){
					if(isset($tmp[spl_object_id($r)])) $res[] = $r;
				}
				break;
			case "|":
				$left = $this->ifSpecApply($left,$all,$access);
				$right = $this->ifSpecApply($right,$all,$access);
				$tmp = [];
				foreach($left as $l){
					$tmp[spl_object_id($l)] = $l;
				}
				foreach($right as $r){
					$tmp[spl_object_id($r)] = $r;
				}
				return array_values($tmp);
				break;
			case ":" :
				if(count($left)===1 && (
					$left[0] instanceof ISpecification
					|| $left[0] instanceof ICrossModelQuery
					|| $left[0] instanceof IArraySorter
				)){
					$right = $this->ifSpecApply($right,$all,$access);
					$res = $this->ifSpecApply($left,$right,$access);
				}else throw new \InvalidArgumentException("Cann'nt apply a set to another set !");
				break;
			default:
				throw new \InvalidArgumentException("Unsupported operator : $operator");
		}
		return $res;
	}

	/**
	 * Si $probablySpec contient un élément de type ISpecification, applique la spec au jeu de
	 * données $data et retourne le résultat.
	 *
	 * @param array                  $probablySpec
	 * @param array                  $data
	 * @param null|ICrossModelAccess $access
	 * @return array
	 */
	private function ifSpecApply(array $probablySpec,array &$data,?ICrossModelAccess $access=null){
		if(isset($probablySpec[0])){
			if($probablySpec[0] instanceof CrossModelQuery)
				$probablySpec[0] = $access->execute($probablySpec[0]);
			if($probablySpec[0] instanceof ISpecification){
				$res = [];
				foreach ($data as $d){
					if($probablySpec[0]->isSatisfiedBy($d)) $res[] = $d;
				}
				return $res;
			}else if($probablySpec[0] instanceof IArraySorter){
				return $probablySpec[0]->sort($data);
			}
		}
		return $probablySpec;
	}

	/**
	 * @param array $indexes   Liste des indexes définis
	 * @param array $maybeExpr Tableau contenant peut-être une expression
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	private function ifExprReplace(array &$indexes,array $maybeExpr):array{
		if(count($maybeExpr) === 2){
			$first = $maybeExpr[array_keys($maybeExpr)[0]];
			if($first instanceof ArithmeticExpression){
				if(isset($indexes[array_keys($maybeExpr)[1]])){
					return $indexes[array_keys($maybeExpr)[1]];
				}else{
					throw new \InvalidArgumentException("Undefined index $maybeExpr[1] !");
				}
			}
		}
		return $maybeExpr;
	}
}