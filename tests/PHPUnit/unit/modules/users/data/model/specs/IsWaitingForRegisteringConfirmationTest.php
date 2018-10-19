<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 15:23
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;


use wfw\engine\package\users\data\model\specs\IsWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;

class IsWaitingForRegisteringConfirmationTest extends UserAbstractSpecsTester{
	public function testMatchAllAdminUsers(){
		$list = $this->createUsers();
		$spec = new IsWaitingForRegisteringConfirmation();
		foreach($list as $user){
			if($user->getState() instanceof UserWaitingForRegisteringConfirmation){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}