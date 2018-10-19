<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/01/18
 * Time: 05:07
 */

namespace wfw\daemons\modelSupervisor\server\requests\admin;

use wfw\daemons\modelSupervisor\server\requests\AbstractMSServerRequest;

/**
 *  Ordre d'extinction du serveur.
 */
final class ShutdownMSServerRequest extends AbstractMSServerRequest implements IMSServerAdminRequest {}