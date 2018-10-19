<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/10/18
 * Time: 14:48
 */

namespace wfw\engine\package\contact\handlers\action;

use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\handlers\action\PostDataDefaultActionHandler;

/**
 * Implémentation de base d'un handler du package contact
 */
abstract class DefaultContactActionHandler extends PostDataDefaultActionHandler{
	/** @var ISession $_session */
	protected $_session;

	/**
	 * DefaultContactActionHandler constructor.
	 *
	 * @param ICommandBus $bus     Bus de commandes
	 * @param IRule       $rule    Régle de validation des données
	 * @param ISession    $session Session
	 */
	public function __construct(ICommandBus $bus, IRule $rule, ISession $session) {
		parent::__construct($bus, $rule);
		$this->_session = $session;
	}
}