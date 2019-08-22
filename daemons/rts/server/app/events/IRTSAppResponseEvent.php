<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Used to respond to a client. The RTSNetwork will write $data into all recipients socket,
 * ignoring execptions.
 */
interface IRTSAppResponseEvent extends IRTSAppEvent {
	/**
	 * @return string[] all exceptions
	 */
	public function getExcepts():array;

	/**
	 * @return array|null all recipients. If null, all sockets will be used as recipients list for
	 *                    the given apps.
	 */
	public function getRecipients():?array;
}