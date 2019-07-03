<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\websocket\events\Handshaked;

/**
 * When a new client have been successfully handshaked
 */
final class ClientConnected extends RTSAppEvent {
	public const INFOS = "connection_infos";
	public const DATE = "connection_date";
	/**
	 * ClientConnected constructor.
	 *
	 * @param Handshaked $data
	 */
	public function __construct(Handshaked $data) {
		parent::__construct(
			'',
			json_encode([
				self::INFOS => $data->getConnectionInfos(),
				self::DATE => $data->getCreationDate()
			 ]),
			IRTSAppEvent::SCOPE | IRTSAppEvent::CENTRALIZATION | IRTSAppEvent::DISTRIBUTION,
			null
		);
	}
}