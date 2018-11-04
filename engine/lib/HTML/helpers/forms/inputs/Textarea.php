<?php
namespace wfw\engine\lib\HTML\helpers\forms\inputs;
use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;

/**
 * Input de type textarea
 */
final class Textarea implements IHTMLInput {
	/** @var string $_data */
	private $_data;
	/** @var string $_name */
	private $_name;
	/** @var null|IHTMLLabel $_label */
	private $_label;
	/** @var array $_attributes */
	private $_attributes;
	/** @var null|string $_default */
	private $_default;

	/**
	 * Textarea constructor.
	 *
	 * @param string          $name       Nom de l'input
	 * @param null|IHTMLLabel $label      (optionnel) Label
	 * @param array           $attributes (optionnel) Attributs additionnels
	 * @param string|null     $default    (optionnel) Valeur par défaut
	 */
	public function __construct(
		string $name,
		?IHTMLLabel $label=null,
		array $attributes=[],
		string $default=null
	){
		$this->_name = $name;
		$this->_label = $label;
		$this->_attributes = $attributes;
		$this->_default = $default;
	}

	/**
	 * @return string Nom de l'input
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 * @param mixed $data Données à intégrer à l'input
	 */
	public function setData($data): void {
		$this->_data = $data;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html = '';
		if($this->_label) $html.=$this->_label;
		$html.='<textarea name="'.$this->_name.'"';
		foreach($this->_attributes as $k=> $v){ $html.=' '.$k.'="'.$v.'"'; }
		$html.='>';
		if(!is_null($this->_data)) $html.=$this->_data;
		else if($this->_default) $html.=$this->_default;
		$html.="</textarea>";
		return $html;
	}

	/**
	 * @return mixed Données de l'input
	 */
	public function getData() {
		return $this->_data;
	}
}