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
final class InternalCommand implements \Serializable {
	public const ROOT = "root";
	public const LOCAL = "local";
	public const WORKER = "worker";

	public const SHUTDOWN = "shutdown";
	public const CMD_ACCEPT = "accept_new_client";
	public const CMD_REJECT = "reject_new_client";

	public const DATA_TRANSMISSION = "data_transmission";

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
	public function getTransmiter(): string {
		return $this->_transmiter;
	}

	/**
	 * @return string
	 */
	public function getSource(): string {
		return $this->_source;
	}

	/**
	 * @return string
	 */
	public function getData(): string {
		return $this->_data;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function getRootKey(): string {
		return $this->_rootKey;
	}

	/**
	 * String representation of object
	 *
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize([
			$this->_rootKey,
			$this->_data,
			$this->_source,
			$this->_name,
			$this->_transmiter
		]);
	}

	/**
	 * Constructs the object
	 *
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) {
		list(
			$this->_rootKey,
			$this->_data,
			$this->_source,
			$this->_name,
			$this->_transmiter
		) = unserialize($serialized);
	}
}