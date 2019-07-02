<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Used to respond to a client. The RTSNetwork will write $data into all recipients socket,
 * ignoring execptions.
 */
class RTSResponseEvent extends RTSEvent implements IRTSResponseEvent {
	/** @var string[]|null $_recipients */
	private $_recipients;
	/** @var string[] $_excepts */
	private $_excepts;

	/**
	 * RTSResponseEvent constructor.
	 *
	 * @param string     $senderId
	 * @param string     $data
	 * @param int        $distributionMode
	 * @param array|null $apps
	 * @param array|null $recipients If null -> all sockets for given apps
	 * @param array|null $exepts     If set, will exclude from sockets all that are listed below.
	 */
	public function __construct(
		string $senderId,
		string $data,
		int $distributionMode = self::CENTRALIZATION,
		?array $apps = ["*"],
		?array $recipients = [],
		array $exepts = []
	){
		parent::__construct($senderId, $data, $distributionMode, $apps);
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