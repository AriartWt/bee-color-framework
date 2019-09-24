<?php
namespace wfw\engine\package\general\handlers\action\errors;

/**
 * Permet de retourner un code d'erreur 404 not found depuis une classe héritant de
 * PostDataDefaultActionHandler
 */
final class NotFound extends \Exception {}