<?php

namespace wfw\daemons\rts\server\local;

/**
 * Allow you to disconnect from the RTS local port
 */
final class RTSLogout extends RTSLoggedCommand {
	/**
	 * RTSLogout constructor.
	 *
	 * @param string $sessId
	 */
	public function __construct(string $sessId) {
		parent::__construct($sessId);
	}

	/**
	 * String representation of object
	 *
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize($this->_sessid);
	}

	/**
	 * Constructs the object
	 *
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) {
		list($this->_sessid) = unserialize($serialized);
	}
}