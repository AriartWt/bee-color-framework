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
use wfw\engine\package\contact\data\model\ContactModel;
use wfw\engine\package\contact\data\model\DTO\Contact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\events\ContactedEvent;
use wfw\engine\package\contact\domain\events\ArchivedEvent;
use wfw\engine\package\contact\domain\events\ContactEvent;
use wfw\engine\package\contact\domain\events\MarkedAsReadEvent;
use wfw\engine\package\contact\domain\events\MarkedAsUnreadEvent;
use wfw\engine\package\contact\domain\events\UnarchivedEvent;

/**
 * Teste le fonctionnement de la classe ArticleModel
 */
final class ContactModelTest extends TestCase
{
	public function testContactedEventIsCorrectlyHandled(){
		$createEvent = $this->createContactedEvent();
		$model = $this->createModel([$createEvent]);
		/** @var Contact $contact */
		$contact = $model->find("id='{$createEvent->getAggregateId()}'")[0];
		$this->assertInstanceOf(Contact::class,$contact);
		$this->assertEquals((string)$createEvent->getLabel(),(string)$contact->getLabel());
		$this->assertEquals((string)$createEvent->getInfos(),(string)$contact->getInfos());
		$this->assertEquals((string)$createEvent->getAggregateId(),(string)$contact->getId());
		$this->assertFalse($contact->isRead());
		$this->assertFalse($contact->isArchived());

		$this->assertEquals(3,count($model->find(ContactModel::NOT_READ)));
		$this->assertEquals(3,count($model->find(ContactModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ContactModel::READ)));
		$this->assertEquals(0,count($model->find(ContactModel::ARCHIVED)));
	}

	public function testArchiveContactEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createContactedEvent(),
			$this->createArchivedEvent($createdEvent->getAggregateId())
		]);
		/** @var Contact $contact */
		$contact = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertTrue($contact->isArchived());

		$this->assertEquals(3,count($model->find(ContactModel::NOT_READ)));
		$this->assertEquals(2,count($model->find(ContactModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ContactModel::READ)));
		$this->assertEquals(1,count($model->find(ContactModel::ARCHIVED)));
	}

	public function testUnarchiveContactEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createContactedEvent(),
			$this->createArchivedEvent($createdEvent->getAggregateId()),
			$this->createUnarchivedEvent($createdEvent->getAggregateId())
		]);
		/** @var Contact $contact */
		$contact = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertFalse($contact->isArchived());

		$this->assertEquals(3,count($model->find(ContactModel::NOT_READ)));
		$this->assertEquals(3,count($model->find(ContactModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ContactModel::READ)));
		$this->assertEquals(0,count($model->find(ContactModel::ARCHIVED)));
	}

	public function testMarkedAsReadContactEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createContactedEvent(),
			$this->createMarkedAsReadEvent($createdEvent->getAggregateId())
		]);
		/** @var Contact $contact */
		$contact = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertTrue($contact->isRead());

		$this->assertEquals(2,count($model->find(ContactModel::NOT_READ)));
		$this->assertEquals(3,count($model->find(ContactModel::NOT_ARCHIVED)));
		$this->assertEquals(1,count($model->find(ContactModel::READ)));
		$this->assertEquals(0,count($model->find(ContactModel::ARCHIVED)));
	}

	public function testMarkedAsUnreadEventIsCorrectlyHandled(){
		$model = $this->createModel([
			$createdEvent = $this->createContactedEvent(),
			$this->createMarkedAsReadEvent($createdEvent->getAggregateId()),
			$this->createMarkedAsUnreadEvent($createdEvent->getAggregateId())
		]);
		/** @var Contact $article */
		$article = $model->find("id='{$createdEvent->getAggregateId()}'")[0];
		$this->assertFalse($article->isRead());

		$this->assertEquals(3,count($model->find(ContactModel::NOT_READ)));
		$this->assertEquals(3,count($model->find(ContactModel::NOT_ARCHIVED)));
		$this->assertEquals(0,count($model->find(ContactModel::READ)));
		$this->assertEquals(0,count($model->find(ContactModel::ARCHIVED)));
	}

	public function testOtherDomainEventIsIgnored(){
		$model = $this->createModel([],true);
		$this->assertEquals(0,count($model->find("id")));
		$model->recieveEvent(new class(new UUID()) extends DomainEvent{});
		$this->assertEquals(0,count($model->find("id")));
	}

	public function testEventListened(){
		$listened = $this->createModel()->listenEvents();
		$this->assertEquals(1,count($listened));
		$this->assertTrue(is_int(array_search(ContactEvent::class,$listened)));
	}

	/**
	 * Crée un model avec 2 articles
	 *
	 * @param array $events Liste d'événements à appliquer
	 * @param bool  $empty
	 * @return ContactModel Crée un nouveau model
	 * @throws \InvalidArgumentException
	 */
	private function createModel(array $events = [],bool $empty = false):ContactModel{
		$model =  new ContactModel(
			new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser()))
		);
		if(!$empty){
			$events = array_merge(
				[$this->createContactedEvent()],
				$events,
				[$this->createContactedEvent()]
			);
		}
		foreach($events as $e){$model->recieveEvent($e);}
		return $model;
	}

	/**
	 * @return ContactedEvent
	 * @throws \InvalidArgumentException
	 */
	private function createContactedEvent():ContactedEvent{
		return new ContactedEvent(
			new UUID(UUID::V4),
			new ContactLabel("label"),
			new ContactInfos("infos")
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
	 * @return MarkedAsReadEvent
	 */
	private function createMarkedAsReadEvent(UUID $articleId):MarkedAsReadEvent{
		return new MarkedAsReadEvent($articleId, new UUID());
	}

	/**
	 * @param UUID $articleId
	 * @return MarkedAsUnreadEvent
	 */
	private function createMarkedAsUnreadEvent(UUID $articleId):MarkedAsUnreadEvent{
		return new MarkedAsUnreadEvent($articleId, new UUID());
	}
}