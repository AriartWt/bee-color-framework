<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 19:44
 */

namespace wfw\tests\PHPUnit\unit\modules\users\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\package\users\domain\Password;

/**
 * teste la classe domain/password
 */
class PasswordTest extends TestCase{
	public function testPasswordEqualsFunctionnality(){
		$strpwd = "a password";
		$password = new Password($strpwd);
		$this->assertTrue($password->equals($strpwd));
	}

	public function testPasswordToStringMustNotBeEqualsToPasswordString(){
		$strpwd = "a password";
		$password = new Password($strpwd);
		$this->assertNotEquals($strpwd,(string) $password);
	}

	public function testTooShort(){
		$this->expectException(\InvalidArgumentException::class);
		new Password(implode('',array_fill(0,3,'1')));
	}

	public function testTooLong(){
		$this->expectException(\InvalidArgumentException::class);
		new Password(implode('',array_fill(0,129,'1')));
	}
}