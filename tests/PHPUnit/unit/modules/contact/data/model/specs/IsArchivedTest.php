<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 22:42
 */

namespace wfw\tests\PHPUnit\unit\modules\contact\data\model\specs;


use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\data\model\specs\IsArchived;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * Teste la spec IsArchived
 */
class IsArchivedTest extends TestCase
{
    public function testMatchAllArchivedContacts(){
        $list = $this->createContacts();
        $spec = new IsArchived();
        foreach($list as $contact){
            if($contact->isArchived())
                $this->assertTrue($spec->isSatisfiedBy($contact));
            else $this->assertFalse($spec->isSatisfiedBy($contact));
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
                new ContactLabel("label :$i"),
                new ContactInfos("infos : $i"),
                microtime(true)-rand(50,50000)
            );
            if($i%3===0) $contact->archive(microtime(true)-rand(50,50000));
            $res[] = $contact;
        }
        return $res;
    }
}