<?php
namespace wfw\engine\core\data\model\arithmeticSearch;

/**
 * Parse une expression arithmetique
 */
interface IArithmeticParser {
	/**
	 *  Parse une expression littérale infixée en une ArithmeticExpression executable postfixée
	 *
	 * @param string $expression Expression à parser
	 * @param array  $indexes    Liste des indexes sur ces données
	 *
	 * @return ArithmeticExpression
	 */
	public function parse(string $expression, array &$indexes):ArithmeticExpression;
}