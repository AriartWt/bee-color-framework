<?php

namespace wfw\daemons\rts\apps\chat;

use wfw\daemons\rts\server\app\events\RTSAppEventEmitter;
use wfw\daemons\rts\server\app\RTSApp;

/**
 * Chat
 */
class RTSChat extends RTSApp {
	/**
	 * RTSChat constructor.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		parent::__construct(new RTSAppEventEmitter(), "/chat");
		$this->subscribeToAppEvents(new ChatConnectionHandler());
	}
}