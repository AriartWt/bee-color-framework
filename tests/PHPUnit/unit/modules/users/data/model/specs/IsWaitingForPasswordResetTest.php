<?php
namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;


use wfw\engine\package\users\data\model\specs\IsWaitingForPasswordReset;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;

/**
 * Class IsWaitingForPasswordResetTest
 * @package wfw\tests\PHPUnit\unit\modules\users\data\model\specs
 */
class IsWaitingForPasswordResetTest extends UserAbstractSpecsTester {
	public function testMatchAllAdminUsers(){
		$list = $this->createUsers();
		$spec = new IsWaitingForPasswordReset();
		foreach($list as $user){
			if($user->getState() instanceof UserWaitingForPasswordReset){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}