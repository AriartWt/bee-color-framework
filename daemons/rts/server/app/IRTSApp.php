<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEvent;
use wfw\daemons\rts\server\app\events\IRTSEventListener;
use wfw\daemons\rts\server\app\events\IRTSEventObserver;

/**
 * Application du RTS
 */
interface IRTSApp extends IRTSEventListener, IRTSEventObserver {
	/**
	 * @return string
	 */
	public function getId():string;

	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 * @return string The app key
	 */
	public function getKey():string;

	/**
	 * @param string $data
	 * @return IRTSEvent[]
	 */
	public function receiveData(string $data):array;
}