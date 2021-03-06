<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 07:52
 */

namespace wfw\daemons\rts\client;

use wfw\daemons\rts\server\app\events\IRTSAppEvent;

/**
 * Client permettant de se connecter à une instance de RTS
 */
interface IRTSClient {
	/**
	 * @param IRTSAppEvent[] $events
	 */
	public function send(IRTSAppEvent ...$events):void;

	/**
	 * Se connecte à une instance RTS
	 */
	public function login():void;

	/**
	 * Se déconnecte d'une instance RTS
	 */
	public function logout():void;

	/**
	 * @return bool True si le client est actuellement loggé (ie a obtenu un sessid)
	 */
	public function isLogged():bool;
}