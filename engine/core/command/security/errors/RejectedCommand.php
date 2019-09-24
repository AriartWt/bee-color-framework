<?php

namespace wfw\engine\core\command\security\errors;

use wfw\engine\core\command\errors\CommandFailure;

/**
 * Throwed when a command have been rejected (attempted to be runned by a non authorized user)
 */
class RejectedCommand extends CommandFailure {}