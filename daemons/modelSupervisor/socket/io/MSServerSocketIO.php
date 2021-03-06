<?php
namespace wfw\daemons\modelSupervisor\socket\io;

use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\network\socket\ISocketIO;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 *  Gérer les écriture/lecture sur une socket ouverte et connectée
 */
final class MSServerSocketIO implements ISocketIO {
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var resource $_socket */
	private $_socket;
	/** @var bool $_closed */
	private $_closed = false;

	/**
	 * ModelManagerServerSocketIO constructor.
	 *
	 * @param ISocketProtocol $protocol Protocol de communication
	 * @param resource                $socket   Socket prêt à être utilisée
	 */
	public function __construct(ISocketProtocol $protocol,$socket) {
		$this->_socket = $socket;
		$this->_protocol = $protocol;
	}

	/**
	 * @return string
	 */
	public function read():string {
		if(!$this->_closed){
			return $this->_protocol->read($this->_socket);
		}else{
			throw new IllegalInvocation("Connection was closed !");
		}
	}

	/**
	 * @param string $data Données à écrire
	 */
	public function write(string $data) {
		if(!$this->_closed){
			$this->_protocol->write($this->_socket,$data);
		}else{
			throw new IllegalInvocation("Connection was closed !");
		}
	}

	/**
	 *  Ferme la connexion de la socket. Rend l'objet inutilisable.
	 */
	public function closeConnection(){
		if(!$this->_closed){
			$this->_closed = true;
			socket_close($this->_socket);
		}
	}
}