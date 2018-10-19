<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 01/06/18
 * Time: 09:36
 */

namespace wfw\tests\PHPUnit\unit\modules\news\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Article;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\errors\ArchivingFailed;
use wfw\engine\package\news\domain\errors\PutOfflineFailed;
use wfw\engine\package\news\domain\errors\PutOnlineFailed;
use wfw\engine\package\news\domain\events\ArchivedEvent;
use wfw\engine\package\news\domain\events\ArticleWrittenEvent;
use wfw\engine\package\news\domain\events\ContentEditedEvent;
use wfw\engine\package\news\domain\events\PutOfflineEvent;
use wfw\engine\package\news\domain\events\PutOnlineEvent;
use wfw\engine\package\news\domain\events\TitleEditedEvent;
use wfw\engine\package\news\domain\events\UnarchivedEvent;
use wfw\engine\package\news\domain\events\VisualLinkEditedEvent;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Class ArticleTest
 *
 * @package wfw\tests\PHPUnit\unit\modules\news\domain
 */
final class ArticleTest extends TestCase
{
    public function testArticleWrittenEventIsCorrectlyGenerated(){
        $id = new UUID(); $authorId = new UUID();
        $title = new Title("A title"); $link = new VisualLink("a/link");
        $content = new Content("A content");
        $article = new Article($id,$title,$link,$content,$authorId,true);
        $events = $article->getEventList()->toArray();
        /** @var ArticleWrittenEvent $e */
        $e = $events[0];

        $this->assertEquals(1,count($events));
        $this->assertInstanceOf(ArticleWrittenEvent::class,$e);
        $this->assertEquals($id,$e->getAggregateId());
        $this->assertEquals($title,$e->getTitle());
        $this->assertEquals($content,$e->getContent());
        $this->assertEquals($link,$e->getVisualLink());
        $this->assertEquals($authorId,$e->getAuthor());
        $this->assertTrue($e->isOnline());

        $this->assertEquals([$id,$title,$link,$content,$authorId,true],$e->getConstructorArgs());
    }

    public function testEditArticleTitleEventIsCorrectlyGenerated(){
        $article = $this->createArticle();
        $newTitle = new Title("A new title");
        $editor = (string) new UUID();
        $article->editTitle($newTitle,$editor);
        $events = $article->getEventList();
        /** @var TitleEditedEvent $titleEvent */

        $this->assertEquals(2,count($events));
        $titleEvent = $events->get(1);
        $this->assertInstanceOf(TitleEditedEvent::class,$titleEvent);
        $this->assertEquals($article->getId(),$titleEvent->getAggregateId());
        $this->assertEquals($newTitle,$titleEvent->getTitle());
        $this->assertEquals($editor,$titleEvent->getEditorId());
    }

    public function testEditArticleVisualLinkEventIsCorrectlyGenerated(){
        $article = $this->createArticle();
        $link = new VisualLink("A/new/link");
        $editor = (string) new UUID();
        $article->editVisual($link,$editor);
        $events = $article->getEventList();
        /** @var VisualLinkEditedEvent $visual */

        $this->assertEquals(2,count($events));
        $visual = $events->get(1);
        $this->assertInstanceOf(VisualLinkEditedEvent::class,$visual);
        $this->assertEquals($article->getId(),$visual->getAggregateId());
        $this->assertEquals($link,$visual->getVisualLink());
        $this->assertEquals($editor,$visual->getEditorId());
    }

    public function testEditArticleContentEventIsCorrectlyGenerated(){
        $article = $this->createArticle();
        $content = new Content("A new Content");
        $editor = (string) new UUID();
        $article->editContent($content,$editor);
        $events = $article->getEventList();
        /** @var ContentEditedEvent $contentEvent */

        $this->assertEquals(2,count($events));
        $contentEvent = $events->get(1);
        $this->assertInstanceOf(ContentEditedEvent::class,$contentEvent);
        $this->assertEquals($article->getId(),$contentEvent->getAggregateId());
        $this->assertEquals($content,$contentEvent->getContent());
        $this->assertEquals($editor,$contentEvent->getEditorId());
    }

    public function testArchiveEventIsCorrectlyGenerated(){
        $article = $this->createArticle();
        $editor = (string) new UUID();
        $article->archive($editor);
        $events = $article->getEventList();
        /** @var ArchivedEvent $archivedEvent */

        $this->assertEquals(2,count($events));
        $archivedEvent = $events->get(1);
        $this->assertInstanceOf(ArchivedEvent::class,$archivedEvent);
        $this->assertEquals($article->getId(),$archivedEvent->getAggregateId());
        $this->assertEquals($editor,$archivedEvent->getArchiver());
    }

    public function testTryingToArchiveAnAlreadyArchivedArticleThrowArchivingFailedException(){
        $article = $this->createArticle();
        $editor = (string) new UUID();
        $article->archive($editor);
        $this->expectException(ArchivingFailed::class);
        $article->archive($editor);
    }

    public function testTryingToUnarchiveANotArchivedArticleThrowArchivingFailedException(){
        $article = $this->createArticle();
        $editor = (string) new UUID();
        $this->expectException(ArchivingFailed::class);
        $article->unarchive($editor);
    }

    public function testUnarchiveEventIsCorrectlyGenerated(){
        $article = $this->createArticle();
        $editor = (string) new UUID();
        $article->archive($editor);
        $article->unarchive($editor);
        $events = $article->getEventList();

        $this->assertEquals(3,count($events));
        /** @var UnarchivedEvent $unarchivedEvent */
        $unarchivedEvent = $events->get(2);
        $this->assertInstanceOf(UnarchivedEvent::class,$unarchivedEvent);
        $this->assertEquals($article->getId(),$unarchivedEvent->getAggregateId());
        $this->assertEquals($editor,$unarchivedEvent->getUnarchiver());
    }

    public function testPutOnlineEventIsCorrectlyGenerated(){
        $article = $this->createArticle(false);
        $editor = (string) new UUID();
        $article->putOnline($editor);
        $events = $article->getEventList();

        $this->assertEquals(2,count($events));
        /** @var PutOnlineEvent $putOnlineEvent */
        $putOnlineEvent = $events->get(1);
        $this->assertInstanceOf(PutOnlineEvent::class, $putOnlineEvent);
        $this->assertEquals($article->getId(),$putOnlineEvent->getAggregateId());
        $this->assertEquals($editor,$putOnlineEvent->getUserId());
    }

    public function testTryingToPutOnlineAnAlreadyonlineArticleThrowPutOnlineFailedException(){
        $article = $this->createArticle();
        $editor = (string) new UUID();
        $this->expectException(PutOnlineFailed::class);
        $article->putOnline($editor);
    }

    public function testPutOfflineEventIsCorrectlyGenerated(){
        $article = $this->createArticle(true);
        $editor = (string) new UUID();
        $article->putOffline($editor);
        $events = $article->getEventList();

        $this->assertEquals(2,count($events));
        /** @var PutOnlineEvent $putOfflineEvent */
        $putOfflineEvent = $events->get(1);
        $this->assertInstanceOf(PutOfflineEvent::class, $putOfflineEvent);
        $this->assertEquals($article->getId(),$putOfflineEvent->getAggregateId());
        $this->assertEquals($editor,$putOfflineEvent->getUserId());
    }

    public function testTryingToPutOfflineAnAlreadyOfflineArticleThrowPutOfflineFailedException(){
        $article = $this->createArticle(false);
        $editor = (string) new UUID();
        $this->expectException(PutOfflineFailed::class);
        $article->putOffline($editor);
    }

    /**
     * @param bool $online
     * @return Article
     * @throws \InvalidArgumentException
     */
    private function createArticle(bool $online = true):Article{
        return new Article(
            new UUID(),
            new Title("A title"),
            new VisualLink("a/link"),
            new Content("A content"),
            new UUID(),
            $online
        );
    }
}