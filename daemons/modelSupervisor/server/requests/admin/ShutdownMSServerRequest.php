<?php
namespace wfw\daemons\modelSupervisor\server\requests\admin;

use wfw\daemons\modelSupervisor\server\requests\AbstractMSServerRequest;

/**
 *  Ordre d'extinction du serveur.
 */
final class ShutdownMSServerRequest extends AbstractMSServerRequest implements IMSServerAdminRequest {}