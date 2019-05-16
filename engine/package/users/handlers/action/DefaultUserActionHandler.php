<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\handlers\action\PostDataDefaultActionHandler;

/**
 * Handler de base pour les action sur les utilisateurs
 */
abstract class DefaultUserActionHandler extends PostDataDefaultActionHandler{
	/** @var ISession $_session */
	protected $_session;
	/** @var ITranslator $_translator */
	protected $_translator;

	/**
	 * DefaultUserActionHandler constructor.
	 *
	 * @param ICommandBus $bus     Bus de commande
	 * @param IRule       $rule    Régle de validation des données
	 * @param ISession    $session Session
	 * @param ITranslator $translator
	 * @param bool        $withGet
	 */
	public function __construct(
		ICommandBus $bus,
		IRule $rule,
		ISession $session,
		ITranslator $translator,
		bool $withGet=false
	){
		parent::__construct($bus, $rule,false,false,true);
		$this->_translator = $translator;
		$this->_session = $session;
	}
}