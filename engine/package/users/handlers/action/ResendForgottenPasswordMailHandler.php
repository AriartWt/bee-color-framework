<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 14:57
 */

namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\command\MultiCommand;
use wfw\engine\package\general\handlers\action\errors\DataError;
use wfw\engine\package\users\command\CancelPasswordRetrieving;
use wfw\engine\package\users\command\RetrievePassword;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\security\data\RetrievePasswordRule;

/**
 * Réenvoie le mail contenant les codes pour un mot de passe oublié.
 */
final class ResendForgottenPasswordMailHandler extends DefaultUserActionHandler
	implements IDomainEventListener
{
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var null|AskedForPasswordRetrievingEvent $_event */
	private $_event;

	/**
	 * ResendForgottenPasswordMailHandler constructor.
	 * @param ICommandBus $bus
	 * @param RetrievePasswordRule $rule
	 * @param ISession $session
	 * @param IUserModelAccess $access
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		ICommandBus $bus,
		RetrievePasswordRule $rule,
		ISession $session,
		IUserModelAccess $access,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session);
		$this->_access = $access;
		$observer->addEventListener(
			AskedForPasswordRetrievingEvent::class,
			$this
		);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		$user = $this->_access->getByLogin($data["login"]);
		if(is_null($user)) throw new DataError(
			"L'utilisateur ".$data["login"]." n'a pas été trouvé !"
		);
		return new MultiCommand(
			new CancelPasswordRetrieving($user->getId(),$user->getId()),
			new RetrievePassword($user->getId(),$user->getId())
		);
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception(
			"AskedForPasswordRetrievingEvent not recieved"
		);
		return parent::successResponse();
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof AskedForPasswordRetrievingEvent) $this->_event = $e;
	}
}