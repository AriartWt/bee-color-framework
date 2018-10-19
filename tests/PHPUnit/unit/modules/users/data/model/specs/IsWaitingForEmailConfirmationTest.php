<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 15:19
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;


use wfw\engine\package\users\data\model\specs\IsWaitingForEmailConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;

class IsWaitingForEmailConfirmationTest extends UserAbstractSpecsTester{
	public function testMatchAllWaitingForEmailConfirmationUsers(){
		$list = $this->createUsers();
		$spec = new IsWaitingForEmailConfirmation();
		foreach($list as $user){
			if($user->getState() instanceof UserWaitingForEmailConfirmation){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}