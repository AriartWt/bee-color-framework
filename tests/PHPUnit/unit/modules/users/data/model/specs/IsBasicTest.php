<?php
namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;


use wfw\engine\package\users\data\model\specs\IsBasic;
use wfw\engine\package\users\domain\types\Basic;

/**
 * Teste la spec Basic
 */
class IsBasicTest extends UserAbstractSpecsTester{
	public function testMatchAllBasicUsers(){
		$list = $this->createUsers();
		$spec = new IsBasic();
		foreach($list as $user){
			if($user->getType() instanceof Basic){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}