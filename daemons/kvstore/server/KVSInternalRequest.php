<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 08:43
 */

namespace wfw\daemons\kvstore\server;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Requête interne entre le KVSServer et ses workers.
 */
final class KVSInternalRequest implements IKVSInternalRequest
{
    /**
     * @var string $_id
     */
    private $_id;
    /**
     * @var string $_serverKey
     */
    private $_serverKey;
    /**
     * @var string $_request
     */
    private $_request;

    /**
     * @var string $_userName
     */
    private $_userName;

    /**
     * @var string données associées à la request
     */
    private $_requestData;

    /**
     * KVSInternalRequest constructor.
     *
     * @param string $serverKey Clé du serveur
     * @param string $userName  Nom du client à l'origine de la requête.
     * @param string $request   Requête à transmettre
     * @param string $requestData
     */
    public function __construct(
        string $serverKey,
        string $userName,
        string $request,
        string $requestData = "")
    {
        $this->_id = (string)new UUID(UUID::V4);
        $this->_serverKey = $serverKey;
        $this->_request = $request;
        $this->_userName = $userName;
        $this->_requestData = $requestData;
    }

    /**
     * @return string Clé générée par le serveur
     */
    public function getServerKey(): string
    {
        return $this->_serverKey;
    }

    /**
     * @return string Identifiant de la requête
     */
    public function getQueryId(): string
    {
        return $this->_id;
    }

    /**
     * @return string Nom de l'utilisateur à l'origine de la requête.
     */
    public function getUserName(): string
    {
        return $this->_userName;
    }

    /**
     * @return string Données du message
     */
    public function getData()
    {
        return $this->_requestData;
    }

    /**
     * @return null Paramètres du message.
     */
    public function getParams()
    {
        return $this->_request;
    }
}