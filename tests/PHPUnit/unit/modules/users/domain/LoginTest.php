<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 19:52
 */

namespace wfw\tests\PHPUnit\unit\modules\users\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\package\users\domain\Login;

/**
 * teste la classe Login
 */
class LoginTest extends TestCase {

	public function testLogin(){
		$str = "A fancy login";
		$login = new Login($str);
		$this->assertEquals($str,(string) $login);
	}

	public function testTooShort(){
		$this->expectException(\InvalidArgumentException::class);
		new Login(implode('',array_fill(0,3,'1')));
	}

	public function testTooLong(){
		$this->expectException(\InvalidArgumentException::class);
		new Login(implode('',array_fill(0,129,'1')));
	}
}