<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/11/17
 * Time: 16:58
 */

namespace wfw\engine\core\domain\events;


use wfw\engine\lib\PHP\types\UUID;

/**
 *  Evenement de base
 */
interface IDomainEvent
{
    /**
     *  UUID de l'événement
     * @return UUID
     */
    public function getUUID():UUID;

    /**
     *  UUID de l'aggrégat ayant généré l'événement
     * @return UUID
     */
    public function getAggregateId():UUID;

    /**
     *  Date de création de l'événement
     * @return float
     */
    public function getGenerationDate():float;
}