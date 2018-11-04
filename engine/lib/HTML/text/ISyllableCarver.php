<?php
namespace wfw\engine\lib\HTML\text;

/**
 * Découpeur de syllables.
 */
interface ISyllableCarver {
	/**
	 * @param string $word Mot à découper
	 * @return string[] syllables
	 */
	public function carve(string $word):array;
}