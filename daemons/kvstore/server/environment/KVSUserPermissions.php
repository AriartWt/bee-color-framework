<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 01:31
 */

namespace wfw\daemons\kvstore\server\environment;

use wfw\engine\lib\PHP\types\PHPEnum;

/**
 *  Liste des permissions possibles pour les utilisateurs et groupes.
 */
final class KVSUserPermissions extends PHPEnum
{
    public const READ = 1;
    public const WRITE = 2;
    public const ADMIN = 4;

    /**
     *  Retourne le nombre correspondant au mode (écriture littérale sensible à la casse)
     *
     * @param string $mode Mode
     *
     * @return int
     */
    public static function get(string $mode):int
    {
        return (int)parent::get($mode);
    }

    /**
     *  Retourne true si $flag contient la permission self::READ
     *
     * @param int $flag Permission à tester
     *
     * @return bool
     */
    public static function readGranted(int $flag):bool{
        return $flag & self::READ;
    }

    /**
     *  Retourne true si $flag contient la permission self::WRITE
     *
     * @param int $flag Permission à tester
     *
     * @return bool
     */
    public static function writeGranted(int $flag):bool{
        return $flag & self::WRITE;
    }

    /**
     *  Retourne true si $flag contient la permission self::ADMIN
     *
     * @param int $flag Permission à tester
     *
     * @return bool
     */
    public static function adminGranted(int $flag):bool{
        return $flag & self::ADMIN;
    }
}