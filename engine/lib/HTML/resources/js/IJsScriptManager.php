<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/10/17
 * Time: 07:53
 */

namespace wfw\engine\lib\HTML\resources\js;


use wfw\engine\lib\HTML\resources\IFileIncluder;

/**
 *  Gére des inclusions de fichiers et codes JavaScript
 */
interface IJsScriptManager extends IFileIncluder
{
    /**
     *   Ajoute une nouvelle variable si elle n'est pas présente
     *
     * @param  string $key   Clé (nom de variable)
     * @param  mixed  $value Valeur à écrire (doit pouvoir être encodée à l'aide de json_encode)
     *
     * @throws Exception
     */
    public function registerVar(string $key,$value):void;
    /**
     *   Supprime une variable de la liste des inclusions si elle est présente
     * @param  string    $key Nom de la variable à supprimer
     * @throws Exception si la variable n'existe pas et que le currentFlag est à self::EMIT_EXCEPTION_OFF
     * @return void
     */
    public function unregisterVar(string $key):void;
    /**
     *   Permet de savoir si une variable est déjà enreigstrée
     * @param  string    $key Variable à tester
     * @return boolean        True si la variable existe, false sinon
     */
    public function isRegisteredVar(string $key):bool;
}