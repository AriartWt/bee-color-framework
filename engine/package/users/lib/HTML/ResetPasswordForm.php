<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\lib\HTML\helpers\forms\Form;
use wfw\engine\lib\HTML\helpers\forms\inputs\Text;
use wfw\engine\lib\HTML\helpers\forms\validation\Honeypot;
use wfw\engine\lib\HTML\helpers\forms\validation\MinTimeValidity;
use wfw\engine\lib\HTML\helpers\forms\validation\MultiValidationPolicy;
use wfw\engine\package\users\security\data\ResetPasswordRule;

/**
 * Formulaire de reset de mot de passe
 */
class ResetPasswordForm extends Form{
	/**
	 * ResetPasswordForm constructor.
	 *
	 * @param ITranslator       $translator
	 * @param ResetPasswordRule $rule
	 * @param string            $errorIcon Chemin d'accés à l'icone d'erreur
	 */
	public function __construct(ITranslator $translator,ResetPasswordRule $rule, string $errorIcon){
		parent::__construct($rule,$errorIcon,new MultiValidationPolicy(
			new MinTimeValidity(2),
			new Honeypot("login")
		));
		$key = "server/engine/package/users/forms";
		$this->addInputs(...[
			new Text("login",null,[
				"placeholder" => $translator->get("$key/LOGIN")
			]),
			new Text("password",null,[
				"placeholder" => $translator->get("$key/NEW_PASSWORD"),
				"type" => "password"
			]),
			new Text("password_confirm",null,[
				"placeholder" => $translator->get("$key/PASSWORD_CONFIRM"),
				"type" => "password"
			])
		]);
	}
}