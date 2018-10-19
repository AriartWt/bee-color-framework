<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/06/18
 * Time: 16:50
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\CancelPasswordRetrieving;

/**
 * Applique la commande d'annulation de restauration de mot de passe.
 */
final class CancelPasswordRetrievingHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var CancelPasswordRetrieving $command */
		$user = $this->get($command->getUserId());
		$user->cancelRetrivingPassword($command->getModifierId());
		$this->repos()->modify($user,$command);
	}
}