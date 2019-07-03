<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\websocket\events\Closed;

/**
 * When a client connection have been closed
 */
final class ClientDisconnected extends RTSAppEvent {
	public const CODE = "close_code";
	public const MESSAGE = "close_message";
	public const INFOS = "connection_infos";
	public const DATE = "close_date";
	/**
	 * ClientConnected constructor.
	 *
	 * @param Closed $data
	 */
	public function __construct(Closed $data) {
		parent::__construct(
			'',
			json_encode([
				self::INFOS => $data->getConnectionInfos(),
				self::CODE => $data->getCode(),
				self::MESSAGE => $data->getMessage(),
				self::DATE => $data->getCreationDate()
			]),
			IRTSAppEvent::SCOPE | IRTSAppEvent::CENTRALIZATION | IRTSAppEvent::DISTRIBUTION,
			null
		);
	}
}