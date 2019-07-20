<?php

namespace wfw\engine\core\app\context;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\IAccessControlCenter;

class BaseAppContext implements IAppContext {

	public function __construct(
		array $langs = [],
		array $accessRules = [],
		array $diceRules = [],
		?array $confFiles = null,
		?string $projectName = null
	){
		if(!$projectName) $projectName = dirname(__DIR__,4);
	}

	/**
	 * @return ICacheSystem Système de cache de l'application.
	 */
	public function getCacheSystem(): ICacheSystem {
		// TODO: Implement getCacheSystem() method.
	}

	/**
	 * @return IConf Configuration de l'application.
	 */
	public function getConf(): IConf {
		// TODO: Implement getConf() method.
	}

	/**
	 * @return IAccessControlCenter Retourne le centre de contrôle des accés.
	 */
	public function getAccessControlCenter(): IAccessControlCenter {
		// TODO: Implement getAccessControlCenter() method.
	}

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public function getTranslator(): ITranslator {
		// TODO: Implement getTranslator() method.
	}

	/**
	 * Called by the app just before closing
	 */
	public function close() {
		// TODO: Implement close() method.
	}
}