<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/09/18
 * Time: 17:03
 */

namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\contact\command\MarkContactsAsUnread;
use wfw\engine\package\contact\domain\errors\MarkAsUnreadFailed;

/**
 * Marque les prises de contact spécifiées comme non lues.
 */
final class MarkContactsAsUnreadHandler extends ContactCommandHandler{
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		$res=[];
		/** @var MarkContactsAsUnread $command */
		foreach($command->getIds() as $id){
			try{
				$contact = $this->get($id);
				$contact->markAsUnread($command->getUserId());
				$res[] = $contact;
			}catch(MarkAsUnreadFailed $e){}
		}
		$this->repos()->editAll($command,...$res);
	}
}