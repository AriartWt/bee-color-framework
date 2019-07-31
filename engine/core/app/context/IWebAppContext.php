<?php
namespace wfw\engine\core\app\context;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHook;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\response\IResponseRouter;
use wfw\engine\core\action\IActionRouter;
use wfw\engine\core\renderer\IRenderer;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\security\IAccessControlCenter;
use wfw\engine\core\session\ISession;
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\view\ILayoutResolver;

/**
 * Contexte de l'application web.
 */
interface IWebAppContext {
	public const CONF="CONF";
	public const CONF_FILES="CONF_FILES";
	public const TRANSLATOR = "TRANSLATOR";
	public const DOMAIN_EVENT_LISTENERS = "DOMAIN_EVENT_LISTENERS";
	public const COMMAND_HANDLERS = "COMMAND_HANDLERS";
	public const ROUTER="ROUTER";
	public const VIEWS="VIEWS";
	public const LANGS="LANGS";
	public const MODULES="MODULES";
	public const ACCESS_RULES="ACCESS_RULES";
	public const COMMAND_RULES="COMMAND_RULES";
	public const QUERY_RULES="QUERY_RULES";
	public const HOOKS = "HOOKS";

	public const CACHE_KEYS = [
		self::CONF => "WFW/WebApp/Confs",
		self::ROUTER => "WFW/WebApp/Router",
		self::TRANSLATOR => "WFW/WebApp/Translator",
		self::DOMAIN_EVENT_LISTENERS => "WFW/WebApp/DomainEventListeners",
		self::VIEWS => "WFW/WebApp/Views",
		self::LANGS => "WFW/WebApp/Langs",
		self::COMMAND_HANDLERS => "WFW/WebApp/CommandHandlers",
		self::MODULES => "WFW/WebApp/Modules",
		self::ACCESS_RULES => "WFW/WebApp/AccessRules",
		self::COMMAND_RULES => "WFW/WebApp/CommandRules",
		self::QUERY_RULES => "WFW/WebApp/QueryRules",
		self::HOOKS => "WFW/WebApp/Hooks",
		self::CONF_FILES => "WFW/WebApp/ConfFiles"
	];

	/**
	 * @return ICacheSystem Système de cache de l'application.
	 */
	public function getCacheSystem():ICacheSystem;

	/**
	 * @return ISession Session associée à l'utilisateur courant.
	 */
	public function getSession(): ISession;

	/**
	 * @return IRouter Router permettant de formatter les URL et de mapper une requête à
	 *                        une action.
	 */
	public function getRouter():IRouter;

	/**
	 * @return IActionRouter Permet de router une action vers son handler.
	 */
	public function getActionRouter():IActionRouter;

	/**
	 * @return IResponseRouter Permet de router une réponse vers son handler.
	 */
	public function getResponseRouter():IResponseRouter;

	/**
	 * @return IConf Configuration de l'application.
	 */
	public function getConf():IConf;

	/**
	 * @return IRequest Requête courante
	 */
	public function getRequest():IRequest;

	/**
	 * @return IRenderer Renderer de vues
	 */
	public function getRenderer():IRenderer;

	/**
	 * @return ILayoutResolver Resolver de layout
	 */
	public function getLayoutResolver():ILayoutResolver;

	/**
	 * @return IAccessControlCenter Retourne le centre de contrôle des accés.
	 */
	public function getAccessControlCenter():IAccessControlCenter;

	/**
	 * @return INotifier Notifier
	 */
	public function getNotifier():INotifier;

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public function getTranslator():ITranslator;

	/**
	 * @return IAction Action correspondant à la requête courante.
	 */
	public function getAction():IAction;

	/**
	 * @return IActionHook Hook.
	 */
	public function getActionHook():IActionHook;

	/**
	 * Called by the app just before closing
	 */
	public function close();
}