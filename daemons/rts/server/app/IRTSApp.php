<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSAppEventApplyer;
use wfw\daemons\rts\server\app\events\IRTSAppEventDispatcher;
use wfw\daemons\rts\server\app\events\IRTSAppEventEmitter;
use wfw\daemons\rts\server\app\events\IRTSAppEventListener;

/**
 * Application du RTS
 */
interface IRTSApp extends IRTSAppEventEmitter, IRTSAppEventListener, IRTSAppEventDispatcher, IRTSAppMessageApplyer, IRTSAppEventApplyer {
	/**
	 * @return string
	 */
	public function getId():string;

	/**
	 * @return bool
	 */
	public function isCentralized():bool;

	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 * @return string The app key
	 */
	public function getKey():string;

	/**
	 * @param string $data Data sent through websocket
	 */
	public function receiveData(string $data):void;
}