<?php

namespace wfw\engine\core\app\context;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\notifier\INotifier;

/**
 * wfw general app context interface
 */
interface IAppContext {
	public const CONF="WFW/WebApp/Confs";
	public const TRANSLATOR = "WFW/WebApp/Translator";
	public const DOMAIN_EVENT_LISTENERS = "WFW/WebApp/DomainEventListeners";
	public const LANGS = "WFW/WebApp/Langs";
	public const COMMAND_HANDLERS = "WFW/WebApp/CommandHandlers";
	public const MODULES = "WFW/WebApp/Modules";
	public const COMMAND_RULES = "WFW/WebApp/CommandRules";
	public const QUERY_RULES = "WFW/WebApp/QueryRules";
	public const CONF_FILES = "WFW/WebApp/ConfFiles";

	/**
	 * @return ICacheSystem Système de cache de l'application.
	 */
	public function getCacheSystem():ICacheSystem;

	/**
	 * @return IConf Configuration de l'application.
	 */
	public function getConf():IConf;

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public function getTranslator():ITranslator;

	/**
	 * @return INotifier Notifier
	 */
	public function getNotifier(): INotifier;

	/**
	 * Called by the app just before closing
	 */
	public function close();
}