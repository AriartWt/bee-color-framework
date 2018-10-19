<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 02:07
 */

namespace wfw\daemons\modelSupervisor\server\components\responses;

use wfw\daemons\modelSupervisor\server\IMSServerResponse;

/**
 * Réponse d'un composant du MSServer à une requête interne.
 */
abstract class ComponentResponse implements IMSServerComponentResponse
{
    /**
     * @var string $_queryId
     */
    private $_queryId;
    /**
     * @var null|IMSServerResponse $_response
     */
    private $_response;

    /**
     * ComponentResponse constructor.
     *
     * @param string                         $queryId  Identifiant de la requête interne du serveur
     * @param null|IMSServerResponse $response Réponse du component à la requête
     */
    public function __construct(string $queryId,?IMSServerResponse $response)
    {
        $this->_queryId = $queryId;
        $this->_response = $response;
    }

    /**
     *  Retourne l'identifiant de la requête envoyée par le ModelServer au WriteModule
     *
     * @return string
     */
    public function getQueryId(): string
    {
        return $this->_queryId;
    }

    /**
     * @return null|IMSServerResponse
     */
    public function getResponse(): ?IMSServerResponse
    {
        return $this->_response;
    }

    /**
     * @return null|string Identifiant de session
     */
    public function getSessionId(): ?string
    {
        return null;
    }
}