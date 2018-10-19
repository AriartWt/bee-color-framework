<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 04:47
 */

namespace wfw\daemons\modelSupervisor\server\components\requests;

/**
 * Demande d'extinction à un composant.
 */
interface IShutdownComponentRequest extends IMSServerComponentRequest,IClientDeniedRequest {}