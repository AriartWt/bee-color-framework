<?php
namespace wfw\engine\lib\HTML\text;

/**
 * Permet d'introduire des césures dans un texte.
 */
interface IHyphenator {
	/**
	 * @param string $text Texte à césurer.
	 * @return string Texte césuré
	 */
	public function hyphenate(string $text):string;
}