<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/01/18
 * Time: 09:47
 */

namespace wfw\engine\core\data\DBAccess\NOSQLDB\kvs;

use wfw\daemons\kvstore\client\KVSClient;
use wfw\daemons\kvstore\server\errors\MustBeLogged;

/**
 *  Accés au KVStore. Tente une reconnexion si la session n'est plus valide a chaque action.
 */
final class KVSAccess extends KVSClient implements IKVSAccess {

    /**
     *  Obtient la valeur associée à une clé.
     *
     * @param string $key Clé
     *
     * @return mixed
     *
     * @throws \wfw\daemons\kvstore\client\errors\AlreadyLogged
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     */
    public function get(string $key)
    {
        try{
            $res = parent::get($key);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            $res = parent::get($key);
        }
        return $res;
    }

    /**
     * @param string   $key         Clé d'enregistrement
     * @param mixed    $data        Données associées à la clé
     * @param float    $ttl         (optionnel défaut : 0) Temps de vie de la clé. Si 0 : pas de limite.
     * @param int|null $storageMode Nouveau mode de stockage de la clé.
     *                              Si null, le stockage de la clé sera celui de l'instance courante.
     *                              Si null aussi, ce sera le mode de stockage par défaut du container.
     *
     * @throws \wfw\daemons\kvstore\client\errors\AlreadyLogged
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
     */
    public function set(string $key, $data, float $ttl = 0, ?int $storageMode = null)
    {
        try{
            parent::set($key, $data, $ttl, $storageMode);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::set($key, $data, $ttl, $storageMode);
        }
    }

    /**
     *  Applique une durée de vie à une clé.
     *
     * @param string $key Clé concernée
     * @param float  $ttl Nouveau temps de vie. Si <0 : la clé n'a plus de limite de vie.
     *
     * @throws \wfw\daemons\kvstore\client\errors\AlreadyLogged
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
     */
    public function setTtl(string $key, float $ttl)
    {
        try{
            parent::setTtl($key, $ttl);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::setTtl($key, $ttl);
        }
    }

    /**
     *  Change le mod ede stockage de la clé.
     *
     * @param string   $key         Clé concernée
     * @param int|null $storageMode Nouveau mode de stockage de la clé.
     *                              Si null, le stockage de la clé sera celui de l'instance courante.
     *                              Si null aussi, ce sera le mode de stockage par défaut du container.
     *
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
     */
    public function changeStorageMode(string $key, ?int $storageMode)
    {
        try{
            parent::changeStorageMode($key, $storageMode);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::changeStorageMode($key, $storageMode);
        }
    }

    /**
     *  Supprime une clé
     *
     * @param string $key Clé à supprimer
     *
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
     */
    public function remove(string $key)
    {
        try{
            parent::remove($key);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::remove($key);
        }
    }

    /**
     *  Teste l'existence d'une clé
     *
     * @param string $key Clé à tester
     *
     * @return bool
     *
     * @throws \wfw\daemons\kvstore\client\errors\AlreadyLogged
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     * @throws \wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied
     */
    public function exists(string $key): bool
    {
        try{
            return parent::exists($key);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            return parent::exists($key);
        }
    }

    /**
     *  Supprime toutes les données du container.
     *
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     */
    public function purge(): void
    {
        try{
            parent::purge();
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::purge();
        }
    }

    /**
     *  Deconnecte l'instance courante du serveur KVS
     *
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     */
    public function logout(): void
    {
        try{
            parent::logout();
        }catch(MustBeLogged $e){
            $this->resetLogin();
        }
    }
}