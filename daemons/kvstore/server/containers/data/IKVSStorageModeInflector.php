<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/01/18
 * Time: 10:57
 */

namespace wfw\daemons\kvstore\server\containers\data;

/**
 *  Permet d'associer un mode de stockage à un gestionnaire de stockage
 */
interface IKVSStorageModeInflector
{
    /**
     * @return KVSStorageModeManager[] Tous les gestionnaires de données
     */
    public function getAll():array;
    /**
     * @param int $mode Mode de stockage
     * @return KVSStorageModeManager Gestionnaires de données concernés
     */
    public function getStorageModeManager(int $mode):KVSStorageModeManager;
}