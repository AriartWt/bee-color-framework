<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 19:12
 */

namespace wfw\tests\PHPUnit\unit\modules\news\data\model\objects;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\data\model\objects\Article;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Test du fonctionnement de la classe ModelObject Article
 */
final class ArticleTest extends TestCase
{
    public function testOfflineArticleConstruction(){
        $id = new UUID();
        $date = microtime(true);
        $authorId = new UUID();
        $title = new Title("A title");
        $content = new Content("Content");
        $visualLink = new VisualLink("a/link");
        $article = new Article(
            $id,
            $title,
            $visualLink,
            $content,
            $authorId,
            $date,
            false
        );
        $this->assertEquals($id,$article->getId());
        $this->assertEquals($date,$article->getCreationDate());
        $this->assertEquals((string)$authorId,$article->getAuthor());
        $this->assertEquals($title,$article->getTitle());
        $this->assertEquals($content,$article->getContent());
        $this->assertEquals($visualLink,$article->getVisualLink());
        $this->assertInternalType("array",$article->getEditions());
        $this->assertFalse($article->isOnline());
        $this->assertFalse($article->isArchived());
        $this->assertEquals(0,count($article->getEditions()));
    }
    public function testOnlineArticleConstruction(){
        $article = $this->createArticle(true);
        $this->assertTrue($article->isOnline());
    }
    public function testArticleEditedMethod(){
        $article = $this->createArticle();
        $id = new UUID();
        $date = microtime(true);
        $type = Article::TITLE;
        $article->edited($id,$date,$type);
        $editArray = $article->getEditions();
        $this->assertInternalType("array",$editArray);
        $this->assertEquals(1,count($editArray));
        $editRow = $editArray[0];
        $this->assertArrayHasKey("user",$editRow);
        $this->assertArrayHasKey("actions",$editRow);
        $this->assertArrayHasKey("date",$editRow);
        $this->assertEquals((string)$id,$editRow["user"]);
        $this->assertEquals($date,$editRow["date"]);
        $this->assertEquals($type,$editRow["actions"][0]);
    }

    public function testArticleEditedTwoTimesBySameUserInSameSecond(){
        $article = $this->createArticle();
        $id = new UUID();
        $date = microtime(true);
        $article->edited($id,$date,Article::TITLE);
        $article->edited($id,$date+0.5,Article::CONTENT);
        $this->assertEquals(1,count($article->getEditions()));
        $editRow = $article->getEditions()[0];

        $this->assertEquals((string)$id,$editRow["user"]);
        $this->assertEquals($date+0.5,$editRow["date"]);
        $this->assertEquals(2,count($editRow["actions"]));
        $this->assertEquals(Article::TITLE,$editRow["actions"][0]);
        $this->assertEquals(Article::CONTENT,$editRow["actions"][1]);
    }

    public function testArticleEditedTwoTimesByTwoDifferentUsers(){
        $article = $this->createArticle();
        $id1 = (string) new UUID();
        $date1 = microtime(true);
        $id2 = (string) new UUID();
        $date2 =  microtime(true) + 50;
        $article->edited($id1,$date1,Article::TITLE);
        $article->edited($id2,$date2,Article::TITLE);

        $this->assertEquals(2,count($article->getEditions()));
        $row1 = $article->getEditions()[0];
        $row2 = $article->getEditions()[1];
        $this->assertEquals($id1,$row1["user"]);
        $this->assertEquals($id2,$row2["user"]);

        $this->assertEquals($date1,$row1["date"]);
        $this->assertEquals($date2,$row2["date"]);

        $this->assertEquals(1,count($row1["actions"]));
        $this->assertEquals(1,count($row2["actions"]));

        $this->assertEquals(Article::TITLE,$row1["actions"][0]);
        $this->assertEquals(Article::TITLE,$row2["actions"][0]);
    }

    public function testArticleEditedTwoTimesByTwoDifferentUsersInSameSecond(){
        $article = $this->createArticle();
        $id1 = (string) new UUID();
        $date1 = microtime(true);
        $id2 = (string) new UUID();
        $date2 =  microtime(true)+0.5;
        $article->edited($id1,$date1,Article::TITLE);
        $article->edited($id2,$date2,Article::TITLE);

        $this->assertEquals(2,count($article->getEditions()));
        $row1 = $article->getEditions()[0];
        $row2 = $article->getEditions()[1];
        $this->assertEquals($id1,$row1["user"]);
        $this->assertEquals($id2,$row2["user"]);

        $this->assertEquals($date1,$row1["date"]);
        $this->assertEquals($date2,$row2["date"]);

        $this->assertEquals(1,count($row1["actions"]));
        $this->assertEquals(1,count($row2["actions"]));

        $this->assertEquals(Article::TITLE,$row1["actions"][0]);
        $this->assertEquals(Article::TITLE,$row2["actions"][0]);
    }

    public function testToDtoMethod(){
        $article = $this->createArticle(true);
        /** @var \wfw\engine\package\news\data\model\DTO\Article $dto */
        $dto = $article->toDTO();

        $this->assertEquals($article->getId(),$dto->getId());
        $this->assertEquals($article->getTitle(),$dto->getTitle());
        $this->assertEquals($article->getContent(),$dto->getContent());
        $this->assertEquals($article->getVisualLink(),$dto->getVisualLink());
        $this->assertEquals($article->getAuthor(), $dto->getAuthor());
        $this->assertEquals($article->getCreationDate(),$dto->getCreationDate());
        $this->assertEquals($article->getEditions(),$dto->getEditions());
        $this->assertEquals($article->isOnline(),$dto->isOnline());
        $this->assertEquals($article->isArchived(),$dto->isArchived());
    }

    private function createArticle(bool $online = false):Article{
        return new Article(
            new UUID(),
            new Title("A title"),
            new VisualLink("a/link"),
            new Content("A content"),
            new UUID(),
            microtime(true),
            $online
        );
    }
}