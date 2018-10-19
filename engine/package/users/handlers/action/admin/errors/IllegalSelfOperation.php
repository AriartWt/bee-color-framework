<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/07/18
 * Time: 13:16
 */

namespace wfw\engine\package\users\handlers\action\admin\errors;

use wfw\engine\package\general\handlers\action\errors\IllegalOperation;

/**
 * Un utilisateur à tenté de se supprimer lui même ou de se désactiver.
 */
final class IllegalSelfOperation extends IllegalOperation {}