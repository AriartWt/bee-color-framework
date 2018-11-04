<?php
namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;
use wfw\engine\package\users\data\model\specs\IsEnabled;
use wfw\engine\package\users\domain\states\EnabledUser;

/**
 * teste la spec IsEnabled
 */
class IsEnabledTest extends UserAbstractSpecsTester {
	public function testMatchAllEnabledUsers(){
		$list = $this->createUsers();
		$spec = new IsEnabled();
		foreach($list as $user){
			if($user->getState() instanceof EnabledUser){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}