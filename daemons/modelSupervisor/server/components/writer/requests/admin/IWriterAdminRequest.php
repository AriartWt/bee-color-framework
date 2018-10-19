<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 03:02
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;

/**
 * Requête d'administration du worker ou de l'un de ses models.
 */
interface IWriterAdminRequest extends IWriterRequest {}