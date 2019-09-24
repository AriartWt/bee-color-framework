<?php

namespace wfw\engine\core\action\errors;

/**
 * Thrown when an action have been found, but it's package were not enabled.
 */
final class ActionHandlerNotEnabled extends ActionResolutionFailure {}