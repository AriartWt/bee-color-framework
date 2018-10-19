<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/10/18
 * Time: 11:19
 */

namespace wfw\engine\package\contact\data\model;

use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\package\contact\data\model\DTO\Contact;

/**
 * Permet d'accéder au model de prises de contact
 */
final class ContactModelAccess implements IContactModelAccess {
	/** @var IMSServerAccess $_db */
	private $_db;

	/**
	 * ContactModelAccess constructor.
	 *
	 * @param IMSServerAccess $access Accés
	 */
	public function __construct(IMSServerAccess $access) {
		return $this->_db = $access;
	}

	/**
	 * @return Contact[] Liste de toutes les prises de contact
	 */
	public function getAll(): array {
		return $this->_db->query(ContactModel::class,"id");
	}

	/**
	 * @return Contact[] Liste de toutes les prises de contact archivées
	 */
	public function getArchived(): array {
		return $this->_db->query(ContactModel::class,ContactModel::ARCHIVED);
	}

	/**
	 * @return Contact[] Liste de toutes les prises de contact non archivées
	 */
	public function getUnarchived(): array {
		return $this->_db->query(ContactModel::class,ContactModel::NOT_ARCHIVED);
	}

	/**
	 * @return Contact[] Liste de toutes les prises de contact lues.
	 */
	public function getRead(): array {
		return $this->_db->query(ContactModel::class,ContactModel::READ);
	}

	/**
	 * @return Contact[] Liste de toutes les prises de contact non lues.
	 */
	public function getNotRead(): array {
		return $this->_db->query(ContactModel::class,ContactModel::NOT_READ);
	}
}