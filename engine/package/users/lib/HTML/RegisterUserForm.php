<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param ITranslator      $translator
	 * @param SelfRegisterRule $rule
	 * @param string           $errorIcon Lien vers l'icone d'erreur
	 * @param string           $cgusLink  Lien vers les conditions générales d'utilisation à accepter
	 */
	public function __construct(
		ITranslator $translator,
		SelfRegisterRule $rule,
		string $errorIcon,
		string $cgusLink
	){
		parent::__construct($rule,$errorIcon,new MultiValidationPolicy(
			new Honeypot("phone"),
			new MinTimeValidity(4)
		));
		$key = "server/engine/package/users/forms";
		$this->addInputs(
			new Text("login",null,[
				"placeholder" => $translator->get("$key/LOGIN")
			]),
			new Text("password",null,[
				"placeholder" => $translator->get("$key/PASSWORD")
			]),
			new Text("password_confirm",null,[
				"placeholder" => $translator->get("$key/PASSWORD_CONFIRM")
			]),
			new Text("email",null,[
				"placeholder" => $translator->get("$key/VALID_MAIL")
			]),
			new Text("email_confirm",null,[
				"placeholder" => $translator->get("$key/MAIL_CONFIRM")
			]),
			new Checkbox(
				"agreement",
				$translator->getAndReplace("$key/AGREEMENT",$cgusLink)
			),
			new Text("phone",null,[
				"placeholder" => $translator->get("$key/MOBILE")
			])
		);
	}
}