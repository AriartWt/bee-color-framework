<?php
namespace wfw\engine\lib\HTML\helpers\forms\inputs;
use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;
use wfw\engine\lib\HTML\helpers\forms\IHTMLLabel;
use wfw\engine\lib\HTML\helpers\forms\inputs\ISelectOption;

/**
 * Input de type select
 */
final class Select implements IHTMLInput {
	/** @var string $_name */
	private $_name;
	/** @var ISelectOption[] $_fields */
	private $_fields;
	/** @var null|IHTMLLabel $_label */
	private $_label;
	/** @var array $_attributes */
	private $_attributes;
	/** @var mixed $_data */
	private $_data;

	/**
	 * Select constructor.
	 *
	 * @param string          $name       Nom de l'input
	 * @param null|IHTMLLabel $label      (optionnel) Label
	 * @param array           $attributes (optionnel) Attributs additionnels
	 * @param ISelectOption[] $fields     Liste des choix possibles
	 */
	public function __construct(
		string $name,
		?IHTMLLabel $label,
		array $attributes=[],
		ISelectOption ... $fields
	){
		$this->_name = $name;
		$this->_fields = $fields;
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
		foreach($this->_fields as $field){
			if($field->getValue() == $data){
				$field->selected();
				$this->_data = $data;
				break;
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html = '';
		if($this->_label) $html.=$this->_label;
		$html.='<select name="'.$this->_name.'"';
		foreach($this->_attributes as $k=> $v){
			$html.=" $k=\"$v\"";
		}
		$html.='>';
		foreach($this->_fields as $field){
			$html.=$field;
		}
		$html.='</select>';
		return $html;
	}

	/**
	 * @return mixed Données de l'input
	 */
	public function getData() {
		return $this->_data;
	}
}