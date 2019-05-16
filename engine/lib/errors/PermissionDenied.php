<?php

namespace wfw\engine\lib\errors;

use Throwable;

/**
 * Permission non accordée
 */
class PermissionDenied extends FileSystemFailure {
	/**
	 * PermissionDenied constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct(
		string $message = "",
		int $code = 0,
		Throwable $previous = null
	) {
		parent::__construct(
			"Permission denied on file system : $message",
			$code,
			$previous
		);
	}
}