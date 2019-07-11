<?php

namespace wfw\engine\lib\logger;

/**
 * Class ConsoleLogFormater
 *
 * @package wfw\engine\lib\logger
 */
final class ConsoleLogFormater implements ILogFormater{
	/** @var ILogFormater $_formater */
	private $_formater;

	protected const WARN = [ "color" => 33 ]; //33 : orange
	protected const LOG = [ "color" => 0 ];//0 : default
	protected const ERR = [ "color" => 41 ];//31 : red background
	protected const DEBUG = [ "color" => 96 ];//96 : light cyan

	/**
	 * ConsoleLogFormater constructor.
	 *
	 * @param ILogFormater $formater
	 */
	public function __construct(ILogFormater $formater) {
		$this->_formater = $formater;
	}

	/**
	 * @param string $message Message à formater
	 * @param int    $loglevel
	 * @return string Log formaté
	 */
	public function format(string $message, int $loglevel = -1): string {
		switch($loglevel){
			case ILogger::ERR :
				$confFormat = self::ERR;
				break;
			case ILogger::WARN :
				$confFormat = self::WARN;
				break;
			case ILogger::DEBUG :
				$confFormat = self::DEBUG;
				break;
			default :
				$confFormat = self::LOG;
				break;
		}
		return "\e[{$confFormat["color"]}m".$this->_formater->format($message,$loglevel)."\e[0m";
	}
}