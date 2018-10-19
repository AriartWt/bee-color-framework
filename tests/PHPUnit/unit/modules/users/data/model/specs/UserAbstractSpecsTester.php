<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 14:49
 */

namespace wfw\tests\PHPUnit\unit\modules\users\data\model\specs;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\RemovedUser;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Client;

/**
 * Class UserAbstractSpecsTester
 * @package wfw\tests\PHPUnit\unit\modules\users\data\model\specs
 */
abstract class UserAbstractSpecsTester extends TestCase{
	/**
	 * Retourne une liste d'utilisateurs dont :
	 * un utilisateur sur cinq est supprimé. (4)
	 * Parmis les utilisateurs non supprimés, un sur deux est activé(8)/désactivé(8)
	 * un utilisateur sur cinq est un Admin,
	 * Parmis les utilisateurs non Admin(4), un sur trois est Client(6), les autres sont Basic(10)
	 * @return User[]
	 */
	protected function createUsers():array{
		$res = [];
		for($i=0;$i<20;$i++){
			$res [] = new User(
				new UUID(),
				new Login("User n$i"),
				new Password("$i superpassword"),
				new Email("an$i@email.fr"),
				new InMemoryUserSettings(),
				($i%5 === 0) ? new RemovedUser() : ($i%2 === 0) ? new EnabledUser() : new DisabledUser(),
				($i%5 === 0) ? new Admin() : ($i%3 === 0) ? new Client() : new Admin(),
				new UUID()
			);
		}
		return $res;
	}
}