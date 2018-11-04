<?php
namespace wfw\engine\lib\HTML\helpers\forms\inputs;
use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;

/**
 * Input de type radio
 */
final class Radio implements IHTMLInput {
	/** @var string $_name */
	private $_name;
	/** @var string $_data */
	private $_data;
	/** @var null|IHTMLLabel $_label */
	private $_label;
	/** @var string $_value */
	private $_value;
	/** @var string[] $_values */
	private $_values;
	/** @var array $_attributes */
	private $_attributes;
	/** @var null|string $_defaultChecked */
	private $_defaultChecked;

	/**
	 * Radio constructor.
	 *
	 * @param string          $name           Nom de l'input
	 * @param null|IHTMLLabel $label          (optionnel) Label
	 * @param null|string     $defaultChecked (optionnel) Radio choisi par défaut
	 * @param array           $attributes     (optionnel) attributs additionnels
	 * @param string[]        ...$values      Listes des valeurs possibles
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $name,
		?IHTMLLabel $label,
		?string $defaultChecked=null,
		array $attributes=[],
		string ...$values
	){
		if(count($values) === 0) throw new \InvalidArgumentException(
			"Expects at least one value !"
		);
		$this->_name = $name;
		$this->_label = $label;
		$this->_values = array_flip($values);
		$this->_value = $values[0];
		$this->_attributes = $attributes;
		$this->_defaultChecked =$defaultChecked;
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
	 * @param string $value
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function selectValue(string $value){
		if(isset($this->_values[$value])) $this->_value = $value;
		else throw new \InvalidArgumentException("Unknown value $value");
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html='';
		$html.=$this->_label ?? '';
		$html.='<input type="radio" name="'.$this->_name.'" id="'.$this->_value.'" ';
		foreach ($this->_attributes as $k=> $v){ $html.=" $k=\"$v\""; }
		$html.=' value="'.$this->_value.'"';
		if(!is_null($this->_value) && $this->_data === $this->_value) $html.=" checked";
		else if(is_null($this->_data) && $this->_value === $this->_defaultChecked) $html.=" checked";
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