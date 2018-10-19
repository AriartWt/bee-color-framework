<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/03/18
 * Time: 03:39
 */

namespace wfw\engine\lib\HTML\helpers\forms;

/**
 * Formulaire HTML
 */
interface IHTMLForm {
	/**
	 * @return string
	 */
	public function getKey():string;

	/**
	 * @param string $key Clé du formulaire
	 * @return bool
	 */
	public function matchKey(string $key):bool;
	/**
	 * @param IHTMLInput $input Input à ajouter
	 */
	public function addInput(IHTMLInput $input):void;

	/**
	 * @param IHTMLInput ...$inputs
	 */
	public function addInputs(IHTMLInput ...$inputs):void;

	/**
	 * @param string $name Nom de l'input à récupérer
	 * @return IHTMLInput
	 */
	public function get(string $name):IHTMLInput;

	/**
	 * @param array  $data Données à valider
	 * @param string|null $key  (optionnel) Clé du formulaire. Si nulle, clé non vérifiée
	 * @return bool True si le formulaire rempli par l'utilisateur est conforme, false sinon
	 */
	public function validates(array $data,?string $key=null):bool;

	/**
	 * @return bool
	 */
	public function hasErrors():bool;
}