<?php

namespace wfw\tests\PHPUnit\integration\modules\news;

use PHPUnit\Framework\TestCase;
use wfw\cli\tester\contexts\TestEnv;
use wfw\engine\core\app\WebApp;
use wfw\engine\package\news\data\model\ArticleModel;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\users\data\model\DTO\User;

/**
 * Teste le fonctionnement des articles.
 */
class ArticleTest extends TestCase
{
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
	 * Since models and events have been reinitialized, no article must be found.
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testListArticlesMustBeEmpty(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/list"
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

	public function testWritingArticleIsCorrectlySetUpByMSServer(){
		ob_start();
		$title = "A title";
		$content = "A content";
		$visual = "/a/visual/link.png";

		$start = microtime(true);
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/create"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"title" => $title,
					"content" => $content,
					"visual" => $visual
				]
			]
		]);
		$end = microtime(true);
		$res = json_decode($t = ob_get_clean(),true);

		$client = TestEnv::createMSClient();
		$client->login();
		$list = $client->query(ArticleModel::class,"id");
		$client->logout();

		TestEnv::restoreErrorHandler();
		$this->assertNotNull($res,$t);
		$this->assertEquals(1,count($list));

		/** @var Article $article */
		$article = $list[0];
		/** @var User $currentUser */
		$currentUser = $_SESSION["user"];
		//Check the MSServer's DTO
		$this->assertInstanceOf(Article::class,$article);
		$this->assertEquals($title,(string)$article->getTitle());
		$this->assertEquals($content,$article->getContent());
		$this->assertEquals($visual,$visual);
		$this->assertFalse($article->isOnline());
		$this->assertFalse($article->isArchived());
		$this->assertEquals($currentUser->getId(),$article->getAuthor());
		$this->assertLessThan($end,$article->getCreationDate());
		$this->assertGreaterThan($start,$article->getCreationDate());

		//Check the server's response to the client
		$this->assertEquals($res["response"]["code"],"001",$t);
		$r = $res["response"]["text"];
		$this->assertInternalType("string",$r);
		$r = json_decode($r,true);
		$this->assertNotNull($r);
		$this->assertEquals((string) $article->getId(),$r["_id"]);
		$this->assertEquals($title,$r["_title"]);
		$this->assertEquals($content,$r["_content"]);
		$this->assertEquals($visual,$r["_link"]);
		$this->assertFalse($r["_online"]);
		$this->assertEquals($currentUser->getId(),$r["_author"]);
		$this->assertInternalType("array",$r["_editions"]);
		$this->assertEmpty($r["_editions"]);
		$this->assertEquals($article->getCreationDate(),$r["_creationDate"]);
	}

	public function testListArticlesMustContainOneArticle(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/list"
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

	public function testListArticlesInNonAjaxModeShouldNotReturnJSON(){
		ob_start();
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/list"
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

	public function testArticleFullEdition(){
		ob_start();
		$title = "A new title";
		$content = "A new content";
		$visual = "/a/new_visual/link.png";

		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/edit"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"article_id" => (string) $article->getId(),
					"title" => $title,
					"content" => $content,
					"visual" => $visual
				]
			]
		]);

		$res = json_decode($t = ob_get_clean(),true);
		$article = $client->query(ArticleModel::class,"id")[0];
		$client->logout();

		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertEquals($title,(string) $article->getTitle());
		$this->assertEquals($content,(string) $article->getContent());
		$this->assertEquals($visual,(string) $article->getVisualLink());
		$this->assertEquals(1,count($article->getEditions()));

		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		//var_dump($r);
		$this->assertNotNull($r);
		$this->assertEquals((string)$article->getTitle(),$r["_title"]);
		$this->assertEquals((string)$article->getContent(),$r["_content"]);
		$this->assertEquals((string)$article->getVisualLink(), $r["_link"]);
		$this->assertEquals(1,count($r["_editions"]));
	}

	public function testNoEditionShouldReturnCode500(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];
		$client->logout();

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/edit"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"article_id" => (string) $article->getId()
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		TestEnv::restoreErrorHandler();
		$this->assertEquals($res["response"]["code"],"500",$res["response"]["text"] ?? $t);
	}

	public function testEditTitleOnly(){
		ob_start();
		$title = "A super new title :D";
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/edit"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"article_id" => (string) $article->getId(),
					"title" => $title
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertEquals($title,(string)$article->getTitle());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals($title,$r["_title"]);
	}

	public function testEditContentOnly(){
		ob_start();
		$content = "A super new content <p> With a paragraph </p> :D";
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/edit"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"article_id" => (string) $article->getId(),
					"content" => $content
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertEquals($content,(string)$article->getContent());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals($content,$r["_content"]);
	}

	public function testEditVisualLinkOnly(){
		ob_start();
		$link = "/a/super/super/new/link.jpeg";
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/edit"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"article_id" => (string) $article->getId(),
					"visual" => $link
				]
			]
		]);
		$res = json_decode($t = ob_get_clean(),true);

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertEquals($link,(string)$article->getVisualLink());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals($link,$r["_link"]);
	}

	public function testPutOnlineArticle(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOnline"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($article->isOnline());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$article->getId(),$r[0]);
	}
	public function testPutOnlineAlreadyOnlineArticleShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOnline"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($article->isOnline());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testPutOfflineArticle(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOffline"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($article->isOnline());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$article->getId(),$r[0]);
	}

	public function testPutOfflineAlreadyOfflineArticleShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOffline"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($article->isOnline());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testArchiveArticle(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/archive"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertTrue($article->isArchived());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$article->getId(),$r[0]);
	}

	public function testArchiveAlreadyArchivedShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];
		$client->logout();

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/archive"
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
		TestEnv::restoreErrorHandler();

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testUnarchiveArticle(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/unarchive"
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

		$article = $client->query(ArticleModel::class,"id='{$article->getId()}'")[0];
		$client->logout();
		TestEnv::restoreErrorHandler();
		//Test MSServer's DTO
		$this->assertFalse($article->isArchived());
		$this->assertEquals(1,count($article->getEditions()));

		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(1,count($r));
		$this->assertEquals((string)$article->getId(),$r[0]);
	}

	public function testUnarchiveAlreadyUnarchivedShouldReturnEmptyArray(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article $article */
		$article = $client->query(ArticleModel::class,"id")[0];
		$client->logout();

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/unarchive"
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
		TestEnv::restoreErrorHandler();
		//Test server's response
		$this->assertEquals($res["response"]["code"],"001",$res["response"]["text"] ?? $t);
		$r = json_decode($res["response"]["text"],true);
		$this->assertNotNull($r);
		$this->assertEquals(0,count($r));
	}

	public function testPutOnlineTwoOfflineArticles(){
		$this->createArticle();

		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article[] $articles */
		$articles = $client->query(ArticleModel::class,"id");
		$client->logout();
		$this->assertEquals(2,count($articles));

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOnline"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $articles[0]->getId(),(string) $articles[1]->getId()],
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
		$this->assertTrue(is_integer(array_search((string)$articles[0]->getId(),$r)));
		$this->assertTrue(is_integer(array_search((string)$articles[1]->getId(),$r)));
	}

	public function testPutOfflineTwoOnlineArticles(){
		ob_start();
		$client = TestEnv::createMSClient();
		$client->login();
		/** @var Article[] $articles */
		$articles = $client->query(ArticleModel::class,"id");
		$client->logout();
		$this->assertEquals(2,count($articles));

		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/putOffline"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"ids" => [(string) $articles[0]->getId(),(string) $articles[1]->getId()],
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
		$this->assertTrue(is_integer(array_search((string)$articles[0]->getId(),$r)));
		$this->assertTrue(is_integer(array_search((string)$articles[1]->getId(),$r)));
	}

	/**
	 * @return string
	 */
	private function createArticle():string{
		ob_start();
		$title = "A title";
		$content = "A content";
		$visual = "/a/visual/link.png";
		TestEnv::get()->create(WebApp::class,[
			"globals" => [
				"_SERVER" => [
					"REQUEST_METHOD" => "POST",
					"PATH_INFO" => "/news/create"
				],
				"_GET" => [
					"ajax" => true,
					"csrfToken" => "falseToken"
				],
				"_POST" => [
					"title" => $title,
					"content" => $content,
					"visual" => $visual
				]
			]
		]);
		return ob_end_clean();
	}
}