<?php
namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\command\ArchiveArticles;
use wfw\engine\package\news\domain\errors\ArchivingFailed;

/**
 * Archive un article
 */
final class ArchiveArticlesHandler extends ArticleCommandHandler {
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var ArchiveArticles $command */
		foreach($command->getArticleIds() as $id){
			try{
				$article = $this->get($id);
				$article->archive($command->getUserId());
				$this->repos()->edit($article,$command);
			}catch(ArchivingFailed $e){}
		}
	}
}