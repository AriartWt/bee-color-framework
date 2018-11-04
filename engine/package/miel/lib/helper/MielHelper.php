<?php
namespace wfw\engine\package\miel\lib\helper;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\package\miel\model\IMielModel;

/**
 * Permet de créer facielement les attributs HTML nécessaires au fonctionnement du module miel.
 */
final class MielHelper implements IMielHelper {
	/** @var IMielModel $_pot */
	private $_pot;
	/** @var IRouter $_router */
	private $_router;
	/** @var bool $_medias */
	private $_medias;

	/**
	 * MielHelper constructor.
	 *
	 * @param IMielModel $pot Pot à gérer
	 * @param IRouter    $router
	 * @param bool       $medias
	 */
	public function __construct(IMielModel $pot, IRouter $router,bool $medias = false) {
		$this->_pot = $pot;
		$this->_router = $router;
		$this->_medias = $medias;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getHTMLForKey(string $key):string{
		return 'data-miel_modifiable="true" '
			.'data-miel_params=\''.json_encode($this->_pot->getParams($key)).'\' '
			.'data-miel_key=\''.$key.'\'';
	}
}