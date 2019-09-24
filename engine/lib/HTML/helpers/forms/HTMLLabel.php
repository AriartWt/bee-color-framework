<?php
namespace wfw\engine\lib\HTML\helpers\forms;

/**
 * Label
 */
final class HTMLLabel implements IHTMLLabel {
	/** @var string $_label */
	private $_label;
	/** @var null|string $_id */
	private $_id;

	/**
	 * HTMLLabel constructor.
	 *
	 * @param string      $label Texte du label
	 * @param null|string $id Identifiant de l'input
	 */
	public function __construct(string $label,?string $id=null) {
		$this->_label = $label;
		$this->_id = $id;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "<label".($this->_id?" for=\"$this->_id\"":"").">$this->_label</label>";
	}

	/**
	 * @return null|string Identifiant de l'input concerné par le label
	 */
	public function getId(): ?string {
		return $this->_id;
	}
}