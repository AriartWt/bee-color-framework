<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/01/18
 * Time: 03:52
 */

namespace wfw\engine\lib\data\string\compressor;

/**
 * Compresse et decompresse des données.
 */
interface IStringCompressor
{
    /**
     * Compresse la chaine passée en paramètre.
     *
     * @param string $string Données à compresser
     * @return string Données compressées
     */
    public function compress(string $string):string;

    /**
     * Decompresse la chaine passée en paramètre. (Précédement compressée avec compress)
     *
     * @param string $string Données à compresser
     * @return string Données décompressées
     */
    public function decompress(string $string):string;
}