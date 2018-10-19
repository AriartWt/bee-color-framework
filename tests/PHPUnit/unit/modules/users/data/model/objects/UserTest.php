<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 14:32
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\objects;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\types\Client;

/**
 * Teste l'objet model $user
 */
class UserTest extends TestCase {
	public function testUserConstruction(){
		$id = new UUID();
		$login = new Login("A fancy login :)");
		$password = new Password("A superStrong89 Password/86547@");
		$email=new Email("lolilol_go@forum.net");
		$settings = new InMemoryUserSettings();
		$state = new DisabledUser();
		$type = new Client();
		$creator = (string) new UUID();
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

		$this->assertEquals($id,$user->getId());
		$this->assertEquals($login,$user->getLogin());
		$this->assertEquals($password,$user->getPassword());
		$this->assertEquals($email, $user->getEmail());
		$this->assertEquals($settings,$user->getSettings());
		$this->assertEquals($state,$user->getState());
		$this->assertEquals($type,$user->getType());
		$this->assertEquals($creator,$user->getCreator());
	}

	public function testToDTO(){
		$id = new UUID();
		$login = new Login("A fancy login :)");
		$password = new Password("A superStrong89 Password/86547@");
		$email=new Email("lolilol_go@forum.net");
		$settings = new InMemoryUserSettings();
		$state = new DisabledUser();
		$type = new Client();
		$creator = (string) new UUID();
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
		/** @var \wfw\engine\package\users\data\model\DTO\User $dto */
		$dto = $user->toDTO();

		$this->assertEquals($user->getId(),$dto->getId());
		$this->assertEquals($user->getLogin(),$dto->getLogin());
		$this->assertEquals($user->getPassword(),$dto->getPassword());
		$this->assertEquals($user->getEmail(),$dto->getEmail());
		$this->assertEquals($user->getSettings(),$dto->getSettings());
		$this->assertEquals($user->getState(),$dto->getState());
		$this->assertEquals($user->getType(),$dto->getType());
		$this->assertEquals($user->getCreator(),$dto->getCreator());
	}
}