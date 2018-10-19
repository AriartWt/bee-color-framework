<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 15:15
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;
use wfw\engine\package\users\data\model\specs\IsDisabled;
use wfw\engine\package\users\domain\states\DisabledUser;

/**
 * Class IsDisabledTest
 * @package wfw\tests\PHPUnit\unit\modules\users\data\model\specs
 */
class IsDisabledTest extends UserAbstractSpecsTester{
	public function testMatchAllDisabledUsers(){
		$list = $this->createUsers();
		$spec = new IsDisabled();
		foreach($list as $user){
			if($user->getState() instanceof DisabledUser){
				$this->assertTrue($spec->isSatisfiedBy($user));
			}else $this->assertFalse($spec->isSatisfiedBy($user));
		}
	}
}