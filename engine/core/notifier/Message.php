<?php
namespace wfw\engine\core\notifier;

/**
 * Message de notifier
 */
final class Message implements IMessage {
	/** @var string $_message */
	private $_message;
	/** @var null|string $_type */
	private $_type;
	/** @var array $_params */
	private $_params;

	/**
	 * Message constructor.
	 *
	 * @param string      $message Message
	 * @param null|string $type    Type du message
	 * @param array       $params  Paramètres optionnels du message.
	 */
	public function __construct(string $message, ?string $type = null, array $params = []) {
		$this->_message = $message;
		$this->_params = $params;
		$this->_type = $type;
	}

	/**
	 * @return string Type du message
	 */
	public function getType(): ?string {
		return $this->_type;
	}

	/**
	 * @return array Paramètres optionnels.
	 */
	public function getParams(): array {
		return $this->_params;
	}

	/**
	 * @return string Message
	 */
	public function __toString() {
		return $this->_message;
	}
}