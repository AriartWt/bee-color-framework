<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/01/18
 * Time: 09:20
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\responses;

use wfw\daemons\modelSupervisor\server\responses\AbastractMSServerResponse;

/**
 * Réponse à une requête sur un model
 */
final class QueryModelResponse extends AbastractMSServerResponse
{
    /**
     * @var string $_data
     */
    private $_data;

    /**
     * QueryModelResponse constructor.
     *
     * @param string $data Données retournées par le mode (sérialisées)
     */
    public function __construct(string $data)
    {
        $this->_data = $data;
    }

    /**
     * @return string Données
     */
    public function getData()
    {
        return $this->_data;
    }
}