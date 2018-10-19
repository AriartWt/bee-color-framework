<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/02/18
 * Time: 06:24
 */

namespace wfw\engine\core\view;

/**
 * Vue
 */
interface IView
{
    /**
     * @return string[] Liste des headers à déclarer pour la vue courante
     */
    public function getHeaders():array;

    /**
     * @param string[] ...$header Header à déclarer
     */
    public function addHeader(string ...$header):void;

    /**
     * Permet d'effectuer des actions sur le buffer pour le modifier après le rendu.
     *
     * @param callable $action Le callable sous la forme : function(string):string
     * @return string Identifiant à inscrire dans la sortie afin que le remplacement puisse avoir
     *                lieu.
     */
    public function registerPostAction(callable $action):string;

    /**
     * Applique les actions sur le rendu et retourne le résultat.
     * @param string $buffer Rendu à modifier
     * @return string
     */
    public function applyPostActions(string $buffer):string;

    /**
     * @return string Retourne le rendu de la vue.
     */
    public function render():string;

    /**
     * @return array Liste d'informations sur la vue
     */
    public function infos():array;
}