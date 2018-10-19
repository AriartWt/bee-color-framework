<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 03/12/17
 * Time: 06:29
 */

namespace wfw\engine\core\data\model\arithmeticSearch;

/**
 *  Contient une pile représentant une expression postfixée
 */
final class ArithmeticExpression
{
    /** @var array $_expr */
    private $_expr;

    /**
     * ArithmeticExpression constructor.
     *
     * @param array $expr Expression
     */
    public function __construct(array $expr)
    {
        $this->_expr = $expr;
    }

    /**
     * @return array
     */
    public function getExpression():array{
        return $this->_expr;
    }
}