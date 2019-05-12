<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\helpers\forms\IHTMLForm;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\command\RegisterUser;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\types\Basic;
use wfw\engine\package\users\domain\types\Client;
use wfw\engine\package\users\lib\confirmationCode\IUserConfirmationCodeGenerator;
use wfw\engine\package\users\lib\HTML\RegisterUserForm;
use wfw\engine\package\users\security\data\SelfRegisterRule;

/**
 * Permet à un utilisateur de créer un compte
 */
final class RegisterHandler implements IActionHandler, IDomainEventListener{
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var IConf $_conf */
	private $_conf;
	/** @var ISession $_session */
	private $_session;
	/** @var null|UserRegisteredEvent $_event */
	private $_event;
	/** @var SelfRegisterRule $_rule */
	private $_rule;
	/** @var string $_errorIcon */
	private $_errorIcon;
	/** @var RegisterUserForm $_form */
	private $_form;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var IUserConfirmationCodeGenerator $_generator */
	private $_generator;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * RegisterHandler constructor.
	 *
	 * @param IConf                          $conf
	 * @param ICommandBus                    $bus
	 * @param ISession                       $session
	 * @param IRouter                        $router
	 * @param INotifier                      $notifier
	 * @param IUserConfirmationCodeGenerator $generator
	 * @param IDomainEventObserver           $observer
	 * @param ITranslator                    $translator
	 * @param SelfRegisterRule               $rule
	 */
	public function __construct(
		IConf $conf,
		ICommandBus $bus,
		ISession $session,
		IRouter $router,
		INotifier $notifier,
		IUserConfirmationCodeGenerator $generator,
		IDomainEventObserver $observer,
		ITranslator $translator,
		SelfRegisterRule $rule
	){
		$this->_translator = $translator;
		$this->_bus = $bus;
		$this->_rule = $rule;
		$this->_conf = $conf;
		$this->_notifier = $notifier;
		$this->_session = $session;
		$this->_generator = $generator;
		$this->_errorIcon = $router->webroot("Image/Icons/delete.png");
		$observer->addEventListener(UserRegisteredEvent::class,$this);
		if(!$session->exists("register_user_form")){
			$this->_form = $this->createForm();
			$session->set("register_user_form",$this->_form);
		}else{
			$this->_form = $session->get("register_user_form");
		}
	}

	/**
	 * @return IHTMLForm
	 */
	private function createForm():IHTMLForm{
		return new RegisterUserForm(
			$this->_rule,
			$this->_errorIcon,
			$this->_conf->getString("server/modules/users/cgu_link") ?? "cgu"
		);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->getMethod() === IRequest::POST){
			$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
			if($this->_form->validates($data)){
				$type = $this->_conf->getString("server/modules/users/register_type");
				switch($type){
					case "client" :
						$type = new Client(); break;
					case "basic" :
						$type = new Basic(); break;
					default :
						$type  = new $type(); break;
				}
				$this->_bus->execute(new RegisterUser(
					new Login($data["login"]),
					new Password($data["password"]),
					new Email($data["email"]),
					$type,
					'',
					new InMemoryUserSettings(),
					new UserWaitingForRegisteringConfirmation(
						$this->_generator->createUserConfirmationCode()
					)
				));
				if($action->getRequest()->isAjax()) return new Response();
				else $this->_notifier->addMessage(new Message(
					$this->_translator->get("server/engine/package/users/REGISTRATION_MAIL_SENT")
				));
			}
		}else $this->_session->set("register_user_form",$this->createForm());
		return new StaticResponse($action);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserRegisteredEvent) $this->_event = $e;
	}
}