<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 11:06
 */

namespace wfw\engine\package\news\data\model;

use wfw\engine\core\data\model\EventReceptionReport;
use wfw\engine\core\data\model\InMemoryEventBasedModel;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\package\news\data\model\objects\Article;
use wfw\engine\package\news\data\model\specs\AuthorIs;
use wfw\engine\package\news\data\model\specs\IsArchived;
use wfw\engine\package\news\data\model\specs\IsOffline;
use wfw\engine\package\news\data\model\specs\IsOnline;
use wfw\engine\package\news\data\model\specs\NotArchived;
use wfw\engine\package\news\domain\events\ArchivedEvent;
use wfw\engine\package\news\domain\events\ArticleEvent;
use wfw\engine\package\news\domain\events\ArticleWrittenEvent;
use wfw\engine\package\news\domain\events\ContentEditedEvent;
use wfw\engine\package\news\domain\events\PutOfflineEvent;
use wfw\engine\package\news\domain\events\PutOnlineEvent;
use wfw\engine\package\news\domain\events\TitleEditedEvent;
use wfw\engine\package\news\domain\events\UnarchivedEvent;
use wfw\engine\package\news\domain\events\VisualLinkEditedEvent;
use wfw\engine\package\users\domain\events\UserRemovedEvent;

/**
 * Model de représentation des articles
 */
class ArticleModel extends InMemoryEventBasedModel {
	public const ARCHIVED = "archived";
	public const ONLINE = "online";
	public const OFFLINE = "offline";
	public const NOT_ARCHIVED = "notArchived";

	/**
	 *  Retourne la liste des classes des événements qui sont écoutés par le model
	 *
	 * @return string[]
	 */
	public function listenEvents(): array {
		return [ ArticleEvent::class, UserRemovedEvent::class ];
	}

	/**
	 * Doit retourner un tableau name=>ISpecification qui définit les indexes à utiliser
	 * pour le modèle courant.
	 * La liste des indexes et synchronisée avec le modèle au moment de la construction puis à
	 * chaque déserialsiation de sorte que les indexes définis soient toujours en adéquation
	 * avec les indexes disponibles pour les recherches sur les modèles.
	 * Par défaut, le teste d'égalité entre un ancien index et un nouvel index se base sur la classe
	 * de la spécification. Si une methode equals():bool est définie sur la Specification, alors
	 * c'est cette méthode qui sera utilisée pour la comparaison. Cela permet de mettre à jour des
	 * indexes contenant certaines données.
	 *
	 * @return ISpecification[]
	 */
	protected function indexes(): array{
		return [
			self::OFFLINE => new IsOffline(),
			self::ONLINE => new IsOnline(),
			self::ARCHIVED => new IsArchived(),
			self::NOT_ARCHIVED => new NotArchived()
		];
	}

	/**
	 *  Traite la reception d'un événement.
	 *
	 * @param \wfw\engine\core\domain\events\IDomainEvent $e Evenement recu
	 *
	 * @return EventReceptionReport
	 */
	protected function recieve(IDomainEvent $e): EventReceptionReport
	{
		if($e instanceof ArticleEvent){
			/** @var ArticleEvent $e */
			/** @var Article $article */
			$article = $this->getById($e->getAggregateId());
			if(is_null($article)){
				if($e instanceof ArticleWrittenEvent){
					$article = new Article(
						$e->getAggregateId(),
						$e->getTitle(),
						$e->getVisualLink(),
						$e->getContent(),
						$e->getAuthor(),
						$e->getGenerationDate(),
						$e->isOnline());
					return new EventReceptionReport([$article]);
				}
			}else{
				if($e instanceof ContentEditedEvent){
					$article->setContent($e->getContent());
					$article->edited(
						$e->getEditorId(),
						$e->getGenerationDate(),
						Article::CONTENT
					);
				}else if($e instanceof TitleEditedEvent){
					$article->setTitle($e->getTitle());
					$article->edited(
						$e->getEditorId(),
						$e->getGenerationDate(),
						Article::TITLE
					);
				}else if($e instanceof VisualLinkEditedEvent){
					$article->setVisualLink($e->getVisualLink());
					$article->edited(
						$e->getEditorId(),
						$e->getGenerationDate(),
						Article::VISUAL
					);
				}else if($e instanceof PutOfflineEvent){
					$article->setOnline(false);
					$article->edited(
						$e->getUserId(),
						$e->getGenerationDate(),
						Article::OFFLINE
					);
				}else if($e instanceof PutOnlineEvent){
					$article->setOnline(true);
					$article->edited(
						$e->getUserId(),
						$e->getGenerationDate(),
						Article::ONLINE
					);
				}else if($e instanceof ArchivedEvent){
					$article->setArchived(true);
					$article->edited(
						$e->getArchiver(),
						$e->getGenerationDate(),
						Article::ARCHIVED
					);
				}else if($e instanceof UnarchivedEvent){
					$article->setArchived(false);
					$article->edited(
						$e->getUnarchiver(),
						$e->getGenerationDate(),
						Article::UNARCHIVED
					);
				}
			}
			return new EventReceptionReport(null,[$article]);
		}else if($e instanceof UserRemovedEvent){
			return new EventReceptionReport(
				null,
				null,
				$this->find((string)new AuthorIs($e->getAggregateId()))
			);
		}
		return new EventReceptionReport();
	}
}