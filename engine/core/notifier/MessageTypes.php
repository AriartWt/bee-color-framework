<?php
namespace wfw\engine\core\notifier;

use wfw\engine\lib\PHP\types\PHPEnum;

/**
 * Enum de type de messages.
 */
class MessageTypes extends PHPEnum {
	public const NOTIFICATION = "notification";
	public const SUCCESS = "success";
	public const ERROR = "error";
	public const INFO = "infos";
}