<?php
namespace wfw\engine\package\users\data\model;

use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\data\model\EventReceptionReport;
use wfw\engine\core\data\model\InMemoryEventBasedModel;

use wfw\engine\package\users\data\model\specs\IsAdmin;
use wfw\engine\package\users\data\model\specs\IsBasic;
use wfw\engine\package\users\data\model\specs\IsClient;
use wfw\engine\package\users\data\model\specs\IsDisabled;
use wfw\engine\package\users\data\model\specs\IsEnabled;
use wfw\engine\package\users\data\model\specs\IsWaitingForEmailConfirmation;
use wfw\engine\package\users\data\model\specs\IsWaitingForPasswordReset;
use wfw\engine\package\users\data\model\specs\IsWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;
use wfw\engine\package\users\domain\events\LoginChangedEvent;
use wfw\engine\package\users\domain\events\UserConfirmedEvent;
use wfw\engine\package\users\domain\events\UserDisabledEvent;
use wfw\engine\package\users\domain\events\UserEnabledEvent;
use wfw\engine\package\users\domain\events\UserEvent;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\events\UserPasswordChangedEvent;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\events\UserPasswordRetrievingCanceledEvent;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\domain\events\UserSettingsModifiedEvent;
use wfw\engine\package\users\domain\events\UserSettingsRemovedEvent;
use wfw\engine\package\users\domain\events\UserTypeChangedEvent;

use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\states\EnabledUser;

/**
 *  Modèle de lecture pour les utilisateurs
 */
class UserModel extends InMemoryEventBasedModel {
	public const IS_ADMIN = "admin";
	public const IS_BASIC = "basic";
	public const IS_CLIENT = "client";
	public const IS_ENABLED = "enabled";
	public const IS_DISABLED = "disabled";
	public const IS_WAITING_FOR_MAIL_CONFIRM = "wfmailConfirmation";
	public const IS_WAITING_FOR_PASSWORD_RESET = "wfpasswordReset";
	public const IS_WAITING_FOR_REGISTRATION_CONFIRM = "wfregistrationConfirmation";

 	/**
	 * @return array
	 */
	public function listenEvents(): array { return [ UserEvent::class ]; }

	/**
	 * Doit retourner un tableau name=>ISpecification qui définit les indexes à utiliser
	 * pour le modèle courant.
	 * La liste des indexes et synchronisée avec le modèle au moment de la construction puis à
	 * chaque déserialsiation de sorte que les indexes définis soient toujours en adéquation
	 * avec les indexes disponibles pour les recherches sur les modèles.
	 * Par défaut, le teste d'égalité entre un ancien index et un nouvel index se base sur la classe
	 * de la spécification. Si une methode equals():bool est définie sur la Specification, alors
	 * c'est cette méthode qui sera utilisée pour la comparaison. Cela permet de mettre à jour des
	 * indexes contenant certaines données.
	 *
	 * @return ISpecification[]
	 */
	protected function indexes(): array{
		return [
			self::IS_ADMIN => new IsAdmin(),
			self::IS_CLIENT => new IsClient(),
			self::IS_BASIC => new IsBasic(),
			self::IS_ENABLED => new IsEnabled(),
			self::IS_DISABLED => new IsDisabled(),
			self::IS_WAITING_FOR_MAIL_CONFIRM => new IsWaitingForEmailConfirmation(),
			self::IS_WAITING_FOR_PASSWORD_RESET => new IsWaitingForPasswordReset(),
			self::IS_WAITING_FOR_REGISTRATION_CONFIRM => new IsWaitingForRegisteringConfirmation()
		];
	}

	/**
	 *  Méthode appelée lors de la reception d'un événement concernant les utilisateurs
	 *
	 * @param \wfw\engine\core\domain\events\IDomainEvent $e Evenement reçu
	 *
	 * @return EventReceptionReport
	 */
	protected function recieve(IDomainEvent $e): EventReceptionReport {
		/** @var UserEvent $e */
		/** @var User $user */
		$user = $this->getById($e->getAggregateId());

		if(is_null($user)){
			if($e instanceof UserRegisteredEvent){
				$user = new User(
					$e->getAggregateId(),
					$e->getLogin(),
					$e->getPassword(),
					$e->getEmail(),
					$e->getSettings(),
					$e->getState(),
					$e->getType(),
					$e->getModifier()
				);
				return new EventReceptionReport([$user]);
			}return new EventReceptionReport();
		}else{
			if($e instanceof UserRemovedEvent){
				$user->setState($e->getUserState());
				return new EventReceptionReport(null,null,[$user]);
			}else if($e instanceof LoginChangedEvent){
				$user->setLogin($e->getLogin());
			}else if($e instanceof UserConfirmedEvent
				|| $e instanceof AskedForEmailChangeEvent
				|| $e instanceof AskedForPasswordRetrievingEvent
				|| $e instanceof CanceledUserMailChangeEvent
				|| $e instanceof UserPasswordRetrievingCanceledEvent){
				$user->setState($e->getUserState());
			}else if($e instanceof UserMailConfirmedEvent){
				$user->setEmail($e->getEmail());
				$user->setState($e->getUserState());
			}else if($e instanceof UserPasswordChangedEvent){
				$user->setPassword($e->getPassword());
			}else if($e instanceof UserPasswordResetedEvent){
				$user->setPassword($e->getPassword());
				$user->setState($e->getUserState());
			}else if($e instanceof UserSettingsModifiedEvent){
				foreach($e->getSettings() as $k=>$s){
					$user->getSettings()->set($k,$s);
				}
			}else if($e instanceof UserSettingsRemovedEvent){
				foreach($e->getSettings() as $key){
					$user->getSettings()->removeKey($key);
				}
			}else if($e instanceof UserTypeChangedEvent){
				$user->setType($e->getType());
			}else if($e instanceof UserEnabledEvent){
				$user->setState(new EnabledUser());
			}else if($e instanceof UserDisabledEvent){
				$user->setState(new DisabledUser());
			}
		}

		return new EventReceptionReport(null,[$user]);
	}
}