<?php
namespace wfw\cli\updator;

/**
 * Gestionnaire de mise à jour
 */
interface IUpdator {
	/**
	 * Vérifie la disponibilité d'une mise à jour
	 * @return array Liste des mises à jour à installer
	 */
	public function check():array;

	/**
	 * Telecharge les mises à jour disponibles
	 * @param null|string $dest Destination des mises à jour
	 * @return void
	 */
	public function download(?string $dest=null):void;

	/**
	 * @param null|string $source Dossier dans lequel se trouvent les mises à jour à installer.
	 */
	public function install(?string $source=null):void;
}