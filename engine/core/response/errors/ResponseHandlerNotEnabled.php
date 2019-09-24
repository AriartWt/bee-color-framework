<?php

namespace wfw\engine\core\response\errors;

/**
 * Thrown when the module/package that owns a response havn't been enabled in configurations.
 */
final class ResponseHandlerNotEnabled extends ResponseResolutionFailure {}