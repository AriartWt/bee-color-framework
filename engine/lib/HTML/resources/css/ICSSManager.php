<?php
namespace wfw\engine\lib\HTML\resources\css;

use wfw\engine\lib\HTML\resources\IFileIncluder;

/**
 *  Gère des inclusions CSS
 */
interface ICSSManager extends IFileIncluder {
	/**
	 *  Enregistre une liste de régles CSS
	 * @param string $txt
	 */
	public function registerInline(string $txt):void;
}