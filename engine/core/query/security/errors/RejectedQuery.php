<?php

namespace wfw\engine\core\query\security\errors;

use wfw\engine\core\query\errors\QueryFailure;

/**
 * Throwed when a query have been rejected (attempted to be runned by a non authorized user)
 */
class RejectedQuery extends QueryFailure {}