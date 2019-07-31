<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\handlers\action\PostDataDefaultActionHandler;

/**
 * handler d'action par défaut des actions concernants les articles
 */
abstract class DefaultArticleActionHandler extends PostDataDefaultActionHandler {
	/** @var ISession $_session */
	protected $_session;

	/**
	 * DefaultArticleActionHandler constructor.
	 *
	 * @param ICommandBus $bus     Bus de commandes
	 * @param IRule       $rule    Régle de validation
	 * @param ISession    $session Sesion
	 * @param ITranslator $translator
	 */
	public function __construct(ICommandBus $bus, IRule $rule,ISession $session, ITranslator $translator) {
		parent::__construct($bus,$translator,$rule);
		$this->_session = $session;
	}
}