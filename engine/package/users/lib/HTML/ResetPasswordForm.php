<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\lib\HTML\helpers\forms\Form;
use wfw\engine\lib\HTML\helpers\forms\inputs\Text;
use wfw\engine\lib\HTML\helpers\forms\validation\MinTimeValidity;
use wfw\engine\lib\HTML\helpers\forms\validation\MultiValidationPolicy;
use wfw\engine\package\users\security\data\ConfirmRule;

/**
 * Formulaire de reset de mot de passe
 */
class ResetPasswordForm extends Form{
	/**
	 * ResetPasswordForm constructor.
	 * @param ConfirmRule $rule
	 * @param string $errorIcon Chemin d'accés à l'icone d'erreur
	 */
	public function __construct(ConfirmRule $rule,string $errorIcon){
		parent::__construct($rule,$errorIcon,new MultiValidationPolicy(
			new MinTimeValidity(2)
		));
		$this->addInputs(...[
			new Text("old",null,[
				"placeholder" => "Ancien mot de passe",
				"type" => "password"
			]),
			new Text("password",null,[
				"placeholder" => "Nouveau mot de passe",
				"type" => "password"
			]),
			new Text("password_confirm",null,[
				"placeholder" => "Confirmation du mot de passe",
				"type" => "password"
			])
		]);
	}
}