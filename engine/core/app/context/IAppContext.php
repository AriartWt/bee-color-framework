<?php

namespace wfw\engine\core\app\context;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\IAccessControlCenter;

/**
 * wfw general app context interface
 */
interface IAppContext {
	public const CONF="WFW/WebApp/Confs";
	public const TRANSLATOR = "WFW/WebApp/Translator";

	/**
	 * @return ICacheSystem Système de cache de l'application.
	 */
	public function getCacheSystem():ICacheSystem;

	/**
	 * @return IConf Configuration de l'application.
	 */
	public function getConf():IConf;

	/**
	 * @return IAccessControlCenter Retourne le centre de contrôle des accés.
	 */
	public function getAccessControlCenter():IAccessControlCenter;

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public function getTranslator():ITranslator;

	/**
	 * Called by the app just before closing
	 */
	public function close();
}