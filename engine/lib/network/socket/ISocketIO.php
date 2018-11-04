<?php 
namespace wfw\engine\lib\network\socket;

/**
 *  Ecriture/lecture sur socket
 */
interface ISocketIO{
	/**
	 * @return string
	 */
	public function read():string;

	/**
	 * @param string $data Données à écrires
	 */
	public function write(string $data);
}