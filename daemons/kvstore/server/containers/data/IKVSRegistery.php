<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 00:28
 */

namespace wfw\daemons\kvstore\server\containers\data;

/**
 *  Registre de clés.
 */
interface IKVSRegistery extends \Iterator,\ArrayAccess
{
    /**
     *  Ajoute une clé au registre
     *
     * @param IKVSRegisteryKey $key Clé à ajouter
     */
    public function add(IKVSRegisteryKey $key);

    /**
     * @param string $name Nom de la clé à obtenir
     *
     * @return null|IKVSRegisteryKey Clé demandée
     */
    public function get(string $name): ?IKVSRegisteryKey;

    /**
     * @param string $name Clé à supprimer
     */
    public function remove(string $name);

    /**
     * @param string $name Nom de la clé
     * @return bool True si la clé existe dans le registre, false sinon
     */
    public function exists(string $name):bool;

    /**
     * @return int Nombre de clés dans le registre
     */
    public function getLength():int;
}