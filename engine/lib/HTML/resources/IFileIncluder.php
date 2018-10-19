<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/10/17
 * Time: 07:43
 */

namespace wfw\engine\lib\HTML\resources;

/**
 *  Permet de gérer des inclusions de fichiers
 */
interface IFileIncluder
{
    public const EMIT_EXCEPTION_ON=0;
    public const EMIT_EXCEPTION_OFF=1;

    /**
     * Enregistre l'url d'un fichier
     * @param string $filePath URL du fichier
     */
    public function register(string $filePath):void;

    /**
     * Supprime l'url d'un fichier enregistré
     * @param string $filePath URL du fichier
     */
    public function unregister(string $filePath):void;

    /**
     * Retourne une chaine de cractère contenant les balises d'inclusion des différents fichiers
     * @param string $add_to_url Suffixe à ajouter à l'url de tous les fichiers
     * @return string
     */
    public function write(string $add_to_url=""):string;

    /**
     * Permet de tester l'existence d'une url de fichier dans le registre
     * @param string $filePath URL du fichier à tester
     * @return bool
     */
    public function isRegistered(string $filePath):bool;
}