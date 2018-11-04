<?php
namespace wfw\engine\package\contact\data\model;

use wfw\engine\core\data\model\EventReceptionReport;
use wfw\engine\core\data\model\InMemoryEventBasedModel;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\data\model\specs\IsArchived;
use wfw\engine\package\contact\data\model\specs\NotArchived;
use wfw\engine\package\contact\data\model\specs\NotRead;
use wfw\engine\package\contact\data\model\specs\Read;
use wfw\engine\package\contact\domain\events\ArchivedEvent;
use wfw\engine\package\contact\domain\events\ContactedEvent;
use wfw\engine\package\contact\domain\events\ContactEvent;
use wfw\engine\package\contact\domain\events\MarkedAsReadEvent;
use wfw\engine\package\contact\domain\events\MarkedAsUnreadEvent;
use wfw\engine\package\contact\domain\events\UnarchivedEvent;

/**
 * Model contenant les prises de contact
 */
class ContactModel extends InMemoryEventBasedModel{
	public const READ = "read";
	public const NOT_READ = "notRead";
	public const ARCHIVED = "archived";
	public const NOT_ARCHIVED = "notArchived";

	/**
	 * Retourne la liste des classes des événements qui sont écoutés par le model
	 * @return string[]
	 */
	public function listenEvents(): array {
		return [ ContactEvent::class ];
	}

	/**
	 *  Traite la reception d'un événement.
	 *
	 * @param \wfw\engine\core\domain\events\IDomainEvent $e Evenement recu
	 *
	 * @return EventReceptionReport
	 */
	protected function recieve(IDomainEvent $e): EventReceptionReport {
		if($e instanceof ContactEvent){
			/** @var Contact $contact */
			$contact = $this->getById($e->getAggregateId());
			if(is_null($contact)){
				if($e instanceof ContactedEvent){
					return new EventReceptionReport([
						new Contact(
							$e->getAggregateId(),
							$e->getLabel(),
							$e->getInfos(),
							$e->getGenerationDate()
						)
					]);
				}
			}else{
				if($e instanceof MarkedAsReadEvent){
					$contact->markAsRead($e->getGenerationDate());
				}else if($e instanceof MarkedAsUnreadEvent){
					$contact->markAsUnread();
				}else if($e instanceof ArchivedEvent){
					$contact->archive($e->getGenerationDate());
				}else if($e instanceof UnarchivedEvent){
					$contact->unarchive();
				}
				return new EventReceptionReport(null,[$contact]);
			}
		}
		return new EventReceptionReport();
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
	protected function indexes(): array {
		return[
			self::ARCHIVED => new IsArchived(),
			self::NOT_ARCHIVED => new NotArchived(),
			self::READ => new Read(),
			self::NOT_READ => new NotRead()
		];
	}
}