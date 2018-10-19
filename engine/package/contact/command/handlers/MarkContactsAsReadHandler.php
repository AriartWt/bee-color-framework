<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/09/18
 * Time: 17:01
 */

namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\contact\command\MarkContactsAsRead;
use wfw\engine\package\contact\domain\errors\MarkAsReadFailed;

/**
 * Marque les prises de contacts spécifiée comme luesg
 */
final class MarkContactsAsReadHandler extends ContactCommandHandler {

	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		$res=[];
		/** @var MarkContactsAsRead $command */
		foreach($command->getIds() as $id){
			try{
				$contact = $this->get($id);
				$contact->markAsRead($command->getUserId());
				$res[] = $contact;
			}catch(MarkAsReadFailed $e){}
		}
		$this->repos()->editAll($command,...$res);
	}
}