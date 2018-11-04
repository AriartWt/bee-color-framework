<?php
namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;

use wfw\engine\package\users\data\model\specs\IsClient;
use wfw\engine\package\users\domain\types\Client;

/**
 * Teste la spec IsClient
 */
class IsClientTest extends UserAbstractSpecsTester{
	public function testMatchAllClientUsers(){
		$list = $this->createUsers();
		$spec = new IsClient();
		foreach($list as $user){
			if($user->getType() instanceof Client){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}