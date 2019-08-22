<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\app\IRTSAppMessage;

/**
 * Used to respond to a client. The RTSNetwork will write $data into all recipients socket,
 * ignoring execptions.
 */
class RTSAppResponseEvent extends RTSAppEvent implements IRTSAppResponseEvent {
	/** @var string[]|null $_recipients */
	private $_recipients;
	/** @var string[] $_excepts */
	private $_excepts;

	/**
	 * RTSResponseEvent constructor.
	 *
	 * @param string              $senderId
	 * @param IRTSAppMessage|null $data
	 * @param array|null          $recipients If null -> all sockets for given apps
	 * @param array|null          $excepts    If set, will exclude from sockets all that are listed below.
	 */
	public function __construct(
		string $senderId,
		?IRTSAppMessage $data = null,
		?array $recipients = [],
		array $excepts = []
	){
		parent::__construct(
			$senderId,
			$data ? json_encode($data) : null,
			IRTSAppResponseEvent::SCOPE | IRTSAppResponseEvent::DISTRIBUTION,
			null
		);
		$this->_excepts = $excepts;
		$this->_recipients = $recipients;
	}

	/**
	 * @return string[]|null
	 */
	public function getRecipients(): ?array {
		return $this->_recipients;
	}

	/**
	 * @return string[]
	 */
	public function getExcepts(): array {
		return $this->_excepts;
	}
}