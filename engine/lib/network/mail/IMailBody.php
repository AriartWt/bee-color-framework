<?php
namespace wfw\engine\lib\network\mail;

/**
 * Corps de mail
 */
interface IMailBody {
	/**
	 * @return bool
	 */
	public function isHTML():bool;
	
	/**
	 * @return string
	 */
	public function alt():string;
	
	/**
	 * @return string
	 */
	public function __toString();
}