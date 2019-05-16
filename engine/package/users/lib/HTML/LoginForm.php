<?php
namespace wfw\engine\package\users\lib\HTML;

use wfw\engine\core\lang\ITranslator;
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
	 * @param ITranslator $translator
	 * @param string      $errorIcon
	 */
	public function __construct(ITranslator $translator,string $errorIcon) {
		parent::__construct(new LoginRule($translator),$errorIcon,new MultiValidationPolicy(
			new Honeypot("mail"),
			new MinTimeValidity(2)
		));
		$key = "server/engine/package/users/forms";
		$this->addInputs(
			new Text("login",null,[
				'placeholder' => $translator->get("$key/LOGIN"),"class"=>"login-input"
			]),
			new Password("password",null,[
				'placeholder' => $translator->get("$key/PASSWORD"),"class"=>"password-input"
			]),
			new Text("mail",null,[
				'placeholder' => $translator->get("$key/MAIL"), "class"=>"mail-input"
			])
		);
	}
}