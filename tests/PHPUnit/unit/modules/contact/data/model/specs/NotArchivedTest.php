<?php
namespace wfw\tests\PHPUnit\unit\modules\contact\data\model\specs;


use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\data\model\specs\NotArchived;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * Teste la spec NotArchived
 */
class NotArchivedTest extends TestCase
{
    public function testMatchAllUnarchivedContacts(){
        $list = $this->createContacts();
        $spec = new NotArchived();
        foreach($list as $article){
            if(!$article->isArchived())
                $this->assertTrue($spec->isSatisfiedBy($article));
            else $this->assertFalse($spec->isSatisfiedBy($article));
        }
    }
    /**
     * @return Contact[]
     * @throws \InvalidArgumentException
     */
    private function createContacts():array{
        $res =[];
        for($i=0;$i<20;$i++){
            $contact = new Contact(
                new UUID(),
                new ContactLabel("label $i"),
                new ContactInfos("infos $i"),
                microtime(true)-rand(50,50000),
                false,
                null,
                $i%2 === 0,
                microtime(true)-rand(50,50000)
            );
            $res[] = $contact;
        }
        return $res;
    }
}