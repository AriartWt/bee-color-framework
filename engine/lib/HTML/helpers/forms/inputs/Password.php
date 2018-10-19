<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/03/18
 * Time: 09:18
 */

namespace wfw\engine\lib\HTML\helpers\forms\inputs;
use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;

/**
 * Input de type password
 */
final class Password implements IHTMLInput {
	/** @var string $_data */
	private $_data;
	/** @var string $_name */
	private $_name;
	/** @var null|IHTMLLabel $_label */
	private $_label;
	/** @var array $_attributes */
	private $_attributes;

	/**
	 * Text constructor.
	 *
	 * @param string          $name       Nom de l'input
	 * @param null|IHTMLLabel $label      (optionnel) Label
	 * @param array           $attributes (optionnel) attributs additionnels
	 */
	public function __construct(
		string $name,
		?IHTMLLabel $label=null,
		array $attributes=[]
	){
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
	public function __toString() {
		$html = '';
		if($this->_label) $html.=$this->_label;
		$html.='<input type="password" name="'.$this->_name.'"';
		foreach($this->_attributes as $k=> $v){ $html.=' '.$k.'="'.$v.'"'; }
		if(!is_null($this->_data)) $html.=' value="'.$this->_data.'"';
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