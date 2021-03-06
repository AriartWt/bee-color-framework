<?php
namespace wfw\daemons\modelSupervisor\client;

/**
 * Permet de trouver l'adresse de la socket d'écoute d'un MSServer.
 */
interface IMSInstanceAddrResolver {
	/**
	 * Permet de retrouver l'adresse de la socket d'écoute d'une instance de MSServer
	 * @param string $name Nom de l'instance du MSServer
	 * @return string
	 */
	public function find(string $name):string;
}