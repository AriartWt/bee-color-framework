<?php
namespace wfw\engine\lib\network\socket\data;

/**
 * Permet d'interpreter le resultat final d'une lecture sur une socket.
 */
interface IDataParser {
	/**
	 * @param string $data Parse les données reçues d'une socket.
	 *
	 * @return mixed Resultat du parsing
	 */
	public function parseData(string $data);

	/**
	 * @param mixed $message Données à linéariser pour être envoyées dans une socket.
	 *
	 * @return string
	 */
	public function lineariseData($message):string;
}