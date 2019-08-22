<?php

namespace wfw\daemons\rts\server\app;

/**
 * Message sent to the end user through socket
 */
final class RTSAppMessage implements IRTSAppMessage {
	/** @var string $_name */
	private $_name;
	/** @var null $_data */
	private $_data;
	/**
	 * RTSAppMessage constructor.
	 *
	 * @param string $name
	 * @param null   $data
	 */
	public function __construct(string $name, $data=null) {
		$this->_data = $data;
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return [
			"event" => $this->_name,
			"data" => $this->_data
		];
	}
}