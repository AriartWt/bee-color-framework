<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 15:38
 */

namespace wfw\engine\lib\network\mail;

/**
 *  Mail attachment
 */
interface IMailAttachment {
	/**
	 * @return string
	 */
	public function path():string;
	
	/**
	 * @return null|string
	 */
	public function name():?string;
	
	/**
	 * @return null|string
	 */
	public function encoding():?string;
	
	/**
	 * @return null|string
	 */
	public function disposition():?string;
	
	/**
	 * @return null|string
	 */
	public function type():?string;
}