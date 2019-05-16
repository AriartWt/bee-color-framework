<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\CancelMailChange;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;

/**
 * Annule la demande de changement d'adresse email.
 * Requiert une authentification.
 */
final class CancelChangeMailHandler implements IActionHandler, IDomainEventListener{
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var ISession $_session */
	private $_session;
	/** @var null|AskedForEmailChangeEvent $_event */
	private $_event;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * CancelChangeMailHandler constructor.
	 *
	 * @param ICommandBus          $bus
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param ITranslator          $translator
	 */
	public function __construct(
		ICommandBus $bus,
		ISession $session,
		IDomainEventObserver $observer,
		ITranslator $translator
	){
		$this->_bus = $bus;
		$this->_session = $session;
		$this->_translator = $translator;
		$observer->addEventListener(AskedForEmailChangeEvent::class,$this);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		/** @var User $user */
		$user = $this->_session->get('user');
		try{
			$this->_bus->execute(new CancelMailChange(
				$user->getId(),
				$user->getId()
			));
			if(is_null($this->_event)) throw new \Exception(
				$this->_translator->get(
					"server/engine/package/users/CANCEL_MAIL_CHANGE_EVENT_NOT_RECIEVED"
				)
			);
			return new Response();
		}catch(\Exception $e){
			return new ErrorResponse(500,$e->getMessage());
		}catch(\Error $e){
			return new ErrorResponse(501,$e->getMessage());
		}
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof CanceledUserMailChangeEvent) $this->_event = $e;
	}
}