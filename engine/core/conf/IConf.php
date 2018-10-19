<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 12/10/17
 * Time: 08:26
 */

namespace wfw\engine\core\conf;

use stdClass;

/**
 *  Classe de configurations
 */
interface IConf
{
    /**
     *  Retourne l'objet stdClass contenant les configurations
     * @return stdClass
     */
    public function getRawConf():stdClass;
    /**
     *  Permet de savoir si l'instance courante est en mode sauvegarde automatique
     * @return bool
     */
    public function autoSaveModeEnabled():bool;
    /**
     *  Permet de changer l'état de la sauvegarde automatique
     * @param bool $auto Nouveau mode de sauvegarde
     */
    public function setAutoSaveMode(bool $auto):void;

    /**
     *  Intégre (fusionne) la configuration passée en paramètre avec la configuration courante
     *
     * @param FileBasedConf|IConf $conf Configuration à intégrer
     */
    public function merge(IConf $conf):void;
    /**
     *  Reconstruit une configuration en réappliquant les données de chaque configurations dans la liste _confMerges
     */
    public function rebuild():void;
    /**
     *  Retourne une configuration
     *
     * @param string $path Clé d'accés à la valeur de la configuration
     *
     * @return mixed
     */
    public function get(string $path);
    /**
     *  Modifie une clé de configuration et l'enregistre
     * @param string $path  Clé de configuration à modifier
     * @param        $value Nouvelle valeur de la clé de configuration
     */
    public function set(string $path, $value):void;

    /**
     *  Renvoie true si la clé de configuratione existe, false sinon
     * @param string $key Clé à tester
     *
     * @return bool
     */
    public function existsKey(string $key):bool;

    /**
     *  Supprime une clé de configuration
     *
     * @param string $key Clé à supprimer
     */
    public function removeKey(string $key);

    /**
     *  Retourne une clé de configuration booléenne
     *
     * @param string $key Clé
     *
     * @return bool|null
     */
    public function getBoolean(string $key):?bool;
    /**
     *  Retourne une clé de configuration entière
     *
     * @param string $key Clé
     *
     * @return int|null
     */
    public function getInt(string $key):?int;

    /**
     *  Retourne un tableau
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getArray(string $key):?array;
    /**
     *  Retourne une clé de configuration chaine de cractère
     * @param string $key Clé
     *
     * @return null|string
     */
    public function getString(string $key):?string;
    /**
     *  Retourne la valeur d'un clé de configuration float
     * @param string $key Clé
     *
     * @return float|null
     */
    public function getFloat(string $key):?float;
    /**
     *  Retourne la valeur d'une clé de configuration stdClass
     *
     * @param string $key Clé
     *
     * @return null|stdClass
     * @throws InvalidTypeException
     */
    public function getObject(string $key):?stdClass;

    /**
     * Persiste la configuration courante
     */
    public function save():void;
}