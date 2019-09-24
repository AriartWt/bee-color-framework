<?php
namespace wfw\daemons\modelSupervisor\server;

use wfw\daemons\modelSupervisor\socket\io\MSServerSocketIO;

/**
 *  Requête telle qu'enregistrée par le serveur.
 */
final class MSServerQuery implements IMSServerQuery {
	/** @var MSServerSocketIO $_io */
	private $_io;
	/** @var IMSServerInternalRequest $_request */
	private $_request;
	/** @var float $_expirationDate */
	private $_expirationDate;
	/** @var mixed $_generationDate */
	private $_generationDate;

	/**
	 * MSServerQuery constructor.
	 *
	 * @param MSServerSocketIO                 $io             Objet permettant de répondre à la requête.
	 * @param IMSServerInternalRequest $request        Requête
	 * @param float                            $expirationDate Date d'éxpiration de la requête
	 */
	public function __construct(
		MSServerSocketIO $io,
		IMSServerInternalRequest $request,
		float $expirationDate
	){
		$this->_io = $io;
		$this->_request = $request;
		$this->_generationDate = microtime(true);
		if($this->_generationDate<$expirationDate){
			$this->_expirationDate = $expirationDate;
		}else{
			throw new \InvalidArgumentException("Cannot create an outdated query !");
		}
	}

	/**
	 * @return MSServerSocketIO Client ayant envoyé la requête
	 */
	public function getIO(): MSServerSocketIO {
		return $this->_io;
	}

	/**
	 * @return IMSServerInternalRequest Requête interne envoyée à l'un des worker.
	 */
	public function getInternalRequest(): IMSServerInternalRequest {
		return $this->_request;
	}

	/**
	 * @return int Date d'expiration de la requête.
	 */
	public function getExpirationDate(): int {
		return $this->_expirationDate;
	}

	/**
	 * @return int Date à laquelle la requête a été créée
	 */
	public function getGenerationDate(): int {
		return $this->_generationDate;
	}
}