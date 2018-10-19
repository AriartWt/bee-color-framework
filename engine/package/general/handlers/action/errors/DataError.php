<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 15:16
 */

namespace wfw\engine\package\general\handlers\action\errors;

/**
 * Les données de l'utilisateur sont valides, mais ne permettent pas d'effectuer l'action demandée.
 */
final class DataError extends \Exception{}