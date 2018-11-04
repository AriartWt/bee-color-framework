<?php
namespace wfw\engine\package\contact\data\model;

use wfw\engine\package\contact\data\model\DTO\Contact;

/**
 * Accés au model de prises de contact
 */
interface IContactModelAccess {
	/**
	 * @return Contact[] Liste de toutes les prises de contact
	 */
	public function getAll():array;

	/**
	 * @return Contact[] Liste de toutes les prises de contact archivées
	 */
	public function getArchived():array;

	/**
	 * @return Contact[] Liste de toutes les prises de contact non archivées
	 */
	public function getUnarchived():array;

	/**
	 * @return Contact[] Liste de toutes les prises de contact lues.
	 */
	public function getRead():array;

	/**
	 * @return Contact[] Liste de toutes les prises de contact non lues.
	 */
	public function getNotRead():array;
}