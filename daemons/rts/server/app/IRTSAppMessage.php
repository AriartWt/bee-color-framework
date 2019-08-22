<?php

namespace wfw\daemons\rts\server\app;

/**
 * Message sent to client through websocket
 */
interface IRTSAppMessage extends \JsonSerializable {
	/**
	 * @return string
	 */
	public function getName():string;

	/**
	 * @return mixed
	 */
	public function getData();
}