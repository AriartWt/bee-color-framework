<?php
namespace wfw\engine\core\domain\aggregate\errors;

/**
 *  Exception elvée lorsqu'un aggrégat n'a pas de méthode pour accueillir un événement donné
 */
class NoHandlerForEvent extends \Exception {}