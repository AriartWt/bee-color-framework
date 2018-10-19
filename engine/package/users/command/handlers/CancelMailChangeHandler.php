<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/06/18
 * Time: 16:46
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\CancelMailChange;

/**
 * Applique la commande CacelMailChanges
 */
final class CancelMailChangeHandler extends UserCommandHandler{

	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var CancelMailChange $command */
		$user = $this->get($command->getUserId());
		$user->cancelEmailChange($command->getModifierId());
		$this->repos()->modify($user,$command);
	}
}