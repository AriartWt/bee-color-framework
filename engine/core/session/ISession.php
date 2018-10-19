<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/02/18
 * Time: 11:09
 */

namespace wfw\engine\core\session;

/**
 * Session PHP
 */
interface ISession
{
    /**
     *   Détruit la session
     */
    public function destroy();
    /**
     * Crée un dossier temporaire et en retourne le chemin
     *
     * @return string Chemin d'accés au dossier temporaire
     */
    public function getTmp():string;
    /**
     *	Permet d'écrire dans la session
     *	@param string $key est la clé à inscrire dans la session
     *	@param mixed $value est la valeur correspondante
     **/
    public function set($key,$value):void;
    /**
     *	 Permet de lire une clé
     *
     *	@param string $key nom de la clé à lire
     * @return mixed|null retourne la session si aucune clé passée, retourne la valeur de la clé si
     *                    elle existe, null sinon
     **/
    public function get($key=null);
    /**
     *  Permet de supprimer une clé dans la session
     * @param string $key clé à supprimer
     */
    public function remove($key):void;

    /**
     * Remplace la valeur d'une clé par une nouvelle valeur
     * @param  string $key      Clé à rempalcer
     * @param  mixed  $newValue Nouvelle valeur à insérer
     */
    public function replace($key,$newValue);

    /**
     * Permet de savoir si une clé est présente dans la session
     *
     * @param string $key Clé à tester
     *
     * @return bool
     */
    public function exists($key):bool;

    /**
     * @return bool Permet de savoir si un utilisateur loggé est enregistré.
     */
    public function isLogged():bool;
}