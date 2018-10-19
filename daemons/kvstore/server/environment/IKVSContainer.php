<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 02:34
 */

namespace wfw\daemons\kvstore\server\environment;

/**
 *  Container du KVS
 */
interface IKVSContainer
{
    /**
     * @return string Nom du container
     */
    public function getName():string;

    /**
     *  Teste l'accés d'un utilisater sur l'écriture, la lecture ou l'adminsitration du container.
     *
     * @param string $userName   Nom de l'utilisateur dont on souhaite tester les droits
     * @param int    $permission Permission à tester
     *
     * @return bool
     */
    public function isUserAccessGranted(string $userName, int $permission):bool;

    /**
     * @return int Retourne le mode de stockage des données par défaut pour se container.
     */
    public function getDefaultStorageMode():int;

    /**
     * @return string Chemin d'accés au repertoir parent du container.
     */
    public function getSavePath():string;
}