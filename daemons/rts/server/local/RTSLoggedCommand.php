<?php

namespace wfw\daemons\rts\server\local;


/**
 * Command that must be used once logged into the RTS local port
 */
abstract class RTSLoggedCommand implements IRTSLocalCommand {
	/** @var string $_sessid */
	protected $_sessid;

	/**
	 * RTSLoggedCommand constructor.
	 *
	 * @param string $sessId RTS Session ID
	 */
	public function __construct(string $sessId) {
		$this->_sessid = $sessId;
	}

	/**
	 * @return string
	 */
	public function getSessid(): string {
		return $this->_sessid;
	}
}