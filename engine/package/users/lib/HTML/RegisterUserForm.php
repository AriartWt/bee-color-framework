<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\lib\HTML\helpers\forms\Form;
use wfw\engine\lib\HTML\helpers\forms\inputs\Checkbox;
use wfw\engine\lib\HTML\helpers\forms\inputs\Text;
use wfw\engine\lib\HTML\helpers\forms\validation\Honeypot;
use wfw\engine\lib\HTML\helpers\forms\validation\MinTimeValidity;
use wfw\engine\lib\HTML\helpers\forms\validation\MultiValidationPolicy;
use wfw\engine\package\users\security\data\SelfRegisterRule;

/**
 * Forumlaire permettant à un utilisateur de s'inscrire
 */
class RegisterUserForm extends Form{
	/**
	 * RegisterUserForm constructor.
	 * @param SelfRegisterRule $rule
	 * @param string $errorIcon Lien vers l'icone d'erreur
	 * @param string $cgusLink Lien vers les conditions générales d'utilisation à accepter
	 */
	public function __construct(SelfRegisterRule $rule,string $errorIcon,string $cgusLink){
		parent::__construct($rule,$errorIcon,new MultiValidationPolicy(
			new Honeypot("mobile"),
			new MinTimeValidity(4)
		));
		$this->addInputs(
			new Text("login",null,[
				"placeholder" => "Login"
			]),
			new Text("password",null,[
				"placeholder" => "Mot de passe"
			]),
			new Text("password_confirm",null,[
				"placeholder" => "Confirmation du mot de passe"
			]),
			new Text("email",null,[
				"placeholder" => "Adresse email valide"
			]),
			new Text("email_confirm",null,[
				"placeholder" => "Confirmation du mail"
			]),
			new Checkbox("agreement","J'accepte les <a href='$cgusLink' target='_blank'>conditions générales d'utilisation</a>"),
			new Text("tel",null,[
				"placeholder" => "Téléphone"
			]),
			new Text("mobile",null,[
				"placeholder" => "Mobile"
			])
		);
	}
}