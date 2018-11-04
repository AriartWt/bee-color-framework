<?php
namespace wfw\engine\package\users\domain\settings;

use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\NoneConfIOAdapter;

/**
 *  Configurations utilisateur en mémoire
 */
class InMemoryUserSettings extends UserSettings {
	public function __construct() {
		$conf = new FileBasedConf("", new NoneConfIOAdapter());
		$conf->setAutoSaveMode(false);

		parent::__construct($conf);
	}
}