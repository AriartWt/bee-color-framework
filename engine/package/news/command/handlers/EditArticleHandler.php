<?php
namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\command\EditArticle;

/**
 * Edite un article
 */
final class EditArticleHandler extends ArticleCommandHandler {
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var EditArticle $command */
		$article = $this->get($command->getArticleId());
		if(!is_null($command->getTitle()))
			$article->editTitle($command->getTitle(),$command->getEditor());
		if(!is_null($command->getContent()))
			$article->editContent($command->getContent(),$command->getEditor());
		if(!is_null($command->getVisualLink()))
			$article->editVisual($command->getVisualLink(),$command->getEditor());
		$this->repos()->edit($article,$command);
	}
}