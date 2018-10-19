<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/06/18
 * Time: 14:53
 */
namespace wfw\tests\PHPUnit\integration\modules\contact;

use PHPUnit\Framework\TestCase;
use wfw\cli\tester\contexts\TestEnv;
use wfw\engine\core\app\WebApp;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\package\contact\command\CreateContact;
use wfw\engine\package\contact\data\model\ContactModel;
use wfw\engine\package\contact\data\model\DTO\Contact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\events\ContactedEvent;

/**
 * Teste le fonctionnement des articles.
 */
class ContactTest extends TestCase implements IDomainEventListener
{
	/** @var ContactedEvent $_e */
	private $_e;
	/**
	 * ArticleTest constructor.
	 *
	 * @param null|string $name
	 * @param array       $data
	 * @param string      $dataName
	 */
	public function __construct(?string $name = null, array $data = [], string $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		require_once CLI."/tester/helpers/session_auto_logged_user.php";
		TestEnv::get()->init();
		TestEnv::restoreEmptyTestSqlDb();
		TestEnv::restoreModels();
	}

	/**
	 * Since models and events have been reinitialized, no contact must be found.
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testListContactMustBeEmpty(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/list"
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

	public function testWritingContactIsCorrectlySetUpByMSServer(){
		$label = new ContactLabel("A title");
		$infos = new ContactInfos("A content");
		/** @var ICommandBus $bus */
		/** @var IDomainEventObserver $observer */
		$observer = TestEnv::get()->create(IDomainEventObserver::class);
		$observer->addEventListener(ContactedEvent::class,$this);
		$bus = TestEnv::get()->create(ICommandBus::class);
		$createContactCommand = new CreateContact($label,$infos);
		$bus->execute($createContactCommand);

		$client = TestEnv::createMSClient();
		$client->login();
		$list = $client->query(ContactModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();

		/** @var Contact $contact */
		$contact = $list[0];
		$this->assertInstanceOf(ContactedEvent::class,$this->_e);
		//Check the MSServer's DTO
		$this->assertInstanceOf(Contact::class,$contact);
		$this->assertEquals($label,(string)$contact->getLabel());
		$this->assertEquals($infos,$contact->getInfos());
		$this->assertEquals($this->_e->getGenerationDate(),$contact->getCreationDate());
		$this->assertFalse($contact->isRead());
		$this->assertFalse($contact->isArchived());
	}

	public function testListConactsMustContainOneContact(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/list"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertNotNull($res);
		$this->assertEquals("001",$res["response"]["code"],$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertEquals(1,count($r));
	}

	public function testListContactsInNonAjaxModeShouldNotReturnJSON(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/list"
				],
				"_GET" => [
					"ajax" => false,
					"csrfToken" => "falseToken"
				],
				"_POST" => []
			]
		]);
		$this->assertNull(json_decode(ob_get_clean()));
	}

	public function testMarkContactAsRead(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsRead"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$contact = $client->query(ContactModel::class,"id='{$contact->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($contact->isRead());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$contact->getId(),$r[0]);
	}
	public function testMarkASReadAlreadyReadContactShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $article */
		$article = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsRead"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $article->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$article = $client->query(ContactModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($article->isRead());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testMarkContactAsUnread(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsUnread"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$contact = $client->query(ContactModel::class,"id='{$contact->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($contact->isRead());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$contact->getId(),$r[0]);
	}

	public function testMarkAsUnreadAlreadyNotReadContactShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsUnread"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$contact = $client->query(ContactModel::class,"id='{$contact->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($contact->isRead());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testArchiveContact(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/archive"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$contact = $client->query(ContactModel::class,"id='{$contact->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($contact->isArchived());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$contact->getId(),$r[0]);
	}

	public function testArchiveAlreadyArchivedShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];
		$client->logout();

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/archive"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		TestEnv::restoreErrorHandler();

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testUnarchiveContact(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/unarchive"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$contact = $client->query(ContactModel::class,"id='{$contact->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($contact->isArchived());

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$contact->getId(),$r[0]);
	}

	public function testUnarchiveAlreadyUnarchivedShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact $contact */
		$contact = $client->query(ContactModel::class,"id")[0];
		$client->logout();

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/unarchive"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contact->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		TestEnv::restoreErrorHandler();
		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testMarkAsReadTwoNotReadContacts(){
		$label = new ContactLabel("A title");
		$infos = new ContactInfos("A content");
		/** @var ICommandBus $bus */
		$bus = TestEnv::get()->create(ICommandBus::class);
		$createContactCommand = new CreateContact($label,$infos);
		$bus->execute($createContactCommand);

		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact[] $contacts */
		$contacts = $client->query(ContactModel::class,"id");
		$client->logout();
		$this->assertEquals(2,count($contacts));

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsRead"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contacts[0]->getId(),(string) $contacts[1]->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		TestEnv::restoreErrorHandler();
		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(2,count($r));
		$this->assertTrue(is_integer(array_search((string)$contacts[0]->getId(),$r)));
		$this->assertTrue(is_integer(array_search((string)$contacts[1]->getId(),$r)));
	}

	public function testMarkAsUnreadTwoReadContacts(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Contact[] $contacts */
		$contacts = $client->query(ContactModel::class,"id");
		$client->logout();
		$this->assertEquals(2,count($contacts));

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/contact/markAsUnread"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $contacts[0]->getId(),(string) $contacts[1]->getId()],
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		TestEnv::restoreErrorHandler();
		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(2,count($r));
		$this->assertTrue(is_integer(array_search((string)$contacts[0]->getId(),$r)));
		$this->assertTrue(is_integer(array_search((string)$contacts[1]->getId(),$r)));
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		$this->_e = $e;
	}
}