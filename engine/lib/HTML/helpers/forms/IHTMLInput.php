<?php
namespace wfw\engine\lib\HTML\helpers\forms;

/**
 * Input HTML
 */
interface IHTMLInput {
	/**
	 * @return string Nom de l'input
	 */
	public function getName():string;

	/**
	 * @param mixed $data Données à intégrer à l'input
	 */
	public function setData($data):void;

	/**
	 * @return mixed Données de l'input
	 */
	public function getData();

	public function __toString();
}