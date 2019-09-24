<?php
namespace wfw\daemons\sctl;

/**
 * Micro serveur sctl.
 */
interface ISCTLServer {
	/**
	 * demarre le serveur
	 */
	public function start():void;

	/**
	 * @param int $signal Signal ayant éteint le serveur
	 */
	public function shutdown(int $signal):void;
}