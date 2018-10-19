<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/02/18
 * Time: 07:43
 */

namespace wfw\engine\lib\HTML\text;

/**
 * Découpeur de syllables.
 */
interface ISyllableCarver
{
    /**
     * @param string $word Mot à découper
     * @return string[] syllables
     */
    public function carve(string $word):array;
}