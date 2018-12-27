<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\lib\HTML\helpers\forms\Form;
use wfw\engine\lib\HTML\helpers\forms\inputs\Password;
use wfw\engine\lib\HTML\helpers\forms\inputs\Text;
use wfw\engine\lib\HTML\helpers\forms\validation\Honeypot;
use wfw\engine\lib\HTML\helpers\forms\validation\MinTimeValidity;
use wfw\engine\lib\HTML\helpers\forms\validation\MultiValidationPolicy;
use wfw\engine\package\users\security\data\LoginRule;

/**
 * Formulaire de connexion
 */
final class LoginForm extends Form {
	/**
	 * LoginForm constructor.
	 *
	 * @param string $errorIcon
	 */
	public function __construct(string $errorIcon) {
		parent::__construct(new LoginRule(),$errorIcon,new MultiValidationPolicy(
			new Honeypot("mail"),
			new MinTimeValidity(2)
		));
		$this->addInputs(
			new Text("login",null,[
				'placeholder' => "Identifiant","class"=>"login-input"
			]),
			new Password("password",null,[
				'placeholder' => "Mot de passe","class"=>"password-input"
			]),
			new Text("mail",null,[
				'placeholder' => "Mail", "class"=>"mail-input"
			])
		);
	}
}