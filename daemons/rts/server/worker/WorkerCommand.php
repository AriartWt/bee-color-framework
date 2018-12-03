<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/08/18
 * Time: 18:53
 */

namespace wfw\daemons\rts\server\worker;

/**
 * Commande à envoyer à des workers
 */
class WorkerCommand {
	public const LOCAL = "local";
	public const ROOT = "root";
	public const CLIENT = "client";

	/** @var string $_transmiter */
	private $_transmiter;
	/** @var string $_source */
	private $_source;
	/** @var string $_data */
	private $_data;
	/** @var string $_name */
	private $_name;

	/**
	 * WorkerCommand constructor.
	 *
	 * @param string $source  Source (local : un client port local, root : message du processus principal,
	 *                        client : message d'un autre client)
	 * @param string $name    Nom de la commande
	 * @param string $data    Données associées
	 * @param string $transmiter Nom de l'emetteur (seulement local)
	 */
	public function __construct(string $source, string $name, string $data = '', string $transmiter = '') {
		$this->_name = $name;
		$this->_data = $data;
		$this->_source = $source;
		$this->_transmiter = $transmiter;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "{".
			'"cmd":"'.$this->_name.'",'.
			'"source":"'.$this->_source.'"'.
			((!empty($this->_data)) ? ',"data":"'.$this->_data.'"' : '').
			((!empty($this->_transmiter)) ? ',"transmiter":"'.$this->_transmiter.'"' : '')
		."}";
	}
}