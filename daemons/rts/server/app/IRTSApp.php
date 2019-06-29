<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEventListener;

/**
 * Application du RTS
 */
interface IRTSApp extends IRTSEventListener {
	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 * @return string The app key
	 */
	public function getKey():string;
}