<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;

/**
 * Requête d'administration du worker ou de l'un de ses models.
 */
interface IWriterAdminRequest extends IWriterRequest {}