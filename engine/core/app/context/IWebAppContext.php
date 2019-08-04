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
interface IWebAppContext extends IAppContext {
	public const HOOKS="WFW/WebApp/Hooks";
	public const VIEWS="WFW/WebApp/Views";
	public const ACCESS_RULES="WFW/WebApp/AccessRules";
	public const ROUTER = "WFW/WebApp/Router";

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
	 * @return IAction Action correspondant à la requête courante.
	 */
	public function getAction():IAction;

	/**
	 * @return IActionHook Hook.
	 */
	public function getActionHook():IActionHook;
}