<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 17:15
 */

namespace wfw\engine\package\users\command\errors;

/**
 * L'utilisateur qui tente de s'enregistrer existe déjà
 */
final class UserAlreadyExists extends \Exception {}