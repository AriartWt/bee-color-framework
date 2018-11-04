<?php
namespace wfw\tests\PHPUnit\unit\modules\news\data\model\specs;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\data\model\objects\Article;
use wfw\engine\package\news\data\model\specs\AuthorIs;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Teste la validité de la spécification AuthorIs
 */
class AuthorIsTest extends TestCase
{
    public function testNoIdMatch(){
        $spec = new AuthorIs(new UUID());
        foreach($this->createArticles() as $article){
            $this->assertFalse($spec->isSatisfiedBy($article));
        }
    }

    public function testOneIdMatch(){
        $list = $this->createArticles();
        $spec = new AuthorIs($list[8]->getAuthor());
        $this->assertTrue($spec->isSatisfiedBy($list[8]));
    }

    public function testSeveralIdMatch(){
        $list = $this->createArticles();
        $spec = new AuthorIs(...[$list[0]->getAuthor(),$list[4]->getAuthor(),$list[8]->getAuthor()]);
        $this->assertTrue($spec->isSatisfiedBy($list[0]));
        $this->assertFalse($spec->isSatisfiedBy($list[1]));
        $this->assertTrue($spec->isSatisfiedBy($list[4]));
        $this->assertFalse($spec->isSatisfiedBy($list[9]));
        $this->assertTrue($spec->isSatisfiedBy($list[8]));
    }

    /**
     * @return Article[]
     * @throws \InvalidArgumentException
     */
    private function createArticles():array{
        $res =[];
        for($i=0;$i<20;$i++){
            $article = new Article(
                new UUID(),
                new Title("A title $i"),
                new VisualLink("a/$i/link"),
                new Content("Content $i"),
                new UUID(),
                microtime(true)-rand(50,50000),
                $i%2 === 0
            );
            if($i%3===0) $article->setArchived(true);
            $res[] = $article;
        }
        return $res;
    }
}