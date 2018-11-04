<?php
namespace wfw\tests\PHPUnit\unit\modules\contact\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\Contact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\errors\ArchivingFailure;
use wfw\engine\package\contact\domain\errors\MarkAsReadFailed;
use wfw\engine\package\contact\domain\errors\MarkAsUnreadFailed;
use wfw\engine\package\contact\domain\events\ArchivedEvent;
use wfw\engine\package\contact\domain\events\ContactedEvent;
use wfw\engine\package\contact\domain\events\MarkedAsReadEvent;
use wfw\engine\package\contact\domain\events\MarkedAsUnreadEvent;
use wfw\engine\package\contact\domain\events\UnarchivedEvent;

/**
 * Class ArticleTest
 *
 * @package wfw\tests\PHPUnit\unit\modules\contact\domain
 */
final class ContactTest extends TestCase
{
	public function testContactedEventIsCorrectlyGenerated(){
		$id = new UUID();
		$label = new ContactLabel("A title");
		$infos = new ContactInfos("A content");
		$contact = new Contact($id,$label,$infos);
		$events = $contact->getEventList()->toArray();
		/** @var ContactedEvent $e */
		$e = $events[0];

		$this->assertEquals(1,count($events));
		$this->assertInstanceOf(ContactedEvent::class,$e);
		$this->assertEquals($id,$e->getAggregateId());
		$this->assertEquals($label,$e->getLabel());
		$this->assertEquals($infos,$e->getInfos());

		$this->assertEquals([$id,$label,$infos],$e->getConstructorArgs());
	}

	public function testArchiveEventIsCorrectlyGenerated(){
		$contact = $this->createContact();
		$editor = (string) new UUID();
		$contact->archive($editor);
		$events = $contact->getEventList();
		/** @var ArchivedEvent $archivedEvent */

		$this->assertEquals(2,count($events));
		$archivedEvent = $events->get(1);
		$this->assertInstanceOf(ArchivedEvent::class,$archivedEvent);
		$this->assertEquals($contact->getId(),$archivedEvent->getAggregateId());
		$this->assertEquals($editor,$archivedEvent->getUser());
	}

	public function testTryingToArchiveAnAlreadyArchivedContactThrowArchivingFailedException(){
		$article = $this->createContact();
		$editor = (string) new UUID();
		$article->archive($editor);
		$this->expectException(ArchivingFailure::class);
		$article->archive($editor);
	}

	public function testTryingToUnarchiveANotArchivedContactThrowArchivingFailedException(){
		$article = $this->createContact();
		$editor = (string) new UUID();
		$this->expectException(ArchivingFailure::class);
		$article->unarchive($editor);
	}

	public function testUnarchiveEventIsCorrectlyGenerated(){
		$contact = $this->createContact();
		$editor = (string) new UUID();
		$contact->archive($editor);
		$contact->unarchive($editor);
		$events = $contact->getEventList();

		$this->assertEquals(3,count($events));
		/** @var UnarchivedEvent $unarchivedEvent */
		$unarchivedEvent = $events->get(2);
		$this->assertInstanceOf(UnarchivedEvent::class,$unarchivedEvent);
		$this->assertEquals($contact->getId(),$unarchivedEvent->getAggregateId());
		$this->assertEquals($editor,$unarchivedEvent->getUser());
	}

	public function testMarkAsReadEventIsCorrectlyGenerated(){
		$article = $this->createContact(false);
		$editor = (string) new UUID();
		$article->markAsRead($editor);
		$events = $article->getEventList();

		$this->assertEquals(2,count($events));
		/** @var MarkedAsReadEvent $markedAsReadEvent */
		$markedAsReadEvent = $events->get(1);
		$this->assertInstanceOf(MarkedAsReadEvent::class, $markedAsReadEvent);
		$this->assertEquals($article->getId(),$markedAsReadEvent->getAggregateId());
		$this->assertEquals($editor,$markedAsReadEvent->getUser());
	}

	public function testTryingToMarkAsReadAnAlreadyReadContactThrowPutMarkAsReadFailedException(){
		$contact = $this->createContact(true);
		$editor = (string) new UUID();
		$this->expectException(MarkAsReadFailed::class);
		$contact->markAsRead($editor);
	}

	public function testMarkedAsUnreadEventIsCorrectlyGenerated(){
		$contact = $this->createContact(true);
		$editor = (string) new UUID();
		$contact->markAsUnread($editor);
		$events = $contact->getEventList();

		$this->assertEquals(3,count($events));
		/** @var MarkedAsUnreadEvent $markedAsUnreadEvent */
		$markedAsUnreadEvent = $events->get(2);
		$this->assertInstanceOf(MarkedAsUnreadEvent::class, $markedAsUnreadEvent);
		$this->assertEquals($contact->getId(),$markedAsUnreadEvent->getAggregateId());
		$this->assertEquals($editor,$markedAsUnreadEvent->getUser());
	}

	public function testTryingToMarkAsUnreadAnAlreadyNotReadContactThrowPutOfflineFailedException(){
		$article = $this->createContact();
		$editor = (string) new UUID();
		$this->expectException(MarkAsUnreadFailed::class);
		$article->markAsUnread($editor);
	}

	/**
	 * @param bool $read
	 * @return Contact
	 * @throws \InvalidArgumentException
	 */
	private function createContact(bool $read = false):Contact{
		$contact = new Contact(
			new UUID(),
			new ContactLabel("A title"),
			new ContactInfos("a/link")
		);
		if($read) $contact->markAsRead(new UUID());
		return $contact;
	}
}