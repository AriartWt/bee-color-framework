<?php
namespace wfw\daemons\kvstore\server;

use wfw\engine\lib\PHP\types\PHPEnum;

/**
 * Class KVStoreModes
 *
 * @package wfw\daemons\kvstore
 */
final class KVSModes extends PHPEnum {
	public const ON_DISK_ONLY = 1;
	public const IN_MEMORY_ONLY = 2;
	public const IN_MEMORY_PERSISTED_ON_DISK = 4;

	/**
	 * @param string $mode
	 *
	 * @return int
	 */
	public static function get(string $mode):int {
		return (int)parent::get($mode);
	}
}