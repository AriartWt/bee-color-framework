<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/09/18
 * Time: 17:47
 */

namespace wfw\engine\package\contact\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\contact\domain\Contact;

/**
 * Repository des prises de contact
 */
interface IContactRepository {
	/**
	 * @param string $id Identifiant de la prise de contact a retrouver
	 * @return null|Contact
	 */
	public function get(string $id):?Contact;

	/**
	 * @param string ...$id Liste des identifiants
	 * @return Contact[]
	 */
	public function getAll(string... $id):array;

	/**
	 * @param Contact  $contact Ajoute une prise de contact au repository
	 * @param ICommand $command Commande ayant entraîné la création de la prise de contact
	 */
	public function add(Contact $contact,ICommand $command):void;

	/**
	 * @param Contact  $contact Prise de contact éditée
	 * @param ICommand $command Commande ayant entraîné la modiofication de la prise de contact
	 */
	public function edit(Contact $contact,ICommand $command):void;

	/**
	 * @param ICommand $command Commande à l'origine des changements
	 * @param Contact  ...$contacts Liste de contacts à éditer
	 */
	public function editAll(ICommand $command, Contact... $contacts):void;
}