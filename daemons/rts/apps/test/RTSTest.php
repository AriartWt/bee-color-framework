<?php

namespace wfw\daemons\rts\apps\test;

use wfw\daemons\rts\server\app\events\RTSAppConnectionHandler;
use wfw\daemons\rts\server\app\events\RTSAppEventEmitter;
use wfw\daemons\rts\server\app\RTSApp;

/**
 * Class RTSTest
 *
 * @package wfw\daemons\rts\apps\test
 */
class RTSTest extends RTSApp {
	public function __construct() {
		parent::__construct(
			new RTSAppEventEmitter(),
			"/test"
		);
		$this->subscribeToAppEvents(new RTSAppConnectionHandler());
	}
}