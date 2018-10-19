<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/05/18
 * Time: 16:14
 */

namespace wfw\cli\tester\launchers;

/**
 * Sequence de tests
 */
interface ITestSequence
{
    /**
     * Permet de lancer la séquence de tests
     */
    public function start():void;
}