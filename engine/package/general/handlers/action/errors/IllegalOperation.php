<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/07/18
 * Time: 13:19
 */

namespace wfw\engine\package\general\handlers\action\errors;

/**
 * Une opération de l'utilisateur entre en conflit avec le fonctionnement de l'application.
 */
class IllegalOperation extends \Exception {}