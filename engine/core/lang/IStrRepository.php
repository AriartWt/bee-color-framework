<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/02/18
 * Time: 07:10
 */

namespace wfw\engine\core\lang;
use stdClass;

/**
 * Repository de chaines
 */
interface IStrRepository
{
    /**
     * @param string $key Clé d'obtention d'une chaine
     * @return string Chaine correspondante
     */
    public function get(string $key):string;

    /**
     * @param null|string $basePath Chemin de base ajouté devant les clén pour une résolution
     *                              relative. Null : resolution absolue.
     */
    public function changeBaseKey(?string $basePath=null):void;

    /**
     * Obtient la chaine associée à $key et remplace un motif pré-établit par une occurence de
     * $replace, dans l'ordre dans lequel elles sont spécifiées.
     *
     * @param string   $key         Clé
     * @param string[] ...$replaces Remplacements
     * @return string Chaine correspondante, dont les motifs de remplacement sont substitués par les
     *                termes fournis.
     */
    public function getAndReplace(string $key, string ...$replaces):string;

    /**
     * @param string $key Clé représentant à sous-ensemble de clés
     * @return null|stdClass
     */
    public function getAll(string $key):?stdClass;
}