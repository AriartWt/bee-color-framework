<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 02:57
 */

namespace wfw\engine\package\users\domain;

use InvalidArgumentException;
use wfw\engine\core\domain\aggregate\AggregateRoot;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;

use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;
use wfw\engine\package\users\domain\events\LoginChangedEvent;
use wfw\engine\package\users\domain\events\UserDisabledEvent;
use wfw\engine\package\users\domain\events\UserEnabledEvent;
use wfw\engine\package\users\domain\events\UserPasswordRetrievingCanceledEvent;
use wfw\engine\package\users\domain\events\UserConfirmedEvent;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\events\UserPasswordChangedEvent;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\events\UserRegistrationProcedureCanceledEvent;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\domain\events\UserSettingsModifiedEvent;
use wfw\engine\package\users\domain\events\UserSettingsRemovedEvent;
use wfw\engine\package\users\domain\events\UserTypeChangedEvent;

use wfw\engine\package\users\domain\settings\UserSettings;

use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\RemovedUser;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\states\UserState;

use wfw\engine\package\users\domain\types\UserType;

use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  Utilisateur
 */
class User extends AggregateRoot
{
	/** @var Login $_login */
	private $_login;
	/** @var Password $_password */
	private $_password;
	/** @var Email $_email */
	private $_email;
	/** @var UserSettings */
	private $_settings;
	/** @var UserState $_state */
	private $_state;
	/** @var UserType $_type */
	private $_type;
	/** @var string $_creator */
	private $_creator;
	
	/**
	 *  User constructor.
	 *
	 * @param UUID         $id       Identifiant de l'utilisateur
	 * @param Login        $login    Login de l'utilisateur
	 * @param Password     $password Mot de passe de l'utilisateur
	 * @param Email        $email    Email
	 * @param UserSettings $settings Paramètres
	 * @param UserState    $state    Etat
	 * @param UserType     $type     Type d'utilisateur
	 * @param string       $creator  identifiant de l'utilisateur ayant créé l'utilisateur coruant
	 */
	public function __construct(
		UUID $id,
		Login $login,
		Password $password,
		Email $email,
		UserSettings $settings,
		UserState $state,
		UserType $type,
		string $creator
	){
		parent::__construct(new UserRegisteredEvent(
			$id,
			$login,
			$password,
			$email,
			$settings,
			$state,
			$type,
			$creator
		));
	}

	/**
	 *  Teste la validité d'un mot de passe sur l'utilisateur courant
	 *
	 * @param string $password Mot de passe à tester
	 *
	 * @return bool
	 */
	private function checkPassword(string $password):bool{
		return $this->_password->equals($password);
	}
	
	/**
	 *  Nouveau login
	 *
	 * @param Login  $login Nouveau login
	 * @param string $modifier Utilisateur à l'origine de la modification
	 */
	public function changeLogin(Login $login,string $modifier){
		$this->throwIfRemoved();
		$this->registerEvent(new LoginChangedEvent(
			$this->getId(),$login,$modifier
		));
	}

	/**
	 * @param UserConfirmationCode $code Code de confirmation
	 *
	 * @param string $confirmer Utilisateur validant la confirmation
	 * @param null|UserState $state Etat de l'utilisateur après la confirmation.
	 * @throws IllegalInvocation Si l'utilisateur n'est pas en état d'attente de confirmation
	 * @throws InvalidArgumentException
	 */
	public function confirm(UserConfirmationCode $code, string $confirmer, ?UserState $state = null){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForRegisteringConfirmation){
			if($this->_state->isValide($code)){
				$this->registerEvent(new UserConfirmedEvent(
					$this->getId(),$state ?? new EnabledUser(),$confirmer)
				);
			}else{
				throw new InvalidArgumentException("Invalid validation code!");
			}
		}else{
			throw new IllegalInvocation("The user is not in registration confirmation state !");
		}
	}

	/**
	 * @param string $modifier Utilisateur demandant l'annulation de la procédure.
	 * @param bool $remove
	 * @throws IllegalInvocation
	 */
	public function cancelRegistration(string $modifier, bool $remove=true){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForRegisteringConfirmation){
			$this->registerEvent(new UserRegistrationProcedureCanceledEvent(
				$this->getId(),$modifier,$remove
			));
		}else{
			throw new IllegalInvocation("The user is not in registration confirmation state !");
		}
	}
	
	/**
	 *  Permet de tenter de changer l'adresse mail de l'utilisateur courant. Place l"utilisateur dans l'état UserWaitingForEmailConfirmation
	 *
	 * @param Email                $email Nouvel email
	 * @param UserConfirmationCode $code  Code de confirmation pour vérifier l'adresse mail
	 * @param string               $asker Utilisateur à l'origine de la demande
	 * @throws IllegalInvocation
	 */
	public function changeEmail(Email $email, UserConfirmationCode $code, string $asker){
		$this->throwIfRemoved();
		$this->registerEvent(new AskedForEmailChangeEvent(
			$this->getId(),
			$email,
			$code,
			new UserWaitingForEmailConfirmation($email,$code),
			$asker)
		);
	}
	
	/**
	 *  Annule la procédure de changement d'adresse email de l'utilisateur courant si l'utilisateur courant est en attente de confirmaton pour sa nouvelle adresse mail
	 *
	 * @param string $canceler Identifiant de l'utilisateur à l'origine de l'annulation
	 * @throws IllegalInvocation
	 */
	public function cancelEmailChange(string $canceler){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForEmailConfirmation){
			$this->registerEvent(new CanceledUserMailChangeEvent(
				$this->getId(),
				new EnabledUser(),
				$canceler
			));
		}else{
			throw new IllegalInvocation("Cannot cancel email change : the current user is not waiting for email confirmation");
		}
	}

	/**
	 * Confirme l'email d'un utilisateur
	 *
	 * @param UserConfirmationCode $code Code de validation
	 * @param string $confirmer Identifiant de l'utilisateur à l'origine de
	 *                                        la confirmation
	 * @param null|UserState $state Etat de l'utilisateur à la fin de la procédure
	 * @throws IllegalInvocation
	 * @throws InvalidArgumentException
	 */
	public function confirmEmail(UserConfirmationCode $code,string $confirmer,?UserState $state=null){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForEmailConfirmation){
			if($this->_state->isValide($code)){
				$this->registerEvent(new UserMailConfirmedEvent(
					$this->getId(),
					$this->_state->getEmail(),
					$state ?? new EnabledUser(),
					$confirmer
				));
			}else{
				throw new InvalidArgumentException("Invalid validation code !");
			}
		}else{
			throw new IllegalInvocation("The user is not in email confirmation state !");
		}
	}
	
	/**
	 *  Change le mot de passe de l'utilisateur. Nécessite l'ancien mot de passe
	 *
	 * @param string $old        Ancien mot de passe
	 * @param Password $password Nouveau mot de passe
	 * @param string   $modifier Identifiant de l'utilisateur à l'origine du changement
	 * @throws InvalidArgumentException
	 */
	public function changePassword(string $old, Password $password,string $modifier){
		$this->throwIfRemoved();
		if($this->checkPassword($old)){
			$this->registerEvent(new UserPasswordChangedEvent(
				$this->getId(),$password,$modifier
			));
		}else{
			throw new InvalidArgumentException("Cannot modify password : the given old password is wrong !");
		}
	}
	
	/**
	 *  Enclenche la procédure de réinitialisation du mot de passe de l'utilisateur.
	 * @param UserConfirmationCode $code Code de vérification
	 * @param string               $modifier Utilisateur à l'origine de la demande
	 * @throws IllegalInvocation
	 */
	public function retrievePassword(UserConfirmationCode $code,string $modifier){
		$this->throwIfRemoved();
		$this->registerEvent(new AskedForPasswordRetrievingEvent(
			$this->getId(),
			$code,
			new UserWaitingForPasswordReset($code),
			$modifier
		));
	}
	
	/**
	 *  Annule la procédure de réinitialisation du mot de passe
	 *
	 * @param string $canceler Identifiant de l'utilisateur à l'origine de l'annulation de la demande
	 * @throws IllegalInvocation
	 */
	public function cancelRetrivingPassword(string $canceler){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForPasswordReset){
			$this->registerEvent(new UserPasswordRetrievingCanceledEvent(
				$this->getId(), new EnabledUser(),$canceler
			));
		}else{
			throw new IllegalInvocation("Cannot cancel password retriving : user is not in retriving state !");
		}
	}

	/**
	 *  Réinitialise le mot de passe de l'utilisateur courant
	 *
	 * @param Password $password Nouveau mot de passe
	 * @param UserConfirmationCode $code Code de confirmation
	 * @param string $reseter Utilisateur effectuant le reset du mot de passe
	 * @param null|UserState $state Etat de l'utilisatuer après la procédure
	 * @throws IllegalInvocation
	 * @throws InvalidArgumentException
	 */
	public function resetPassword(
		Password $password,
		UserConfirmationCode $code,
		string $reseter,
		?UserState $state = null
	){
		$this->throwIfRemoved();
		if($this->_state instanceof UserWaitingForPasswordReset){
			if($this->_state->isValide($code)){
				$this->registerEvent(new UserPasswordResetedEvent(
					$this->getId(),
					$password,
					$state ?? new EnabledUser(),
					$reseter
				));
			}else{
				throw new InvalidArgumentException("Wrong confirmation code !");
			}
		}else{
			throw new IllegalInvocation("Cannot reset password : user is not in UserWaitingForPasswordReset state !");
		}
	}
	
	/**
	 *  Applique des modifications à une liste de clé de paramètres.
	 *
	 * @param array  $settings Liste des paramètres utilisateur à changer (clé/valeur)
	 * @param string $modifier Utilisateur modifiant le(s) paramètre(s)
	 * @throws InvalidArgumentException
	 */
	public function modifySettings(array $settings, string $modifier){
		$this->throwIfRemoved();
		foreach($settings as $k=>$s){
			if(!is_string($k)){
				throw new InvalidArgumentException("Invalid index at offset $k ! (Only string index are allowed)");
			}
		}
		$this->registerEvent(new UserSettingsModifiedEvent(
			$this->getId(),$settings,$modifier)
		);
	}
	
	/**
	 *  Supprime les clés paramètres contenues dans $settings
	 *
	 * @param array  $settings Liste des clés à supprimer
	 * @param string $remover  Identifiant de l'utilisateur à l'origine de la suppression
	 * @throws InvalidArgumentException
	 */
	public function removeSettings(array $settings, string $remover){
		$this->throwIfRemoved();
		foreach($settings as $k=>$s){
			if(!is_string($s)){
				throw new InvalidArgumentException("Invalid key at offset $k ! (Only string index are allowed)");
			}else if(!$this->_settings->existsKey($s)){
				throw new InvalidArgumentException("Invalid key at offset $k : $s doesn't exists !");
			}
		}
		$this->registerEvent(new UserSettingsRemovedEvent(
			$this->getId(),$settings,$remover
		));
	}
	
	/**
	 *  Change le type de l'utilisateur courant
	 *
	 * @param UserType $type Nouveau type d'utilisateur
	 * @param string   $modifier Identifiant de l'utilisateur à l'origine de la modification
	 */
	public function changeType(UserType $type, string $modifier){
		$this->throwIfRemoved();
		$this->registerEvent(new UserTypeChangedEvent(
			$this->getId(),$type,$modifier
		));
	}
	
	/**
	 * Désactive l'utilisateur courant
	 *
	 * @param string $disabler Identifiant de l'utilisateur à l'origine de la désactivation
	 */
	public function disable(string $disabler){
		$this->throwIfRemoved();
		$this->registerEvent(new UserDisabledEvent($this->getId(),$disabler));
	}
	
	/**
	 * Active l'utilisateur courant
	 *
	 * @param string $enabler identifiant de l'utilisatuer à l'origine de l'activation
	 */
	public function enable(string $enabler){
		$this->throwIfRemoved();
		$this->registerEvent(new UserEnabledEvent($this->getId(),$enabler));
	}
	
	/**
	 *  Place l'utilisateur courant dans l'état "supprimé"
	 *
	 * @param string $remover identifiant de l'utilisateur à l'origine de la suppression
	 */
	public function remove(string $remover){
		$this->throwIfRemoved("This user is already in a removed state !");
		$this->registerEvent(new UserRemovedEvent(
			$this->getId(),new RemovedUser(),$remover
		));
	}

	/**
	 * @return bool
	 */
	protected function isRemoved():bool{
		return $this->_state instanceof RemovedUser;
	}

	/**
	 * @param string $message
	 * @throws IllegalInvocation
	 */
	private function throwIfRemoved(string $message="Can't modify a removed user !"):void{
		if($this->isRemoved())
			throw new IllegalInvocation($message);
	}

	/**
	 * @param UserRegisteredEvent $e
	 */
	protected function applyUserRegisteredEvent(UserRegisteredEvent $e){
		$this->_login = $e->getLogin();
		$this->_password = $e->getPassword();
		$this->_email = $e->getEmail();
		$this->_settings = $e->getSettings();
		$this->_state = $e->getState();
		$this->_type  = $e->getType();
		$this->_creator = $e->getModifier();
	}

	/**
	 *  Applique l'événement de changement de login
	 * @param LoginChangedEvent $e Evenement de changement de login
	 */
	protected function applyLoginChangedEvent(LoginChangedEvent $e){
		$this->_login = $e->getLogin();
	}

	/**
	 *  Applique l'événement de confirmation de l'utilisateur
	 * @param UserConfirmedEvent $e Event de confirmation
	 */
	protected function applyUserConfirmedEvent(UserConfirmedEvent $e){
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement de confirmation de l'adresse mail de l'utilisateur
	 * @param UserMailConfirmedEvent $e Event de confirmation
	 */
	protected function applyUserMailConfirmedEvent(UserMailConfirmedEvent $e){
		$this->_email = $e->getEmail();
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement de demande de changement de l'adresse mail de l'utilisateur. Place l'utilisateur dans
	 *        l'état UserWaitingForEmailConfirmation
	 * @param AskedForEmailChangeEvent $e Evenement de demande de changement d'adresse mail
	 */
	protected function applyAskedForEmailChangeEvent(AskedForEmailChangeEvent $e){
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement d'annulation de changement d'adresse mail. Replace l'utilisateur dans l'état Enabled
	 * @param CanceledUserMailChangeEvent $e Evenement d'annulation de changement d'adresse mail
	 */
	protected function applyCanceledUserMailChangeEvent(CanceledUserMailChangeEvent $e){
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement de changement de mot de passe de l'utilisateur.
	 * @param UserPasswordChangedEvent $e Evenemnt de changement de mot de passe
	 */
	protected function applyUserPasswordChangedEvent(UserPasswordChangedEvent $e){
		$this->_password = $e->getPassword();
	}

	/**
	 *  Applique l'événement de réinitialisation de mot de passe
	 * @param UserPasswordResetedEvent $e Evenement de réinitialisation de mot de passe
	 */
	protected function applyUserPasswordResetedEvent(UserPasswordResetedEvent $e){
		$this->_password = $e->getPassword();
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement de demande de réinitialisation de mot de passe. Place l'utilisateur courant dans
	 *        l'état UserWaitingForRe
	 * @param AskedForPasswordRetrievingEvent $e Evenemnt de demande de réinitialisation de mot de passe
	 */
	protected function applyAskedForPasswordRetrievingEvent(AskedForPasswordRetrievingEvent $e){
		$this->_state = $e->getUserState();
	}

	/**
	 *  Annule la demande de réinitialisation de mot de passe.
	 * @param UserPasswordRetrievingCanceledEvent $e Evenement d'annulation de réinitialisation de mot de passe
	 */
	protected function applyUserPasswordRetrievingCanceledEvent(UserPasswordRetrievingCanceledEvent $e){
		$this->_state = $e->getUserState();
	}

	/**
	 *  Applique l'événement de modifications de paramètres utilisateurs.
	 * @param UserSettingsModifiedEvent $e Evenement de modification de clé de paramètres
	 */
	protected function applyUserSettingsModifiedEvent(UserSettingsModifiedEvent $e){
		foreach($e->getSettings() as $key=>$value){
			$this->_settings->set($key,$value);
		}
	}

	/**
	 *  Applique l'événement de suppression de clés de paramètres
	 * @param UserSettingsRemovedEvent $e Evenement de suppression de clés de paramètres
	 */
	protected function applyUserSettingsRemovedEvent(UserSettingsRemovedEvent $e){
		foreach($e->getSettings() as $key){
			$this->_settings->removeKey($key);
		}
	}

	/**
	 *  Applique l'événement de changement de type d'utilisateur
	 *
	 * @param UserTypeChangedEvent $e Evenement de changement de type d'utilisateur
	 */
	protected function applyUserTypeChangedEvent(UserTypeChangedEvent $e){
		$this->_type = $e->getType();
	}

	/**
	 *  Applique l'événement de suppression de l'utilisateur
	 * @param UserRemovedEvent $e Evenement de suppression de l'utilisateur
	 */
	protected function applyUserRemovedEvent(UserRemovedEvent $e){
		$this->_state = $e->getUserState();
	}
	
	/**
	 * Applique l'événement de désactivation de l'utilisateur
	 * @param UserDisabledEvent $e Evenement de désactivation de l'utilisateur
	 */
	protected function applyUserDisabledEvent(UserDisabledEvent $e){
		$this->_state = new DisabledUser();
	}
	
	/**
	 * Applique l'événement d'activation de l'utilisateur
	 * @param UserEnabledEvent $e Activation de l'utilisateur
	 */
	protected function applyUserEnabledEvent(UserEnabledEvent $e){
		$this->_state = new EnabledUser();
	}

	/**
	 * @param UserRegistrationProcedureCanceledEvent $e
	 */
	protected function applyUserRegistrationProcedureCanceledEvent(UserRegistrationProcedureCanceledEvent $e){
		$this->_type = $e->removeUser() ? new RemovedUser() : new DisabledUser();
	}
}