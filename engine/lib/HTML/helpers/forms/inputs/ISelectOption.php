<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/03/18
 * Time: 12:20
 */

namespace wfw\engine\lib\HTML\helpers\forms\inputs;

/**
 * Option d'un select
 */
interface ISelectOption
{
    /**
     * Marque l'option comme selectionnée par défaut par le select.
     */
    public function selected():void;

    /**
     * Relache la selection d'une option pour la valeur par défaut du select
     */
    public function unselect():void;

    /**
     * @return string Valeur contenue dans le select
     */
    public function getValue():string;

    /**
     * @return string
     */
    public function __toString();
}