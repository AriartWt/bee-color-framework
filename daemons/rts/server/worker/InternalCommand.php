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
class InternalCommand {
	public const ROOT = "root";
	public const LOCAL = "local";
	public const WORKER = "worker";

	public const CMD_ACCEPT = "accept_new_client";
	public const CMD_REJECT = "reject_new_client";

	public const DATA_TRANSMISSION = "data_transmission";

	public const FEEDBACK_CLIENT_CREATED = "new_client_created";
	public const FEEDBACK_CLIENT_DISCONNECTED = "client_disconnected";

	/** @var string $_transmiter */
	private $_transmiter;
	/** @var string $_source */
	private $_source;
	/** @var string $_data */
	private $_data;
	/** @var string $_name */
	private $_name;
	/** @var string $_rootKey */
	private $_rootKey;

	/**
	 * WorkerCommand constructor.
	 *
	 * @param string $source          Source (local : un client port local, root : message du processus principal,
	 *                                client : message d'un autre client)
	 * @param string $name            Nom de la commande
	 * @param string $data            Données associées
	 * @param string $transmiter      Nom de l'emetteur (seulement local)
	 * @param string $rootKey         Root key generated at server starts.
	 */
	public function __construct(
		string $source,
		string $name,
		?string $data = null,
		?string $transmiter = null,
		string $rootKey = ''
	) {
		$this->_name = $name;
		$this->_data = $data;
		$this->_source = $source;
		$this->_transmiter = $transmiter;
		$this->_rootKey = $rootKey;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "{".
			'"cmd":"'.$this->_name.'",'.
			'"source":"'.$this->_source.'"'.
			((!empty($this->_data)) ? ',"data":"'.$this->_data.'"' : '').
			((!empty($this->_rootKey)) ? ',"root_key":"'.$this->_rootKey.'"' : '').
			((!empty($this->_transmiter)) ? ',"transmiter":"'.$this->_transmiter.'"' : '')
		."}";
	}
}