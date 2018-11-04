<?php
namespace wfw\engine\lib\cli\argv;

/**
 *  Parser
 */
interface IArgvParser {
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