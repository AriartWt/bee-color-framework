<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/09/18
 * Time: 16:57
 */

namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\contact\command\UnarchiveContacts;
use wfw\engine\package\contact\domain\errors\ArchivingFailure;

/**
 * Execute la commande de désarchivage de prise de contact
 */
final class UnarchiveContactsHandler extends ContactCommandHandler{
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		$res=[];
		/** @var UnarchiveContacts $command */
		foreach($command->getIds() as $id){
			try{
				$contact = $this->get($id);
				$contact->unarchive($command->getUserId());
				$res[] = $contact;
			}catch(ArchivingFailure $e){}
		}
		$this->repos()->editAll($command,...$res);
	}
}