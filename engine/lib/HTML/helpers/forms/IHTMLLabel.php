<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/03/18
 * Time: 03:59
 */

namespace wfw\engine\lib\HTML\helpers\forms;

/**
 * Label HTML
 */
interface IHTMLLabel {
	/**
	 * @return null|string Identifiant de l'input concerné par le label
	 */
	public function getId():?string;
    public function __toString();
}