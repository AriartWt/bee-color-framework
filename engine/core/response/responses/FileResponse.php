<?php

namespace wfw\engine\core\response\responses;

use wfw\engine\core\response\IResponse;

/**
 * Class FileResponse
 *
 * @package wfw\engine\core\response\responses
 */
class FileResponse implements IResponse {
	/** @var string $_fileName */
	private $_fileName;

	/**
	 * FileResponse constructor.
	 *
	 * @param string $fileName
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $fileName) {
		if(!file_exists($fileName)) throw new \InvalidArgumentException(
			"File not found $fileName"
		);
		else $this->_fileName = $fileName;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return $this->_fileName;
	}
}