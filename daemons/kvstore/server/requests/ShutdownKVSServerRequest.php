<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 02:04
 */

namespace wfw\daemons\kvstore\server\requests;

/**
 *  Demande l'extinction du serveur.
 */
final class ShutdownKVSServerRequest extends AbstractKVSRequest implements IAdminRequest {}