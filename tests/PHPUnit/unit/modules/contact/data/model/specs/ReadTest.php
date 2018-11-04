<?php
namespace wfw\tests\PHPUnit\unit\modules\contact\data\model\specs;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\data\model\specs\Read;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * teste de la spec IsOffline
 */
class ReadTest extends TestCase
{
	public function testMatchAllReadContact(){
		$list = $this->createContacts();
		$spec = new Read();
		foreach($list as $contact){
			if($contact->isRead())
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
				microtime(true)-rand(50,50000)
			);
			if($i%3===0) $contact->markAsRead(microtime(true)-rand(50,50000));
			$res[] = $contact;
		}
		return $res;
	}
}