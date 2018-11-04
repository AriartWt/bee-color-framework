<?php
namespace wfw\engine\package\general\views\ajax;

use wfw\engine\core\view\View;

/**
 * Vue ajax de base
 */
final class Ajax extends View {
	/** @var string $_data */
	protected $_data;

	/**
	 * Ajax constructor.
	 *
	 * @param string      $code Code de réponse
	 * @param null        $data (optionnel) Données
	 * @param null|string $viewPath (optionnel) Chemin vers la vue
	 */
	public function __construct(string $code, $data=null, ?string $viewPath = null) {
		parent::__construct($viewPath);
		$this->_data = json_encode([
			"response"=>[
				"code"=> $code,
				"text"=> $data ?? null
			]
		]);
	}
}