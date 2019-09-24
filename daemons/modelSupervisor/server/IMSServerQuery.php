<?php
namespace wfw\daemons\modelSupervisor\server;

use wfw\daemons\modelSupervisor\socket\io\MSServerSocketIO;

/**
 *  Représente une requête reçue par le MSServer, envoyée par un MSClient.
 */
interface IMSServerQuery {
	/**
	 * @return MSServerSocketIO Client ayant envoyé la requête
	 */
	public function getIO():MSServerSocketIO;

	/**
	 * @return IMSServerInternalRequest Requête interne envoyée à l'un des worker.
	 */
	public function getInternalRequest():IMSServerInternalRequest;

	/**
	 * @return int Date d'expiration de la requête.
	 */
	public function getExpirationDate(): int;

	/**
	 * @return int Date à laquelle la requête a été créée
	 */
	public function getGenerationDate() : int;
}