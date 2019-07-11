<?php

namespace wfw\engine\lib\logger;

/**
 * Write in STDERR and/or STDOUT only.
 */
final class StandardLogger extends FileLogger {
	public const NORMAL_MODE = "normal";
	public const ALL_STDOUT_MODE = "all_stdout";
	public const ALL_STDERR_MODE = "all_stderr";

	private const MODES = [
		self::NORMAL_MODE => [STDOUT,STDERR,STDERR,STDOUT],
		self::ALL_STDOUT_MODE => [STDOUT,STDOUT,STDOUT,STDOUT],
		self::ALL_STDERR_MODE => [STDERR,STDERR,STDERR,STDERR]
	];

	/**
	 * StandardLogger constructor.
	 *
	 * @param ILogFormater $formater
	 * @param string       $mode
	 * @throws \wfw\engine\lib\errors\PermissionDenied
	 */
	public function __construct(ILogFormater $formater, string $mode = self::ALL_STDOUT_MODE) {
		parent::__construct($formater, ...self::MODES[$mode]);
	}
}