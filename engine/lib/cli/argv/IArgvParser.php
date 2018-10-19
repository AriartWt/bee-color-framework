<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/12/17
 * Time: 09:05
 */

namespace wfw\engine\lib\cli\argv;

/**
 *  Parser
 */
interface IArgvParser
{
    /**
     * @return ArgvOptMap
     */
    public function getOptMap():ArgvOptMap;
    /**
     *  Parse un tableau d'arguments
     *
     * @param array     $argv Arguments
     *
     * @return ArgvParserResult
     */
    public function parse(array $argv):ArgvParserResult;
}