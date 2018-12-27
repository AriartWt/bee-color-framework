<?php
namespace wfw\engine\package\miel\lib\helper;

use wfw\engine\core\router\IRouter;
use wfw\engine\package\miel\model\IMielModel;

/**
 * Permet de créer facielement les attributs HTML nécessaires au fonctionnement du module miel.
 */
final class MielHelper implements IMielHelper {
	/** @var IMielModel $_pot */
	private $_pot;
	/** @var IRouter $_router */
	private $_router;

	/**
	 * MielHelper constructor.
	 *
	 * @param IMielModel $pot Pot à gérer
	 * @param IRouter    $router
	 */
	public function __construct(IMielModel $pot, IRouter $router) {
		$this->_pot = $pot;
		$this->_router = $router;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getHTMLForKey(string $key):string{
		$params = $this->_pot->getParams($key);
		return 'data-miel_modifiable="true" '
			.'data-miel_params=\''.json_encode($params).'\' '
			.'data-miel_key=\''.$key.'\''
			.(($params["module"]??"" === "medias")
				? "data-miel-medias_data='".$this->_pot->get($key)."'"
				: '');
	}
}