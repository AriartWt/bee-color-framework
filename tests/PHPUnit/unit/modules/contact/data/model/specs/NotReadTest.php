<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 22:37
 */

namespace wfw\tests\PHPUnit\unit\modules\contact\data\model\specs;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\data\model\specs\NotRead;
use wfw\engine\package\contact\data\model\specs\Read;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * teste de la spec IsOffline
 */
class NotReadTest extends TestCase
{
    public function testMatchAllNotReadContcats(){
        $list = $this->createContacts();
        $spec = new NotRead();
        foreach($list as $contact){
            if(!$contact->isRead())
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
                new ContactLabel("Lable : $i"),
                new ContactInfos("Infos : $i"),
                microtime(true)-rand(50,50000),
                true,
                microtime(true)-rand(50,50000)
            );
            if($i%3===0) $contact->markAsUnread();
            $res[] = $contact;
        }
        return $res;
    }
}