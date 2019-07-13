<?php

namespace wfw\daemons\rts\apps\chat;

use wfw\daemons\rts\server\app\events\ClientConnected;
use wfw\daemons\rts\server\app\events\RTSAppConnectionHandler;
use wfw\daemons\rts\server\app\events\RTSAppResponseEvent;
use wfw\daemons\rts\server\app\IRTSApp;
use wfw\daemons\rts\server\app\RTSAppMessage;

/**
 * Send
 */
class ChatConnectionHandler extends RTSAppConnectionHandler {
	/**
	 * send an array of all connected clients
	 *
	 * @param IRTSApp         $app
	 * @param ClientConnected $event
	 */
	public function clientConnected(IRTSApp $app, ClientConnected $event) {
		parent::clientConnected($app, $event);
		$app->dispatch(
			new RTSAppResponseEvent(
				$event->getConnection()->getId(),
				new RTSAppMessage(
					$event->getConnection()->getApp().":already_connected",
					array_diff(
						array_keys($this->getClients($event->getConnection()->getApp())),
						[$event->getConnection()->getId()]
					)
				),
				[$event->getConnection()->getId()]
			)
		);
	}
}