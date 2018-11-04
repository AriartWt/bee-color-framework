<?php
namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\command\UnarchiveArticles;
use wfw\engine\package\news\domain\errors\ArchivingFailed;

/**
 * Désarchive un article archivé.
 */
final class UnarchiveArticlesHandler extends ArticleCommandHandler {
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var UnarchiveArticles $command */
		foreach($command->getArticleIds() as $id){
			try{
				$article = $this->get($id);
				$article->unarchive($command->getUserId());
				$this->repos()->edit($article,$command);
			}catch(ArchivingFailed $e){}
		}
	}
}