<?php
namespace wfw\engine\lib\HTML\helpers\forms\inputs;
use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;

/**
 * Input de type file
 */
final class File implements IHTMLInput {
	/** @var string $_name */
	private $_name;
	/** @var null|\wfw\engine\lib\HTML\helpers\forms\IHTMLLabel $_label */
	private $_label;
	/** @var array $_attributes */
	private $_attributes;
	/** @var mixed $_data */
	private $_data;

	/**
	 * File constructor.
	 *
	 * @param string                                             $name       Nom de l'input
	 * @param null|\wfw\engine\lib\HTML\helpers\forms\IHTMLLabel $label      (optionnel) Label
	 * @param array                                              $attributes (optionnel) Attributs additionnels
	 */
	public function __construct(string $name, ?IHTMLLabel $label=null, array $attributes=[]) {
		$this->_name = $name;
		$this->_label = $label;
		$this->_attributes = $attributes;
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
	public function __toString(){
		$html='';
		if($this->_label) $html.=$this->_label;
		$html.='<input type="file" name="'.$this->_name.'" value="'.$this->_data.'"';
		foreach($this->_attributes as $k=> $v){
			$html.=" $k=\"$v\"";
		}
		$html.=">";
		return $html;
	}

	/**
	 * @return mixed Données de l'input
	 */
	public function getData() {
		return $this->_data;
	}
}