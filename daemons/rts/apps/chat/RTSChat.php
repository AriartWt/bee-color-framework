<?php

namespace wfw\daemons\rts\apps\chat;

use wfw\daemons\rts\server\app\events\RTSAppEventEmitter;
use wfw\daemons\rts\server\app\events\RTSAppEventSubscriber;
use wfw\daemons\rts\server\app\RTSApp;

/**
 * Chat
 */
class RTSChat extends RTSApp {
	private $_userManagement;

	/**
	 * RTSChat constructor.
	 *
	 * @param int $currentScope
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $currentScope) {
		parent::__construct(new RTSAppEventEmitter(), $currentScope,"/chat");
		$this->_userManagement = new RTSAppEventSubscriber();
		$this->subscribeToAppEvents($this->_userManagement);
	}
}