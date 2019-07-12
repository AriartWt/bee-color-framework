<?php

namespace wfw\daemons\rts\server\app\events;
use wfw\daemons\rts\apps\chat\RTSChat;
use wfw\daemons\rts\server\app\RTSAppMessage;

/**
 * Class RTSAppEventSubscriber
 *
 * @package wfw\daemons\rts\server\app\events
 */
class RTSAppEventSubscriber implements IRTSAppEventSubscriber {
	/**
	 * @return string[] eventClass=>method
	 */
	public function getEvents(): array {
		return [
			ClientConnected::class => "clientConnected",
			ClientDisconnected::class => "clientDisconnected"
		];
	}

	public function clientConnected(RTSChat $app, ClientConnected $event){
		fwrite(STDOUT, "CLIENT CONNECTED TO CHAT : ".$event->getConnection()->getId().PHP_EOL);
		$app->dispatch(new RTSAppResponseEvent($event->getConnection()->getId(),new RTSAppMessage(
			"Connected",$event->getConnection()->getId()
		),null));
	}

	public function clientDisconnected(RTSChat $app, ClientDisconnected $event){
		fwrite(STDOUT, "CLIENT DISCONNECTED FROM CHAT : ".$event->getConnection()->getId().PHP_EOL);
		$app->dispatch(new RTSAppResponseEvent($event->getConnection()->getId(),new RTSAppMessage(
			"Disconnected",$event->getConnection()->getId()
		),null,[$event->getConnection()->getId()]));
	}
}