<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/02/18
 * Time: 07:40
 */

namespace wfw\engine\lib\HTML\text;

/**
 * Permet d'introduire des césures dans un texte.
 */
interface IHyphenator
{
    /**
     * @param string $text Texte à césurer.
     * @return string Texte césuré
     */
    public function hyphenate(string $text):string;
}