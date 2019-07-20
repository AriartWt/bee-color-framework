<?php

namespace wfw\tests\PHPUnit\integration\modules\users;

use PHPUnit\Framework\TestCase;
use wfw\cli\tester\contexts\TestEnv;
use wfw\engine\core\app\WebApp;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\UserModel;
use wfw\engine\package\users\domain\repository\UserRepository;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Client;

/**
 * Effectue les tests en simulation un utilisateur authentifié.
 * @warning le module user doit subir à chaque fois des tests fonctionnels permettant
 * de tester les fonctionnalité de login/inscription/logout/procédures contenant des mails !
 */
class UserAdminTest extends TestCase{
	/**
	 * ArticleTest constructor.
	 *
	 * @param null|string $name
	 * @param array       $data
	 * @param string      $dataName
	 */
	public function __construct(?string $name = null, array $data = [], string $dataName = ''){
		parent::__construct($name, $data, $dataName);
		require_once dirname(__DIR__,5)."/cli/tester/helpers/session_auto_logged_user.php";//load an admin user
		TestEnv::get()->init();
		TestEnv::restoreEmptyTestSqlDb();
		TestEnv::restoreModels();
	}

	public function testUserListMustBeEmpty(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/list"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => []
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertEquals(0,count($r));
	}

	public function testNonAjaxUserListMustReturn404(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/list"
				],
				"_GET" => [
					"ajax" => false,
					"csrfToken" => "falseToken"
				],
				"_POST" => []
			]
		]);
		$this->assertTrue(preg_match("#^.*Not found.*$#",ob_get_clean())?true:false);
	}

	public function testAdminRegistration(){
		$login = "A cool login :D";
		$password ="AveryLongPassword @85";
		$email = "an@email.fr";
		$type = "admin";
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/register"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"login" => $login,
					"password" => $password,
					"email" => $email,
					"type" => $type
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);

		$client = TestEnv::createMSClient();
		$client->login();
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertNotNull($res,$t);
		$this->assertEquals(1,count($list));

		/** @var User $user */
		$user = $list[0];
		/** @var User $currentUser */
		$currentUser = $_SESSION["user"];
		//Check MSServer's DTO
		$this->assertInstanceOf(User::class,$user);
		$this->assertEquals($login,(string)$user->getLogin());
		$this->assertTrue($user->getPassword()->equals($password));
		$this->assertEquals($email,(string)$user->getEmail());
		$this->assertInstanceOf(DisabledUser::class,$user->getState());
		$this->assertInstanceOf(InMemoryUserSettings::class,$user->getSettings());
		$this->assertEquals((string)$currentUser->getId(),$user->getCreator());
		$this->assertInstanceOf(Admin::class,$user->getType());

		//Test server's response
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertArrayNotHasKey("_password",$r);
		$this->assertEquals($r["_login"],$login);
		$this->assertEquals($r["_email"],$email);
		$this->assertEquals($r["_type"],"Admin");
		$this->assertEquals($r["_state"],"DisabledUser");
		$this->assertEquals($r["_creatorId"],(string)$currentUser->getId());
	}

	public function testClientRegistration(){
		$login = "Robert";
		$password ="AveryLongPassword @85";
		$email = "a_robert_email@email.fr";
		$type = "client";
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/register"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"login" => $login,
					"password" => $password,
					"email" => $email,
					"type" => $type
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$client = TestEnv::createMSClient();
		$client->login();
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertNotNull($res,$t);
		$this->assertEquals(2,count($list));

		/** @var User $user */
		$user = $list[1];
		/** @var User $currentUser */
		$currentUser = $_SESSION["user"];
		//Check MSServer's DTO
		$this->assertInstanceOf(User::class,$user);
		$this->assertEquals($login,(string)$user->getLogin());
		$this->assertTrue($user->getPassword()->equals($password));
		$this->assertEquals($email,(string)$user->getEmail());
		$this->assertInstanceOf(DisabledUser::class,$user->getState());
		$this->assertInstanceOf(InMemoryUserSettings::class,$user->getSettings());
		$this->assertEquals((string)$currentUser->getId(),$user->getCreator());
		$this->assertInstanceOf(Client::class,$user->getType());

		//Test server's response
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertArrayNotHasKey("_password",$r);
		$this->assertEquals($r["_login"],$login);
		$this->assertEquals($r["_email"],$email);
		$this->assertEquals($r["_type"],"Client");
		$this->assertEquals($r["_state"],"DisabledUser");
	}

	public function testAttemptingToAddUserWithExistingLogginShouldReturnCode201(){
		$login = "Robert";
		$password ="AveryLongPassword @85";
		$email = "a_robert_email@email.fr";
		$type = "client";
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/register"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"login" => $login,
					"password" => $password,
					"email" => $email,
					"type" => $type
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("201",$res["response"]["code"],$res["response"]["text"] ?? $t);
	}

	public function testEnableUsers(){
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$ids = [
			(string)$list[0]->getId(),
			(string)$list[1]->getId()
		];
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/enable"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => $ids
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertInternalType("array",$r);
		$this->assertCount(2,$r);
		$this->assertCount(0,array_diff($r,$ids));

		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id='$r[0],$r[1]'");
		$client->logout();
		$this->assertCount(2,$list);
		foreach($list as $u){
			$this->assertInstanceOf(EnabledUser::class,$u->getState());
		}
	}

	public function testDisableUsers(){
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$ids = [
			(string)$list[0]->getId(),
			(string)$list[1]->getId()
		];
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/disable"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => $ids
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertInternalType("array",$r);
		$this->assertCount(2,$r);
		$this->assertCount(0,array_diff($r,$ids));

		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id='$r[0],$r[1]'");
		$client->logout();
		$this->assertCount(2,$list);
		foreach($list as $u) {
			$this->assertInstanceOf(DisabledUser::class, $u->getState());
		}
	}

	public function testChangeAdminTypeToClient(){
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$id = (string)$list[0]->getId();
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/changeType"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"id" => $id,
					"type" => "client"
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("000",$res["response"]["code"],$res["response"]["text"] ?? $t);

		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User $user */
		$user = $client->query(UserModel::class,"id")[0];
		$client->logout();

		$this->assertInstanceOf(Client::class,$user->getType());
	}

	public function testChangeClientTypeToAdmin(){
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$id = (string)$list[0]->getId();
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/changeType"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"id" => $id,
					"type" => "admin"
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("000",$res["response"]["code"],$res["response"]["text"] ?? $t);

		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User $user */
		$user = $client->query(UserModel::class,"id")[0];
		$client->logout();

		$this->assertInstanceOf(Admin::class,$user->getType());
	}

	public function testChangeMail(){
		$newMail = "asuper@newmail.fr";
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$id = (string)$list[0]->getId();
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/changeMail"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"id" => $id,
					"email" => $newMail
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("000",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User $user */
		$user = $client->query(UserModel::class,"id")[0];
		$client->logout();

		$this->assertEquals($newMail,(string)$user->getEmail());
	}

	public function testResetPassword(){
		$newPassword = "A superNewPassword@90";
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User[] $list */
		$list = $client->query(UserModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertEquals(2,count($list));
		$id = (string)$list[0]->getId();
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/users/admin/resetPassword"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"id" => $id,
					"password" => $newPassword,
					"password_confirm" => $newPassword
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("000",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var User $user */
		$user = $client->query(UserModel::class,"id")[0];
		$client->logout();

		$this->assertTrue($user->getPassword()->equals($newPassword));
	}

	public function testRestoreErrorHandler(){
		$this->assertTrue(true);
		TestEnv::restoreErrorHandler();
	}
}