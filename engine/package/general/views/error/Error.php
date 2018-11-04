<?php
namespace wfw\engine\package\general\views\error;

use wfw\engine\core\view\View;
use wfw\engine\lib\network\http\HTTPStatus;

/**
 * Vue d'erreur.
 */
final class Error extends View {
	/** @var string $_msg */
	protected $_msg;

	/**
	 * Error constructor.
	 *
	 * @param string      $msg  Message d'erreur.
	 * @param int|null    $code (optionnel) Code HTTP
	 * @param null|string $viewPath
	 */
	public function __construct(string $msg,?int $code = null,?string $viewPath=null) {
		parent::__construct($viewPath);
		if(!is_null($code)){
			if(HTTPStatus::existsValue($code)){
				http_response_code($code);
			}
		}
		$this->_msg = $msg;
	}
}