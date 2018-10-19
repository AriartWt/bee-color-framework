<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/11/17
 * Time: 16:33
 */

namespace wfw\engine\core\command;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Commande de base
 */
abstract class Command implements ICommand
{
    /**
     * @var UUID
     */
    private $_uuid;
    /**
     * @var float
     */
    private $_generationDate;

    /**
     *  Command constructor.
     */
    public function __construct(){
        $this->_uuid = new UUID();
        $this->_generationDate = microtime(true);
    }

    /**
     *  Retourne l'UUID de la commande
     * @return UUID
     */
    public function getId():UUID{
        return $this->_uuid;
    }

    /**
     *  Retourne la date de création de la commande
     * @return float
     */
    public function getGenerationDate():float{
        return $this->_generationDate;
    }
}