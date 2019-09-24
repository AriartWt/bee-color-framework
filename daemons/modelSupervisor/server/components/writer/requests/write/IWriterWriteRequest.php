<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\write;

use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;

/**
 * @brief Requête d'écriture sur l'un des models du worker
 */
interface IWriterWriteRequest extends IWriterRequest {}