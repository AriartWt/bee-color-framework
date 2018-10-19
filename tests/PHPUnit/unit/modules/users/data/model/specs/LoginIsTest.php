<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 15:24
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;

use wfw\engine\package\users\data\model\specs\LoginIs;

/**
 * Class LoginIsTest
 * @package wfw\tests\PHPUnit\unit\modules\users\data\model\specs
 */
class LoginIsTest extends UserAbstractSpecsTester {
	public function testMatchAllAdminUsers(){
		$list = $this->createUsers();
		$spec = new LoginIs("User n3");
		foreach($list as $user){
			if((string)$user->getLogin() === "User n3"){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}