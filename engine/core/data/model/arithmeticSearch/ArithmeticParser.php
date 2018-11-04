<?php
namespace wfw\engine\core\data\model\arithmeticSearch;


use wfw\engine\core\data\model\ICrossModelQuery;
use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\lib\PHP\types\PHPString;
use wfw\engine\lib\PHP\types\Type;

/**
 *  Permet de parser une expression arithmetique infixée en pile postifée.
 */
final class ArithmeticParser implements IArithmeticParser {

	/**
	 *  Permet de savoir si un caractère est un opérateur d'expression valide
	 *
	 * @param string $c Charactère à tester
	 *
	 * @return bool
	 */
	protected function isOperator(string $c):bool{
		return is_numeric(strpos("+-&|()':",$c));
	}

	/**
	 *  Retourne la priorité d'un opérateur
	 *
	 * @param string $c Opérateur
	 *
	 * @return int
	 */
	protected function getOperatorPriority(string $c):int{
		if($c ==="+" || $c==="-" || $c==="|"){
			return 1;
		}else if($c === "&"){
			return 10;
		}else if($c === ":"){
			return 50;
		}else{
			return -1;
		}
	}

	/**
	 *  Retourne la valeur d'une opérande de l'expression en cours de parsing
	 *
	 * @param string $index   Index (nom de l'opérande)
	 * @param array  $indexes Données indéxées
	 *
	 * @return array
	 */
	private function getIndexValue(string $index, array &$indexes):array{
		if(isset($indexes[$index])){
			return $indexes[$index];
		}else{
			if(strpos($index,"id=")===0 && isset($indexes["id"])){
				//Commande spéciale pour les identifiants
				$needed = explode(",",substr($index,3));
				$res=[];
				foreach($needed as $n){
					//On supprime les espaces
					$n = str_replace(" ","",$n);
					//On récupère l'identifiant
					if(isset($indexes["id"][$n])){
						$res[]=$indexes["id"][$n];
					}
				}
				return $res;
			}else if(ctype_xdigit($index)){
				return [$this->tryDecode($index)];
			}else{
				throw new \InvalidArgumentException("Undefined index $index !");
			}
		}
	}

	/**
	 * Tente de décoder un index hexadécimal qui semble être une spécification encodée
	 * @param string $str
	 * @return ISpecification|ICrossModelQuery
	 * @throws \InvalidArgumentException
	 */
	private function tryDecode(string $str){
		$spec = pack("H*",$str);
		try {
			/** @var ISpecification|ICrossModelQuery $spec */
			$res = unserialize($spec);
			if($res instanceof ISpecification
				|| $res instanceof ICrossModelQuery
				|| $res instanceof IArraySorter
			){
				return $res;
			}else{
				throw new \ParseError(
					(new Type($res))->get()." doesn't implements "
					.ISpecification::class.", ".ICrossModelQuery::class." or ".IArraySorter::class
				);
			}
		}catch(\Exception $e){
			throw new \InvalidArgumentException("Can't decode $str !");
		}
	}

	/**
	 *  Parse une expression littérale infixée en une ArithmeticExpression executable postfixée
	 *
	 * @param string $expression Expression à parser
	 * @param array  $indexes    Liste des indexes sur ces données
	 *
	 * @return ArithmeticExpression
	 */
	public function parse(string $expression, array &$indexes):ArithmeticExpression{
		$expression = str_replace([' ',"\n"],'', $expression);
		$declarations = array_reverse(explode(';',$expression));
		$len = count($declarations);
		if($len>1) $indexes["+custom_asserts"]=[];
		while($len>1){
			$rawAssert = array_pop($declarations);
			$asserts = explode("=",$rawAssert);
			if(count($asserts)!==2)
				throw new \ParseError(
					"Invalid temp index declaration :"
					." index=expression is required but '$rawAssert' given"
				);
			$indexes[$asserts[0]] = [$this->parseExpr($asserts[1],$indexes),$asserts[0]];
			$indexes["+custom_asserts"][] = $asserts[0];
			$len--;
		}
		return $this->parseExpr(array_pop($declarations),$indexes);
	}

	/**
	 * @param string $expression Expression à parser
	 * @param array  $indexes    Liste des indexes
	 * @return ArithmeticExpression
	 * @throws \InvalidArgumentException
	 */
	private function parseExpr(string $expression, array &$indexes):ArithmeticExpression{
		$stack = [];//Stack de travail
		$parsed = [];//Pile contenant les indexes et les opérateurs

		$expr = str_replace(" ","",$expression);
		$expr = new PHPString($expr);
		$arr = $expr->split();

		$currentIndex="";
		$ignoreOperators = false;

		foreach($arr as $c){
			if($c === "'"){
				$ignoreOperators = !$ignoreOperators;
				continue;
			}
			if(!$this->isOperator($c) || $ignoreOperators){
				$currentIndex.=$c;
				continue;
			}else{
				if(!empty($currentIndex)){
					$parsed[]=$this->getIndexValue($currentIndex,$indexes);
					$currentIndex = "";
				}
				if($c==="("){
					$stack[]=$c;
				}else if($c===")"){
					while(!empty($stack) && $stack[count($stack)-1]!=="("){
						$parsed[] = array_pop($stack);
					}
					if(!empty($stack) && $stack[count($stack)-1]==="("){
						array_pop($stack);
					}else{
						throw new \InvalidArgumentException("Invalid expression : $expression");
					}
				}else{
					if(!empty($stack) && $this->getOperatorPriority($c)
						<= $this->getOperatorPriority($stack[count($stack)-1]))
					{
						$parsed[]=array_pop($stack);
					}
					$stack[]=$c;
				}
			}
		}

		if($ignoreOperators){
			throw new \InvalidArgumentException(
				"Invalid expression : $expression. A closing quote is missing. !"
			);
		}

		if(!empty($currentIndex)){
			$parsed[]=$this->getIndexValue($currentIndex,$indexes);
		}

		while(!empty($stack)){
			$parsed[] = array_pop($stack);
		}

		return new ArithmeticExpression($parsed);
	}
}