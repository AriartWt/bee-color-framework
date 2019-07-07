<?php

namespace wfw\daemons\rts\server\app\events;


/**
 * Used to close connections from apps. recipients and excepts works as RTSAppResponseEvent does.
 */
final class RTSCloseConnectionsEvent extends RTSAppResponseEvent {
	/**
	 * RTSCloseConnectionsEvent constructor.
	 *
	 * @param string     $senderId
	 * @param string     $data
	 * @param array|null $recipients
	 * @param array|null $excepts
	 */
	public function __construct(string $senderId, string $data, ?array $recipients = [], ?array $excepts = []) {
		parent::__construct($senderId, $data, $recipients, $excepts);
	}
}