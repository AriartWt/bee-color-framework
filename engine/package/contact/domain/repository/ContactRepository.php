<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/09/18
 * Time: 17:50
 */

namespace wfw\engine\package\contact\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\repository\IAggregateRootRepository;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\Contact;

/**
 * Repository de prises de contact
 */
final class ContactRepository implements IContactRepository{
	/** @var IAggregateRootRepository $_repos */
	private $_repos;

	/**
	 * ContactRepository constructor.
	 *
	 * @param IAggregateRootRepository $repos Gestionnaire de repository pour les aggrégats
	 */
	public function __construct(IAggregateRootRepository $repos) {
		$this->_repos = $repos;
	}

	/**
	 * @param string $id Identifiant de la prise de contact a retrouver
	 * @return null|Contact
	 */
	public function get(string $id): ?Contact {
		/** @var null|Contact $contact */
		$contact = $this->_repos->get(new UUID(UUID::V6,$id));
		return $contact;
	}

	/**
	 * Retourne tous les prises de contact correspondants aux identifiants
	 *
	 * @param string[] $ids Liste d'identifiants de prise de contact
	 * @return Contact[]
	 */
	public function getAll(string... $ids): array {
		$uuids = [];
		foreach($ids as $id){$uuids[] = new UUID(UUID::V6,$id);}
		return $this->_repos->getAll(...$uuids);
	}

	/**
	 * @param Contact  $contact Ajoute une prise de contact au repository
	 * @param ICommand $command Commande ayant entraîné la création de la prise de contact
	 */
	public function add(Contact $contact, ICommand $command): void {
		$this->_repos->add($contact,$command);
	}

	/**
	 * @param Contact  $contact Prise de contact éditée
	 * @param ICommand $command Commande ayant entraîné la modiofication de la prise de contact
	 */
	public function edit(Contact $contact, ICommand $command): void {
		$this->_repos->modify($contact,$command);
	}


	/**
	 * @param ICommand $command     Commande à l'origine des changements
	 * @param Contact  ...$contacts Liste de contacts à éditer
	 */
	public function editAll(ICommand $command, Contact... $contacts): void {
		$this->_repos->modifyAll($command,...$contacts);
	}
}