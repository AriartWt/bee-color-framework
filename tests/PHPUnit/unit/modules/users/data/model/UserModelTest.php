<?php
namespace wfw\tests\PHPUnit\unit\modules\users\data\model;

use PHPUnit\Framework\TestCase;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticParser;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSearcher;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSolver;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\specs\LoginIs;
use wfw\engine\package\users\data\model\UserModel;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;
use wfw\engine\package\users\domain\events\LoginChangedEvent;
use wfw\engine\package\users\domain\events\UserConfirmedEvent;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\events\UserPasswordChangedEvent;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\events\UserPasswordRetrievingCanceledEvent;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\domain\events\UserSettingsModifiedEvent;
use wfw\engine\package\users\domain\events\UserSettingsRemovedEvent;
use wfw\engine\package\users\domain\events\UserTypeChangedEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\RemovedUser;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Basic;
use wfw\engine\package\users\domain\types\UserType;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Test le UserModel
 */
class UserModelTest extends TestCase {
	public function testUserRegisteredEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$e = $this->createUserRegisteredEvent()
		],true);

		$user = $model->find("id='".$e->getAggregateId()."'");
		$this->assertCount(1,$user);
		/** @var User $user */
		$user = $user[0];
		$this->assertInstanceOf(User::class,$user);
		$this->assertEquals($e->getPassword(),$user->getPassword());
		$this->assertEquals($e->getLogin(),$user->getLogin());
		$this->assertEquals($e->getType(),$user->getType());
		$this->assertEquals($e->getEmail(),$user->getEmail());
		$this->assertEquals($e->getSettings(),$user->getSettings());
		$this->assertEquals($e->getState(),$user->getState());

		$this->assertCount(1,$model->find(UserModel::IS_ENABLED));
		$this->assertCount(0,$model->find(UserModel::IS_DISABLED));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_PASSWORD_RESET));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_MAIL_CONFIRM));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_REGISTRATION_CONFIRM));
		$this->assertCount(1,$model->find(UserModel::IS_BASIC));
		$this->assertCount(0,$model->find(UserModel::IS_CLIENT));
		$this->assertCount(0,$model->find(UserModel::IS_ADMIN));
	}

	public function testUserRemovedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		$user = $users[0];
		$model->recieveDomainEvent($this->createRemovedEvent($user->getId()));
		$this->assertCount(0,$model->find("id='".$user->getId()."'"));

		$this->assertCount(1,$model->find(UserModel::IS_ENABLED));
		$this->assertCount(0,$model->find(UserModel::IS_DISABLED));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_PASSWORD_RESET));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_MAIL_CONFIRM));
		$this->assertCount(0,$model->find(UserModel::IS_WAITING_FOR_REGISTRATION_CONFIRM));
		$this->assertCount(1,$model->find(UserModel::IS_BASIC));
		$this->assertCount(0,$model->find(UserModel::IS_CLIENT));
		$this->assertCount(0,$model->find(UserModel::IS_ADMIN));
	}

	public function testLoginChangedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		$user = $users[0];
		$model->recieveDomainEvent($this->createLoginChangedEvent($user->getId(), "new login @"));
		$res = $model->find((string)new LoginIs("new login @"));
		$this->assertCount(1,$res);
		$this->assertEquals($user->getId(),$res[0]->getId());
	}

	public function testUserConfirmedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserConfirmedEvent($user->getId(), new EnabledUser(), new UUID()));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(EnabledUser::class,$u->getState());
	}

	public function testASkedForMailChangeCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(
			new AskedForEmailChangeEvent(
				$user->getId(),
				new Email("new@email.com"),
				new UserConfirmationCode("code"),
				new UserWaitingForEmailConfirmation(
					new Email("new@email.com"),
					new UserConfirmationCode("code")
				),
				new UUID()
			)
		);
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(UserWaitingForEmailConfirmation::class,$u->getState());
	}

	public function testASkedForPasswordRetrievingCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(
			new AskedForPasswordRetrievingEvent(
				$user->getId(),
				new UserConfirmationCode("code"),
				new UserWaitingForPasswordReset(
					new UserConfirmationCode("code")
				),
				new UUID()
			)
		);
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(UserWaitingForPasswordReset::class,$u->getState());
	}

	public function testCanceledusermailChangeEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(
			new CanceledUserMailChangeEvent(
				$user->getId(),
				new EnabledUser(),
				new UUID()
			)
		);
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(EnabledUser::class,$u->getState());
	}

	public function testuserPasswordRetrivingCanceledEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(
			new UserPasswordRetrievingCanceledEvent(
				$user->getId(),
				new EnabledUser(),
				new UUID()
			)
		);
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(EnabledUser::class,$u->getState());
	}

	public function testUserMailConfirmedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent($this->createMailConfirmedEvent($user->getId(), "newmail@mail.fr"));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(EnabledUser::class,$u->getState());
		$this->assertNotEquals($user->getEmail(),$u->getEmail());
	}

	public function testUserPasswordChangedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserPasswordChangedEvent(
			$user->getId(),
			new Password("a new password 78"),
			new UUID()
		));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertTrue($u->getPassword()->equals("a new password 78"));
	}

	public function testUserPasswordResetedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserPasswordResetedEvent(
			$user->getId(),
			new Password("a new password 78"),
			new EnabledUser(),
			new UUID()
		));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertTrue($u->getPassword()->equals("a new password 78"));
		$this->assertInstanceOf(EnabledUser::class,$u->getState());
	}

	public function testUserSettingsModifiedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserSettingsModifiedEvent(
			$user->getId(),
			[
				"a/new/key" => 156
			],
			new UUID()
		));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertEquals(156,$u->getSettings()->getInt("a/new/key"));
	}

	public function testUserSettingsRemovedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserSettingsModifiedEvent(
			$user->getId(),
			[
				"a/new/key" => 156
			],
			new UUID()
		));
		$model->recieveDomainEvent(new UserSettingsRemovedEvent(
			$user->getId(),
			[
				"a/new/key"
			],
			new UUID()
		));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertNull($u->getSettings()->getInt("a/new/key"));
	}

	public function testUserTypeChangedEventCorrectlyHandled(){
		$model = $this->createModel();
		$users = $model->find("id");
		/** @var User $user */
		$user = $users[0];
		$model->recieveDomainEvent(new UserTypeChangedEvent(
			$user->getId(),
			new Admin(),
			new UUID()
		));
		/** @var User $u */
		$u = $model->find("id='".$user->getId()."'")[0];
		$this->assertInstanceOf(Admin::class,$u->getType());
	}

	private function createModel(array $events=[],bool $empty = false):UserModel{
		$model = new UserModel(
			new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser()))
		);
		if(!$empty){
			$events = array_merge(
				[$this->createUserRegisteredEvent()],
				$events,
				[$this->createUserRegisteredEvent()]
			);
		}
		foreach($events as $e){$model->recieveDomainEvent($e);}
		return $model;
	}
	private function createUserRegisteredEvent(?UserState $state=null,?UserType $type=null):UserRegisteredEvent{
		return new UserRegisteredEvent(
			new UUID(),
			new Login("Test"),
			new Password("testtest"),
			new Email("test@email.com"),
			new InMemoryUserSettings(),
			$state ?? new EnabledUser(),
			$type ?? new Basic(),
			new UUID()
		);
	}
	private function createRemovedEvent(UUID $user):UserRemovedEvent{
		return new UserRemovedEvent($user,new RemovedUser(),new UUID());
	}
	private function createLoginChangedEvent(UUID $user, string $login):LoginChangedEvent{
		return new LoginChangedEvent($user,new Login($login),new UUID());
	}
	private function createMailConfirmedEvent(UUID $user, string $mail):UserMailConfirmedEvent{
		return new UserMailConfirmedEvent($user,new Email($mail),new EnabledUser(),new UUID());
	}
}