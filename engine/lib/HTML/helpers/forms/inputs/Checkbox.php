<?php
namespace wfw\engine\lib\HTML\helpers\forms\inputs;

use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;

/**
 * Input de type checkbox
 */
final class Checkbox implements IHTMLInput {
	/** @var string $_name */
	private $_name;
	/** @var null|\wfw\engine\lib\HTML\helpers\forms\IHTMLLabel $_label */
	private $_label;
	/** @var array $_attributes */
	private $_attributes;
	/** @var bool $_data */
	private $_data;
	/** @var bool $_default */
	private $_default;

	/**
	 * Checkbox constructor.
	 *
	 * @param string                                             $name       Nom de l'input
	 * @param null|\wfw\engine\lib\HTML\helpers\forms\IHTMLLabel $label      (optionnel) Label
	 * @param array                                              $attributes (optionnel) Attributs additionnels
	 * @param bool                                               $default    (optionnel) valeur par défaut
	 */
	public function __construct(
		string $name,
		?IHTMLLabel $label=null,
		array $attributes=[],
		bool $default=false
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
		$this->_data = filter_var($data,FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html = '';
		if($this->_label) $html.=$this->_label;
		$html.='<input type="checkbox" name="'.$this->_name.'"';
		foreach($this->_attributes as $k=> $v){
			$html.=' '.$k.'="'.$v.'"';
		}
		if(!is_null($this->_data)) $html.=$this->_data ? ' checked' : '';
		else if($this->_default) $html.=' checked';
		$html.='>';
		return $html;
	}

	/**
	 * @return mixed Données de l'input
	 */
	public function getData() {
		return $this->_data;
	}
}