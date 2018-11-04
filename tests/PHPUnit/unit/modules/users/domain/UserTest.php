<?php
namespace wfw\tests\PHPUnit\unit\modules\users\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;
use wfw\engine\package\users\domain\events\LoginChangedEvent;
use wfw\engine\package\users\domain\events\UserConfirmedEvent;
use wfw\engine\package\users\domain\events\UserDisabledEvent;
use wfw\engine\package\users\domain\events\UserEnabledEvent;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\events\UserPasswordChangedEvent;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\events\UserPasswordRetrievingCanceledEvent;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\events\UserRegistrationProcedureCanceledEvent;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\domain\events\UserSettingsModifiedEvent;
use wfw\engine\package\users\domain\events\UserSettingsRemovedEvent;
use wfw\engine\package\users\domain\events\UserTypeChangedEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Basic;
use wfw\engine\package\users\domain\User;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Teste l'aggrégat User
 */
class UserTest extends TestCase{
	public function testUserRegisteredEventIsCorrectlyGenerated(){
		$id = new UUID();
		$login = new Login("Alogin");
		$password = new Password("apasswordd");
		$email = new Email("an@email.fr");
		$settings = new InMemoryUserSettings();
		$state = new EnabledUser();
		$type = new Basic();
		$creator = new UUID();

		$user = new User(
			$id,
			$login,
			$password,
			$email,
			$settings,
			$state,
			$type,
			$creator
		);
		$events = $user->getEventList()->toArray();
		$this->assertEquals(1,count($events));
		/** @var UserRegisteredEvent $e */
		$e = $events[0];
		$this->assertInstanceOf(UserRegisteredEvent::class,$e);
		$this->assertEquals((string)$id,(string)$e->getAggregateId());
		$this->assertEquals((string)$login,(string)$e->getLogin());
		$this->assertTrue($e->getPassword()->equals("apasswordd"));
		$this->assertEquals($settings,$e->getSettings());
		$this->assertEquals($state,$e->getState());
		$this->assertEquals($type,$e->getType());
		$this->assertEquals($creator,$e->getModifier());
	}

	public function testUserRemovedEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$modifier = new UUID();
		$user->remove($modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserRemovedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertInstanceOf(UserRemovedEvent::class,$e);
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertEquals((string) $modifier,$e->getModifier());
	}

	public function testLoginChangedEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$login = new Login("ANewLogin");
		$modifier = new UUID();
		$user->changeLogin($login,$modifier);
		$this->assertEquals(2,$user->getEventList()->count());
		/** @var LoginChangedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertInstanceOf(LoginChangedEvent::class,$e);
		$this->assertEquals($login,$e->getLogin());
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertEquals((string)$modifier,$e->getModifier());
	}

	public function testTryingToChangeLoginOfRemovedUserThrowIllegalInvocationException(){
		$user = $this->createUser();
		$user->remove('');
		$this->expectException(IllegalInvocation::class);
		$user->changeLogin(new Login('a login'),new UUID());
	}

	public function testDisableUserEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$modifier = new UUID();
		$user->disable($modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserDisabledEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertInstanceOf(UserDisabledEvent::class,$e);
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertEquals((string) $modifier,$e->getModifier());
	}

	public function testTryingToDisableARemovedUserThrowIllegalInvocationException(){
		$user = $this->createUser();
		$user->remove('');
		$this->expectException(IllegalInvocation::class);
		$user->disable(new UUID());
	}

	public function testEnableUserEventIsCorrectlyGenerated(){
		$user = $this->createUser(new DisabledUser());
		$modifier = new UUID();
		$user->enable($modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserEnabledEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertInstanceOf(UserEnabledEvent::class,$e);
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertEquals((string) $modifier,$e->getModifier());
	}

	public function testTryingToEnableARemovedUserThrowIllegalInvocationException(){
		$user = $this->createUser();
		$user->remove('');
		$this->expectException(IllegalInvocation::class);
		$user->disable(new UUID());
	}

	public function testUserConfirmedEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$state = new UserWaitingForRegisteringConfirmation($code);
		$modifier = new UUID();
		$user = $this->createUser($state);
		$user->confirm($code,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserConfirmedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(EnabledUser::class,$e->getUserState());
		$this->assertEquals($modifier,$e->getModifier());
	}

	public function testTryingToConfirmARemovedUserThrowIllegalInvocationException(){
		$code = new UserConfirmationCode(new UUID(UUID::V4));
		$user = $this->createUser(
			new UserWaitingForRegisteringConfirmation(
				$code
			)
		);
		$user->remove('');
		$this->expectException(IllegalInvocation::class);
		$user->confirm($code,new UUID());
	}

	public function testTryingToConfirmAnUserThatIsNotInWaitingStateThorwIllegalInvocation(){
		$user = $this->createUser();
		$this->expectException(IllegalInvocation::class);
		$user->confirm(new UserConfirmationCode(new UUID()),new UUID());
	}

	public function testUserCancelConfirmationEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$state = new UserWaitingForRegisteringConfirmation($code);
		$modifier = new UUID();
		$user = $this->createUser($state);
		$user->cancelRegistration($modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserRegistrationProcedureCanceledEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserRegistrationProcedureCanceledEvent::class,$e);
		$this->assertTrue($e->removeUser());
		$this->assertEquals($modifier,$e->getModifier());
	}

	public function testUserCancelConfirmationEventWithoutUserRemovalIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$state = new UserWaitingForRegisteringConfirmation($code);
		$modifier = new UUID();
		$user = $this->createUser($state);
		$user->cancelRegistration($modifier,false);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserRegistrationProcedureCanceledEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserRegistrationProcedureCanceledEvent::class,$e);
		$this->assertFalse($e->removeUser());
		$this->assertEquals($modifier,$e->getModifier());
	}

	public function testTryingToCancelUserRegistrationOfARemovedUserThrowIllegalInvocation(){
		$code = new UserConfirmationCode(new UUID(UUID::V4));
		$user = $this->createUser(
			new UserWaitingForRegisteringConfirmation(
				$code
			)
		);
		$user->remove('');
		$this->expectException(IllegalInvocation::class);
		$user->cancelRegistration(new UUID());
	}

	public function testTryingToCancelAnUserThatIsNotInWaitingStateThrowIllegalInvocation(){
		$user = $this->createUser();
		$this->expectException(IllegalInvocation::class);
		$user->cancelRegistration(new UUID());
	}

	public function testAskedForUserMailChangeEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$mail = new Email("a.new@email.com");
		$modifier = new UUID();
		$user = $this->createUser();
		$user->changeEmail($mail,$code,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var AskedForEmailChangeEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(AskedForEmailChangeEvent::class,$e);
		$this->assertEquals($mail,$e->getEmail());
		$this->assertEquals($modifier,$e->getModifier());
		/** @var UserWaitingForEmailConfirmation $state */
		$state = $e->getUserState();
		$this->assertInstanceOf(UserWaitingForEmailConfirmation::class,$state);
		$this->assertTrue($state->isValide($code));
	}

	public function testTryingToChangeMailOfARemovedUserThatThrowIllegalInvocation(){
		$user = $this->createUser();
		$user->remove(new UUID());
		$this->expectException(IllegalInvocation::class);
		$user->changeEmail(
			new Email("another@mail.fr"),
			new UserConfirmationCode(new UUID(UUID::V4)),
			new UUID()
		);
	}

	public function testCanceledChangeMailEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$mail = new Email("a.new@email.com");
		$modifier = new UUID();
		$canceler = new UUID();
		$user = $this->createUser();
		$user->changeEmail($mail,$code,$modifier);
		$user->cancelEmailChange($canceler);

		$this->assertEquals(3,$user->getEventList()->count());
		/** @var CanceledUserMailChangeEvent $e */
		$e = $user->getEventList()->toArray()[2];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(CanceledUserMailChangeEvent::class,$e);
		$this->assertEquals($canceler,$e->getModifier());
	}

	public function testTryingToCancelChangeMailOfARemovedUserThatThrowIllegalInvocation(){
		$user = $this->createUser();
		$user->remove(new UUID());
		$this->expectException(IllegalInvocation::class);
		$user->cancelEmailChange(new UUID());
	}

	public function testTryingToChangeMailOfUserNotInWaitingStateThrowIllegalInvocation(){
		$user = $this->createUser();
		$this->expectException(IllegalInvocation::class);
		$user->cancelEmailChange(new UUID());
	}

	public function testUserMailConfirmedEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$mail = new Email("a.new@email.com");
		$modifier = new UUID();
		$confirmer = new UUID();
		$user = $this->createUser();
		$user->changeEmail($mail,$code,$modifier);
		$user->confirmEmail($code,$confirmer);

		$this->assertEquals(3,$user->getEventList()->count());
		/** @var UserMailConfirmedEvent $e */
		$e = $user->getEventList()->toArray()[2];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserMailConfirmedEvent::class,$e);
		$this->assertEquals($confirmer,$e->getModifier());
	}

	public function testTryingToConfirmMailOfARemovedUserThatThrowIllegalInvocation(){
		$user = $this->createUser();
		$user->remove(new UUID());
		$this->expectException(IllegalInvocation::class);
		$user->confirmEmail(new UserConfirmationCode(new UUID(UUID::V4)),new UUID());
	}

	public function testTryingToConfirmMailOfUserNotInWaitingStateThrowIllegalInvocation(){
		$user = $this->createUser();
		$this->expectException(IllegalInvocation::class);
		$user->confirmEmail(new UserConfirmationCode(new UUID(UUID::V4)),new UUID());
	}

	public function testUserPasswordChangedEventIsCorrectlyGenerated(){
		$np = new Password("A new password @");
		$modifier = new UUID();
		$user = $this->createUser();
		$user->changePassword("passwordd",$np,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserPasswordChangedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserPasswordChangedEvent::class,$e);
		$this->assertEquals($modifier,$e->getModifier());
		$this->assertTrue($e->getPassword()->equals("A new password @"));
	}

	public function testTryingToChangePasswordWithWrongOldPasswordThrowInvalidArgumentException(){
		$user = $this->createUser();
		$this->expectException(\InvalidArgumentException::class);
		$user->changePassword("wrong passwd :(",new Password("anotherpassword"),new UUID());
	}

	public function testAskedForPasswordRetrievingEventisCorrectlyGenerated(){
		$modifier = new UUID();
		$code = new UserConfirmationCode(new UUID(UUID::V4));
		$user = $this->createUser();
		$user->retrievePassword($code,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var AskedForPasswordRetrievingEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(AskedForPasswordRetrievingEvent::class,$e);
		$this->assertEquals($modifier,$e->getModifier());
		$this->assertInstanceOf(UserWaitingForPasswordReset::class,$e->getUserState());
		/** @var UserWaitingForPasswordReset $state */
		$state = $e->getUserState();
		$this->assertEquals($code,$state->getCode());
		$this->assertTrue($state->isValide($code));
	}

	public function testTryingToAskPasswordRetrievingOfARemovedUserThrowIllegalInvocation(){
		$user = $this->createUser();
		$user->remove(new UUID());
		$this->expectException(IllegalInvocation::class);
		$user->retrievePassword(new UserConfirmationCode(new UUID(UUID::V4)),new UUID());
	}

	public function testCanceledPasswordRetrivingEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$modifier = new UUID();
		$canceler = new UUID();
		$user = $this->createUser();
		$user->retrievePassword($code,$modifier);
		$user->cancelRetrivingPassword($canceler);

		$this->assertEquals(3,$user->getEventList()->count());
		/** @var UserPasswordRetrievingCanceledEvent $e */
		$e = $user->getEventList()->toArray()[2];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserPasswordRetrievingCanceledEvent::class,$e);
		$this->assertEquals($canceler,$e->getModifier());
	}

	public function testTryingToCancelPasswordRetrievingOfARemovedUserThrowIllegalInvocation(){
		$user = $this->createUser();
		$user->remove(new UUID());
		$this->expectException(IllegalInvocation::class);
		$user->cancelRetrivingPassword(new UUID());
	}

	public function testTryingToCancelPasswordRetrievingOfAUserNotInWaitingStateThrowIllegalInvocation(){
		$user = $this->createUser();
		$this->expectException(IllegalInvocation::class);
		$user->cancelRetrivingPassword(new UUID());
	}

	public function testUserPasswordResetedEventIsCorrectlyGenerated(){
		$code = new UserConfirmationCode("a code");
		$modifier = new UUID();
		$canceler = new UUID();
		$np = new Password("another password");
		$user = $this->createUser();
		$user->retrievePassword($code,$modifier);
		$user->resetPassword($np,$code,$canceler);

		$this->assertEquals(3,$user->getEventList()->count());
		/** @var UserPasswordResetedEvent $e */
		$e = $user->getEventList()->toArray()[2];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserPasswordResetedEvent::class,$e);
		$this->assertEquals($canceler,$e->getModifier());
		$this->assertTrue($e->getPassword()->equals("another password"));
	}

	public function testTryingToResetPasswordWithWrongCodeThrowInvalidArgumentException(){
		$code = new UserConfirmationCode("a code");
		$np = new Password("another password");
		$user = $this->createUser();
		$user->retrievePassword($code,new UUID());
		$this->expectException(\InvalidArgumentException::class);
		$user->resetPassword($np,new UserConfirmationCode("wrong code"),new UUID());
	}

	public function testTryingToResetPasswordOfAnUserThtIsNotInWaitingStateThrowIllegalInvocation(){
		$user = $this->createUser(new DisabledUser());
		$this->expectException(IllegalInvocation::class);
		$user->resetPassword(
			new Password("passwordd"),
			new UserConfirmationCode("the code"),
			new UUID()
		);
	}

	public function testModifiedUserSettingsEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$modifier = new UUID();
		$settings = [
			"a/key" => false,
			"a/trueKey" => true,
			"an/array" => [1,2,3],
			"an/object" => ["prop"=>"value"],
			"a/string" => "fancy things to store",
			"an/integer" => 42,
			"a/float" => 42.42
		];
		$user->modifySettings($settings,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserSettingsModifiedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserSettingsModifiedEvent::class,$e);
		$this->assertEquals((string)$modifier,$e->getModifier());
		$this->assertEquals($settings,$e->getSettings());
	}

	public function testRemovedUserSettingsEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$modifier = new UUID();
		$remover = new UUID();
		$settings = [
			"a/key" => false,
			"a/trueKey" => true,
			"an/array" => [1,2,3],
			"an/object" => ["prop"=>"value"],
			"a/string" => "fancy things to store",
			"an/integer" => 42,
			"a/float" => 42.42
		];
		$removedSettings = ["an/object","a/string"];
		$user->modifySettings($settings,$modifier);
		$user->removeSettings($removedSettings,$remover);

		$this->assertEquals(3,$user->getEventList()->count());
		/** @var UserSettingsRemovedEvent $e */
		$e = $user->getEventList()->toArray()[2];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserSettingsRemovedEvent::class,$e);
		$this->assertEquals((string) $remover,$e->getModifier());
		$this->assertEquals($removedSettings,$e->getSettings());
	}

	public function testModifySettingsWithInvalidIntKeyShouldThrowAnException(){
		$user = $this->createUser();
		$modifier = new UUID();
		$settings = [
			"a value :/",
			"a/key" => false,
			"a/trueKey" => true,
			"an/array" => [1,2,3],
			"an/object" => ["prop"=>"value"],
			"a/string" => "fancy things to store",
			"an/integer" => 42,
			"a/float" => 42.42
		];
		$this->expectException(\InvalidArgumentException::class);
		$user->modifySettings($settings,$modifier);
	}

	public function testRemoveSettingsWithInvalidIntKeyShouldThrowAnException(){
		$user = $this->createUser();
		$modifier = new UUID();
		$settings = [
			0
		];
		$this->expectException(\InvalidArgumentException::class);
		$user->removeSettings($settings,$modifier);
	}

	public function testChangeUserTypeEventIsCorrectlyGenerated(){
		$user = $this->createUser();
		$modifier = new UUID();
		$type = new Basic();
		$user->changeType($type,$modifier);

		$this->assertEquals(2,$user->getEventList()->count());
		/** @var UserTypeChangedEvent $e */
		$e = $user->getEventList()->toArray()[1];
		$this->assertEquals($user->getId(),$e->getAggregateId());
		$this->assertInstanceOf(UserTypeChangedEvent::class,$e);
		$this->assertEquals((string)$modifier,$e->getModifier());
		$this->assertEquals($type,$e->getType());
	}

	private function createUser(?UserState $state = null):User{
		return new User(
			new UUID(),
			new Login("Test"),
			new Password("passwordd"),
			new Email("an@email.com"),
			new InMemoryUserSettings(),
			$state ?? new EnabledUser(),
			new Admin(),
			''
		);
	}
}