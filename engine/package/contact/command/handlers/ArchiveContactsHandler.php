<?php
namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\contact\command\ArchiveContacts;
use wfw\engine\package\contact\domain\errors\ArchivingFailure;

/**
 * Traite une commande d'archivage de contacts
 */
final class ArchiveContactsHandler extends ContactCommandHandler{
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		$res = [];
		/** @var ArchiveContacts $command */
		foreach($command->getIds() as $id){
			try{
				$contact = $this->get($id);
				$contact->archive($command->getUserId());
				$res[] = $contact;
			}catch(ArchivingFailure $e){}
		}
		$this->repos()->editAll($command,...$res);
	}
}