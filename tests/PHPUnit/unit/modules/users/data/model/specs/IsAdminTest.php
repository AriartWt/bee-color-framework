<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 15:06
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;


use wfw\engine\package\users\data\model\specs\IsAdmin;
use wfw\engine\package\users\domain\types\Admin;

class IsAdminTest extends UserAbstractSpecsTester{
	public function testMatchAllAdminUsers(){
		$list = $this->createUsers();
		$spec = new IsAdmin();
		foreach($list as $user){
			if($user->getType() instanceof Admin){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}