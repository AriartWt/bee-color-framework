<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * RTSEvent
 */
interface IRTSEvent extends \JsonSerializable {
	public const SCOPE = 1; /* distribued at worker scope */
	public const DISTRIBUTION = 2; /* distribued to all workers */
	public const CENTRALIZATION = 4; /* passed to the ROOT RTS instance */

	/**
	 * @return string Event data
	 */
	public function getData():string;

	/**
	 * @return string
	 */
	public function getId():string;

	/**
	 * @return float
	 */
	public function getCreationDate():float;

	/**
	 * @return string Sender socket Id
	 */
	public function getSenderId():string;

	/**
	 * @param int $mode Mode to test
	 * @return bool True if the mode is enabled, false otherwise
	 */
	public function distributionMode(int $mode):bool;
}