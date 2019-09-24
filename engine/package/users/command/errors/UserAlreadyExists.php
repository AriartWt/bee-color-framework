<?php
namespace wfw\engine\package\users\command\errors;

/**
 * L'utilisateur qui tente de s'enregistrer existe déjà
 */
final class UserAlreadyExists extends \Exception {}