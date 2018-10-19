<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 16:54
 */

namespace wfw\tests\PHPUnit\unit\modules\news\data\model;

use PHPUnit\Framework\TestCase;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticParser;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSearcher;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSolver;
use wfw\engine\core\domain\events\DomainEvent;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\data\model\ArticleModel;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\news\data\model\specs\AuthorIs;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\events\ArchivedEvent;
use wfw\engine\package\news\domain\events\ArticleEvent;
use wfw\engine\package\news\domain\events\ArticleWrittenEvent;
use wfw\engine\package\news\domain\events\ContentEditedEvent;
use wfw\engine\package\news\domain\events\PutOfflineEvent;
use wfw\engine\package\news\domain\events\PutOnlineEvent;
use wfw\engine\package\news\domain\events\TitleEditedEvent;
use wfw\engine\package\news\domain\events\UnarchivedEvent;
use wfw\engine\package\news\domain\events\VisualLinkEditedEvent;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\domain\states\EnabledUser;

/**
 * Teste le fonctionnement de la classe ArticleModel
 */
final class ArticleModelTest extends TestCase
{
	public function testArticleWrittenEventIsCorrectlyHandled(){
		$createEvent = $this->createArticleWrittenEvent();
		$model = $this->createModel([$createEvent]);
		/** @var Article $article */
		$article = $model->find("id='{$createEvent->getAggregateId()}'")[0];
		$this->assertInstanceOf(Article::class,$article);
		$this->assertEquals((string)$createEvent->getTitle(),(string)$article->getTitle());
		$this->assertEquals((string)$createEvent->getContent(),(string)$article->getContent());
		$this->assertEquals((string)$createEvent->getVisualLink(),(string)$article->getVisualLink());
		$this->assertEquals((string)$createEvent->getAuthor(),(string)$article->getAuthor());
		$this->assertEquals((string)$createEvent->getAggregateId(),(string)$article->getId());
		$this->assertFalse($article->isOnline());
		$this->assertFalse($article->isArchived());
		$this->assertEmpty($article->getEditions());

		$this->assertEquals(3,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(3,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(0,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testArticleTitleEditedEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$titleEditedEvent = $this->createTitleEditedEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$editions = $article->getEditions();
		$this->assertEquals((string)$titleEditedEvent->getTitle(),(string)$article->getTitle());
		$this->assertEquals(1,count($editions));
	}

	public function testArticleContentEditedEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$contentEditedEvent = $this->createContentEditedEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$editions = $article->getEditions();
		$this->assertEquals((string)$contentEditedEvent->getContent(),(string)$article->getContent());
		$this->assertEquals(1,count($editions));
	}

	public function testArticleVisualEditedEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$visualEditedEvent = $this->createVisualLinkEditedEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$editions = $article->getEditions();
		$this->assertEquals((string)$visualEditedEvent->getVisualLink(),(string)$article->getVisualLink());
		$this->assertEquals(1,count($editions));
	}

	public function testArchiveArticleEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$this->createArchivedEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertTrue($article->isArchived());
		$editions = $article->getEditions();
		$this->assertEquals(1,count($editions));

		$this->assertEquals(3,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(2,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(1,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testUnarchiveArticleEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$this->createArchivedEvent($createdEvent->getAggregateId()),
			$this->createUnarchivedEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertFalse($article->isArchived());
		$editions = $article->getEditions();
		$this->assertEquals(2,count($editions));

		$this->assertEquals(3,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(3,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(0,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testPutOnlineArticleEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(),
			$this->createPutOnlineEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertTrue($article->isOnline());
		$editions = $article->getEditions();
		$this->assertEquals(1,count($editions));

		$this->assertEquals(2,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(3,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(1,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(0,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testPutOfflineArticleEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(true),
			$this->createPutOfflineEvent($createdEvent->getAggregateId())
		]);
		/** @var Article $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertFalse($article->isOnline());
		$editions = $article->getEditions();
		$this->assertEquals(1,count($editions));
		$this->assertEquals(3,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(3,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(0,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testArticleIsCorrectlyRemovedAfterUserRemovedEventHandling(){
		$model = $this->createModel([
			$createdEvent = $this->createArticleWrittenEvent(true)
		]);
		$this->assertEquals(1,count($model->find((string) new AuthorIs($createdEvent->getAuthor()))));
		$model->recieveEvent(
			new UserRemovedEvent(new UUID(UUID::V6, $createdEvent->getAuthor()), new EnabledUser())
		);
		$this->assertEquals(0,count($model->find((string) new AuthorIs($createdEvent->getAuthor()))));

		$this->assertEquals(2,count($model->find(ArticleModel::OFFLINE)));
		$this->assertEquals(2,count($model->find(ArticleModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ArticleModel::ONLINE)));
		$this->assertEquals(0,count($model->find(ArticleModel::ARCHIVED)));
	}

	public function testOtherDomainEventIsIgnored(){
		$model = $this->createModel([],true);
		$this->assertEquals(0,count($model->find("id")));
		$model->recieveEvent(new class(new UUID()) extends DomainEvent{});
		$this->assertEquals(0,count($model->find("id")));
	}

	public function testEventListened(){
		$listened = $this->createModel()->listenEvents();
		$this->assertEquals(2,count($listened));
		$this->assertTrue(is_int(array_search(ArticleEvent::class,$listened)));
		$this->assertTrue(is_int(array_search(UserRemovedEvent::class,$listened)));
	}

	/**
	 * Crée un model avec 2 articles
	 *
	 * @param array $events Liste d'événements à appliquer
	 * @param bool  $empty
	 * @return ArticleModel Crée un nouveau model
	 * @throws \InvalidArgumentException
	 */
	private function createModel(array $events = [],bool $empty = false):ArticleModel{
		$model =  new ArticleModel(
			new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser()))
		);
		if(!$empty){
			$events = array_merge(
				[$this->createArticleWrittenEvent()],
				$events,
				[$this->createArticleWrittenEvent()]
			);
		}
		foreach($events as $e){$model->recieveEvent($e);}
		return $model;
	}

	/**
	 * @param bool $online
	 * @return ArticleWrittenEvent
	 * @throws \InvalidArgumentException
	 */
	private function createArticleWrittenEvent(bool $online=false):ArticleWrittenEvent{
		return new ArticleWrittenEvent(
			new UUID(UUID::V4),
			new Title("Title"),
			new VisualLink("test"),
			new Content("content"),
			new UUID(UUID::V6),
			$online
		);
	}

	/**
	 * @param UUID $articleId
	 * @return TitleEditedEvent
	 * @throws \InvalidArgumentException
	 */
	private function createTitleEditedEvent(UUID $articleId){
		return new TitleEditedEvent(
			$articleId,
			new Title("New title"),
			new UUID(UUID::V6)
		);
	}

	/**
	 * @param UUID $articleId
	 * @return ContentEditedEvent
	 * @throws \InvalidArgumentException
	 */
	private function createContentEditedEvent(UUID $articleId){
		return new ContentEditedEvent(
			$articleId,
			new Content("New content"),
			new UUID(UUID::V6)
		);
	}

	/**
	 * @param UUID $articleId
	 * @return VisualLinkEditedEvent
	 * @throws \InvalidArgumentException
	 */
	private function createVisualLinkEditedEvent(UUID $articleId){
		return new VisualLinkEditedEvent(
			$articleId,
			new VisualLink("new/link"),
			new UUID(UUID::V6)
		);
	}

	/**
	 * @param UUID $articleId
	 * @return ArchivedEvent
	 */
	private function createArchivedEvent(UUID $articleId):ArchivedEvent{
		return new ArchivedEvent($articleId,new UUID());
	}

	/**
	 * @param UUID $articleId
	 * @return UnarchivedEvent
	 */
	private function createUnarchivedEvent(UUID $articleId):UnarchivedEvent{
		return new UnarchivedEvent($articleId,new UUID());
	}

	/**
	 * @param UUID $articleId
	 * @return PutOnlineEvent
	 */
	private function createPutOnlineEvent(UUID $articleId):PutOnlineEvent{
		return new PutOnlineEvent($articleId, new UUID());
	}

	/**
	 * @param UUID $articleId
	 * @return PutOfflineEvent
	 */
	private function createPutOfflineEvent(UUID $articleId):PutOfflineEvent{
		return new PutOfflineEvent($articleId, new UUID());
	}
}