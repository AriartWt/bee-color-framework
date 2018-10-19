<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/03/18
 * Time: 04:56
 */

namespace wfw\engine\lib\HTML\helpers\forms\inputs;

use wfw\engine\lib\HTML\helpers\forms\IHTMLInput;

/**
 * Input accompagné d'un span d'erreur lorsqu'un champ est mal remplit.
 */
final class ErrorInput implements IHTMLInput {
	/** @var IHTMLInput $_input */
	private $_input;
	/** @var string $_errorIcon */
	private $_errorIcon;
	/** @var string $_errorMessage */
	private $_errorMessage;

	/**
	 * ErrorInput constructor.
	 *
	 * @param IHTMLInput $input
	 * @param string     $errorIcon
	 * @param string     $errorMessage
	 */
	public function __construct(IHTMLInput $input,string $errorIcon,string $errorMessage)
	{
		$this->_input = $input;
		$this->_errorIcon = $errorIcon;
		$this->_errorMessage = $errorMessage;
	}

	/**
	 * @return string Nom de l'input
	 */
	public function getName(): string
	{
		return $this->_input->getName();
	}

	/**
	 * @param mixed $data Données à intégrer à l'input
	 */
	public function setData($data): void
	{
		$this->_input->setData($data);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return
			'<div class="flex">'
			.'<span class="error-inline" id="span'.$this->getName().'" title="'.$this->_errorMessage.'">'
			.'<img class="input-icon" src="'.$this->_errorIcon.'" alt="Champ incorrect">'
			.'</span>
			'."$this->_input</div>";
	}

	/**
	 * @return mixed Données de l'input
	 */
	public function getData() {
		return $this->_input->getData();
	}
}