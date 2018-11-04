<?php
namespace wfw\engine\package\contact\command\handlers;

use wfw\engine\core\command\ICommandHandler;
use wfw\engine\package\contact\command\errors\ContactNotFound;
use wfw\engine\package\contact\domain\Contact;
use wfw\engine\package\contact\domain\repository\IContactRepository;

/**
 * Implémentation de base pour un handler de commande sur les prises de contact
 */
abstract class ContactCommandHandler implements ICommandHandler{
	/** @var IContactRepository $_repos */
	private $_repos;

	/**
	 * ContactCommandHandler constructor.
	 *
	 * @param IContactRepository $repos Repository de prises de contact
	 */
	public function __construct(IContactRepository $repos) {
		$this->_repos = $repos;
	}

	/**
	 * @param string $id identifiant du contact à retrouver
	 * @return Contact
	 * @throws ContactNotFound
	 */
	protected function get(string $id):Contact{
		$contact = $this->_repos->get($id);
		if(is_null($contact)) throw new ContactNotFound($id);
		return $contact;
	}

	/**
	 * @return IContactRepository
	 */
	protected function repos():IContactRepository{
		return $this->_repos;
	}
}