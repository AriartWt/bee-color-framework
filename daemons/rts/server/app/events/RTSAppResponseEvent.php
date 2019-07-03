<?php

namespace wfw\daemons\rts\server\app\events;

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
	 * @param string     $senderId
	 * @param string     $data
	 * @param array|null $recipients If null -> all sockets for given apps
	 * @param array|null $exepts     If set, will exclude from sockets all that are listed below.
	 */
	public function __construct(
		string $senderId,
		string $data,
		?array $recipients = [],
		array $exepts = []
	){
		parent::__construct(
			$senderId,
			$data,
			IRTSAppResponseEvent::SCOPE | IRTSAppResponseEvent::DISTRIBUTION,
			null
		);
		$this->_excepts = $exepts;
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