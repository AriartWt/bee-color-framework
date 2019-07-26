<?php
namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\command\PutArticlesOnline;
use wfw\engine\package\news\domain\errors\PutOnlineFailed;

/**
 * Met un article en ligne.
 */
final class PutArticlesOnlineHandler extends ArticleCommandHandler {
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command){
		/** @var PutArticlesOnline $command */
		foreach($command->getArticleIds() as $id){
			try{
				$article = $this->get($id);
				$article->putOnline($command->getUserId());
				$this->repos()->edit($article,$command);
			}catch(PutOnlineFailed $e){}
		}
	}
}