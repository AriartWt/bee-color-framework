<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * RTSEvent
 */
interface IRTSAppEvent extends \JsonSerializable {
	public const SCOPE = 1; /* distributed at worker scope */
	public const DISTRIBUTION = 2; /* distributed to all workers */
	public const CENTRALIZATION = 4; /* passed to the ROOT RTS instance */

	/**
	 * @return string Event data
	 */
	public function getData():string;

	/**
	 * @return string[] All apps that can recieve the event. If null, event can be dispatched in
	 *                  every apps.
	 */
	public function getApps():?array;

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