<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 06:55
 */

namespace wfw\daemons\kvstore\server\containers\data\storages;

use wfw\daemons\kvstore\server\containers\data\KVSStorageModeManager;

/**
 *  Les clés qui se trouvent sur le disque sont mises en cache en mémoire.
 *        Toutes les clés sont sauvegardées sur le disque.
 */
final class InMemoryPersistedOnDisk implements KVSStorageModeManager
{
    /**
     * @var InMemoryOnly $_memoryStorage
     */
    private $_memoryStorage;
    /**
     * @var OnDiskOnly $_diskStorage
     */
    private $_diskStorage;

    /**
     * InMemoryPersistedOnDiskStorage constructor.
     *
     * @param InMemoryOnly $memoryStorage Gestionnaire de stockage en mémoire
     * @param OnDiskOnly   $diskStorage   Gestionnaire de stockage sur le disque
     */
    public function __construct(InMemoryOnly $memoryStorage, OnDiskOnly $diskStorage)
    {
        $this->_diskStorage = $diskStorage;
        $this->_memoryStorage = $memoryStorage;
    }

    /**
     *  Obtient la valeur associées à une clé
     *
     * @param string $key Clé dont on souhaite obtenir les données
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if(!$this->_memoryStorage->exists($key)){
            //Si la clé n'existe pas en mémoire, on la cherche sur le disque.
            $data = $this->_diskStorage->get($key);
            //que la clé existe ou non, on sauvegarde le résultat en mémoire.
            //Cela évite une recherche inutile sur le disque pour la fois suivante.
            $this->_memoryStorage->set($key,null);
            return $data;
        }else{
            return $this->_memoryStorage->get($key);
        }
    }

    /**
     *  Enregistre une valeur par une clé
     *
     * @param string      $key  Clé de stockage
     * @param mixed       $data Données associées
     */
    public function set(string $key, $data)
    {
        $this->_diskStorage->set($key,$data);
        $this->_memoryStorage->set($key,$data);
    }

    /**
     *  Supprime une clé et les données associées
     *
     * @param string $key Clé à supprimer
     */
    public function remove(string $key)
    {
        $this->_diskStorage->remove($key);
        $this->_memoryStorage->remove($key);
    }

    /**
     * @param string $key
     *
     * @return bool True si la clé existe, false sinon
     */
    public function exists(string $key): bool
    {
        //Comme on met en cache en mémoire les résultats sur des clés qui n'existent pas,
        //on cherche sur le disque l'existence d'une clé.
        return $this->_diskStorage->exists($key);
    }
}