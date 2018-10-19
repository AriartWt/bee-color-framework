<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/06/18
 * Time: 16:45
 */
namespace wfw\engine\core\session;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\types\Admin;

function session_start(){
	$_SESSION=[];
	$_SESSION["csrfToken"] = "falseToken";
	$_SESSION["user"] = new User(
		new UUID(UUID::V4,"e9107d64-ecb8-46cf-a98d-332c1b1a904a"),
		new Login("test"),
		new Password("testtest"),
		new Email("email@email.fr"),
		new InMemoryUserSettings(),
		new EnabledUser(),
		new Admin(),
		''
	);
};

function session_destroy(){
	$_SESSION=[];
}