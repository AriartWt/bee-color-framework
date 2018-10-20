<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 22:42
 */

namespace wfw\tests\PHPUnit\unit\modules\news\data\model\specs;


use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\data\model\objects\Article;
use wfw\engine\package\news\data\model\specs\IsArchived;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Teste la spec IsArchived
 */
class IsArchivedTest extends TestCase
{
    public function testMatchAllArchivedArticles(){
        $list = $this->createArticles();
        $spec = new IsArchived();
        foreach($list as $article){
            if($article->isArchived())
                $this->assertTrue($spec->isSatisfiedBy($article));
            else $this->assertFalse($spec->isSatisfiedBy($article));
        }
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