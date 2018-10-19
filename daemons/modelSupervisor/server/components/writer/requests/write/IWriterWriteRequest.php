<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 03:04
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\write;

use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;

/**
 * @brief Requête d'écriture sur l'un des models du worker
 */
interface IWriterWriteRequest extends IWriterRequest {}