<?php
namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\command\PutArticlesOffline;
use wfw\engine\package\news\domain\errors\PutOfflineFailed;

/**
 * Handle rpour la commande de mise hors ligne d'un article
 */
final class PutArticlesOfflineHandler extends ArticleCommandHandler {
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var PutArticlesOffline $command */
		foreach($command->getArticleIds() as $id){
			try{
				$article = $this->get($id);
				$article->putOffline($command->getInitiatorId());
				$this->repos()->edit($article,$command);
			}catch(PutOfflineFailed $e){}
		}
	}
}