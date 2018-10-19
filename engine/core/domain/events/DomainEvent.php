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
abstract class DomainEvent implements IDomainEvent
{
    private $_uuid;
    private $_generationDate;
    private $_aggregateId;

    /**
     *  Event constructor.
     *
     * @param UUID $aggregateId Identifiant de l'aggrégat générant l'événement
     */
    public function __construct(UUID $aggregateId)
    {
        $this->_uuid = new UUID();
        $this->_generationDate = microtime(true);
        $this->_aggregateId = $aggregateId;
    }

    /**
     *  UUID de l'événement
     * @return UUID
     */
    public function getUUID():UUID{
        return $this->_uuid;
    }

    /**
     *  Date de création de l'événement
     * @return float
     */
    public function getGenerationDate():float{
        return $this->_generationDate;
    }

    /**
     *  Identifiant de l'aggrégat ayant généré l'événement
     * @return UUID
     */
    public function getAggregateId(): UUID
    {
        return $this->_aggregateId;
    }
}