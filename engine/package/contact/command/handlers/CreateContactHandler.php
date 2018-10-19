<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/09/18
 * Time: 17:06
 */

namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\command\CreateContact;
use wfw\engine\package\contact\domain\Contact;

/**
 * Traite la commande de création d'une nouvelle prise de contact
 */
final class CreateContactHandler extends ContactCommandHandler {

	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var CreateContact $command */
		$this->repos()->add(new Contact(
			new UUID(),
			$command->getLabel(),
			$command->getInfos()
		),$command);
	}
}