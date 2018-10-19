<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/03/18
 * Time: 12:44
 */

namespace wfw\engine\lib\HTML\helpers\forms\inputs;

/**
 * Option pour un select
 */
final class Option implements ISelectOption {
	/** @var string $_key */
	private $_key;
	/** @var string $_value */
	private $_value;
	/** @var array $_attributes */
	private $_attributes;
	/** @var bool $_selected */
	private $_selected;

	/**
	 * Option constructor.
	 *
	 * @param string $value     Valeur de l'option
	 * @param string $key       Texte affiché
	 * @param array  $attribute (optionnel) Attributs additionnels
	 * @param bool   $selected  (optionnel) Option selectionnée par défaut
	 */
	public function __construct(
		string $value,
		string $key,
		array $attribute=[],
		bool $selected = false
	){
		$this->_key = $key;
		$this->_attributes = $attribute;
		$this->_selected = $selected;
		$this->_value = $value;
	}

	/**
	 * Marque l'option comme selectionnée par défaut par le select.
	 */
	public function selected(): void {
		$this->_selected = true;
	}

	/**
	 * @return string Valeur contenue dans le select
	 */
	public function getValue(): string {
		return $this->_value;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html='<option value="'.$this->_value.'"';
		foreach($this->_attributes as $k=> $v){
			$html.=" $k=\"$v\"";
		}
		if($this->_selected) $html.=' selected';
		$html.='>'.$this->_key.'</option>';
		return $html;
	}

	/**
	 * Relache la selection d'une option pour la valeur par défaut du select
	 */
	public function unselect(): void {
		$this->_selected = false;
	}
}