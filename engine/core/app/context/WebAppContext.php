<?php
namespace wfw\engine\core\app\context;

use Dice\Dice;
use SessionHandlerInterface;
use wfw\engine\core\action\ActionHandlerFactory;
use wfw\engine\core\action\ActionHookFactory;
use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHook;
use wfw\engine\core\action\IActionHookFactory;
use wfw\engine\core\action\MultiHook;
use wfw\engine\core\conf\WFW;
use wfw\engine\core\notifier\FlashNotifier;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\printer\IPrinter;
use wfw\engine\core\notifier\printer\SimpleHTMLPrinter;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\request\RequestData;
use wfw\engine\core\response\ResponseRouter;
use wfw\engine\core\action\ActionRouter;
use wfw\engine\core\action\IActionHandlerFactory;
use wfw\engine\core\response\IResponseRouter;
use wfw\engine\core\action\IActionRouter;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\renderer\IRenderer;
use wfw\engine\core\renderer\Renderer;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\response\IResponseHandlerFactory;
use wfw\engine\core\response\ResponseHandlerFactory;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\request\Request;
use wfw\engine\core\router\Router;
use wfw\engine\core\security\AccessControlCenter;
use wfw\engine\core\security\AccessRuleFactory;
use wfw\engine\core\security\IAccessControlCenter;
use wfw\engine\core\security\IAccessRuleFactory;
use wfw\engine\core\session\handlers\PHPSessionHandler;
use wfw\engine\core\session\ISession;
use wfw\engine\core\view\ILayoutFactory;
use wfw\engine\core\view\ILayoutResolver;
use wfw\engine\core\view\IViewFactory;
use wfw\engine\core\view\LayoutFactory;
use wfw\engine\core\view\LayoutResolver;
use wfw\engine\core\view\ViewFactory;
use wfw\engine\lib\HTML\resources\css\CSSManager;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\lib\HTML\resources\js\JsScriptManager;
use wfw\engine\package\general\handlers\response\AjaxHandler;
use wfw\engine\package\general\handlers\response\ErrorHandler;

/**
 * Contexte de production
 */
class WebAppContext extends BaseAppContext implements IWebAppContext {
	/** @var Router $_router */
	private $_router;
	/** @var IAction $_action */
	private $_action;
	/** @var IConf $_conf */
	private $_conf;

	/**
	 * ProdContext constructor.
	 *
	 * @param string      $defaultLayoutClass Layout par défaut à utiliser
	 * @param null|string $errorViewPath      Chemin d'accés à la vue d'erreur par défaut
	 * @param null|String $ajaxViewPath       Chemin d'accés à la vue ajax par défaut
	 * @param array       $connections        Connexions d'urls
	 * @param array       $langs              Langues disponibles
	 * @param array       $securityRules      3 indxes : access, command, query
	 * @param array       $hooks              Liste des hooks
	 * @param array       $diceRules          Regles à ajouter à Dice
	 * @param array       $globals            Contient la variables globales de php aux index _GET,_POST,_FILES,_SERVER
	 * @param null|string $projectName        Nom du projet. Sert de namespace pour les clés du cache.
	 * @param array       $cacheRule
	 */
	public function __construct(
		string $defaultLayoutClass,
		?string $errorViewPath = null,
		?string $ajaxViewPath = null,
		array $connections = [],
		?array $langs = [],
		array $securityRules = [],
		?array $hooks = null,
		array $diceRules = [],
		array $globals = [],
		?string $projectName = null,
		array $cacheRule = []
	){
		parent::__construct(
			$langs,
			$securityRules["command"] ?? [],
			$securityRules["query"] ?? [],
			[],
			null,
			$cacheRule
		);

		$this->loadModules();
		$accessRules = $this->getAccessRules($securityRules["access"] ?? null);
		$hooks =$this->getHooks($hooks);

		$this->_conf = $conf = $this->getConf();

		$cache = $this->getCacheSystem();
		if(!$cache->contains(self::ROUTER)){
			$this->addDiceRules([
				IRouter::class => [
					'instanceOf' => Router::class,
					'shared' => true,
					'constructParams' => [
						array_merge(
							$connections,
							$conf->getArray("server/router/connections") ?? []
						),
						array_unique(
							array_merge(
								$langs,
								$conf->getArray("server/language/availables") ?? []
							)
						),
						null
					]
				]
			]);
			$this->_router = $router = $this->create(IRouter::class);
		}else{
			$this->_router = $router = $cache->get(self::ROUTER);
			$this->addDiceRules([
				'*' => [
					'substitutions' => [
						IRouter::class => [Dice::INSTANCE => function() use($router){ return $router; }]
					]
				]
			]);
		}

		$this->addDiceRules([
			IActionHandlerFactory::class => [
				'instanceOf' => ActionHandlerFactory::class,
				'shared' => true
			],
			IResponseHandlerFactory::class => [
				'instanceOf' => ResponseHandlerFactory::class,
				'shared' => true
			],
			ILayoutFactory::class => [
				'instanceOf' => LayoutFactory::class,
				'shared' => true
			],
			IAccessRuleFactory::class => [
				'instanceOf' => AccessRuleFactory::class,
				'shared' => true
			],
			IViewFactory::class => [
				'instanceOf' => ViewFactory::class,
				'shared' => true
			],
			IActionHookFactory::class=>[
				'instanceOf' => ActionHookFactory::class,
				'shared' => true
			],
			IActionHook::class => [
				'instanceOf' => MultiHook::class,
				'shared' => true,
				'constructParams' => [ $hooks ]
			],
			SessionHandlerInterface::class => [ 'instanceOf' => PHPSessionHandler::class ],
			IRequestData::class => [
				'instanceOf' => RequestData::class,
				'shared' => true,
				'constructParams' => [
					$globals["_GET"] ?? $_GET,
					$globals["_POST"] ?? $_POST,
					$globals["_FILES"] ?? $_FILES
				]
			],
			IRequest::class => [
				'instanceOf' => Request::class,
				'shared' => true,
				'constructParams' => [
					$globals["_SERVER"] ?? $_SERVER
				]
			],
			IActionRouter::class => [ 'instanceOf' => ActionRouter::class, 'shared' => true],
			IRenderer::class => [ 'instanceOf' => Renderer::class, 'shared' => true ],
			IResponseRouter::class => ['instanceOf' => ResponseRouter::class, 'shared' => true ],
			ILayoutResolver::class => [
				'instanceOf' => LayoutResolver::class,
				'constructParams' => [ $defaultLayoutClass ],
				'shared' => true
			],
			ErrorHandler::class => [ 'constructParams' => [ $errorViewPath ] ],
			AjaxHandler::class => [ 'constructParams' => [ $ajaxViewPath ] ],
			IAccessControlCenter::class => [
				'instanceOf' => AccessControlCenter::class,
				'constructParams' => [ $accessRules ],
				'shared' => true
			],
			INotifier::class => [ 'instanceOf' => FlashNotifier::class, 'shared' => true ],
			IPrinter::class => [ 'instanceOf' => SimpleHTMLPrinter::class, 'shared' => true ],
			ICSSManager::class => [ 'instanceOf' => CSSManager::class, 'shared' => true ],
			IJsScriptManager::class => [ 'instanceOf' => JsScriptManager::class, 'shared' => true ],
		]);
		$this->_action = $action = $this->getRouter()->parse($this->getRequest());
		$this->addDiceRules([
			'*' => [
				'substitutions' => [
					IAction::class => [Dice::INSTANCE => function() use($action){ return $action; }]
				]
			]
		]);
		$this->getTranslator()->changeCurrentLanguage($action->getLang());
		$this->addDiceRules($diceRules);
	}

	/**
	 * @param array|null $access
	 * @return array
	 */
	protected function getAccessRules(?array $access=null):array{
		$rules = $this->getCacheSystem()->get(self::ACCESS_RULES);
		if(is_null($rules)){
			$rules = WFW::accessPolicy(!empty($access) ? $access : null);
			$this->getCacheSystem()->set(self::ACCESS_RULES,$rules);
		}
		return $rules;
	}

	/**
	 * @param array|null $hooks
	 * @return array
	 */
	protected function getHooks(?array $hooks=null):array{
		$hooksPolicy = $this->getCacheSystem()->get(self::HOOKS);
		if(is_null($hooksPolicy)){
			$hooksPolicy = WFW::hooksPolicy(!empty($hooks) ? $hooks : null);
			$this->getCacheSystem()->set(self::HOOKS,$hooksPolicy);
		}
		return $hooksPolicy;
	}

	/**
	 * @return ISession Session associée à l'utilisateur courant.
	 */
	public final function getSession(): ISession{
		/** @var ISession $session */
		$session = $this->create(ISession::class);
		return $session;
	}

	/**
	 * @return IRouter Router permettant de formatter les URL et de mapper une requête à
	 *                        une action.
	 */
	public final function getRouter(): IRouter{ return $this->_router; }

	/**
	 * @return IActionRouter Permet de router une action vers son handler.
	 */
	public final function getActionRouter(): IActionRouter{
		/** @var IActionRouter $router */
		$router = $this->create(IActionRouter::class);
		return $router;
	}

	/**
	 * @return IResponseRouter Permet de router une réponse vers son handler.
	 */
	public final function getResponseRouter(): IResponseRouter{
		/** @var IResponseRouter $router */
		$router = $this->create(IResponseRouter::class);
		return $router;
	}

	/**
	 * @return IRequest Requête courante
	 */
	public final function getRequest(): IRequest{
		/** @var IRequest $request */
		$request = $this->create(IRequest::class);
		return $request;
	}

	/**
	 * @return IRenderer Renderer de vues
	 */
	public final function getRenderer(): IRenderer{
		/** @var IRenderer $renderer */
		$renderer = $this->create(IRenderer::class);
		return $renderer;
	}

	/**
	 * @return ILayoutResolver
	 */
	public final function getLayoutResolver():ILayoutResolver{
		/** @var ILayoutResolver $resolver */
		$resolver = $this->create(ILayoutResolver::class);
		return $resolver;
	}

	/**
	 * @return IAccessControlCenter Retourne le centre de contrôle des accés.
	 */
	public final function getAccessControlCenter(): IAccessControlCenter{
		/** @var IAccessControlCenter $accessControlCenter */
		$accessControlCenter = $this->create(IAccessControlCenter::class);
		return $accessControlCenter;
	}

	/**
	 * @return IAction Action correspondant à la requête courante.
	 */
	public final function getAction(): IAction { return $this->_action; }

	/**
	 * @return IActionHook Hook.
	 */
	public final function getActionHook(): IActionHook {
		/** @var IActionHook $actionHook */
		$actionHook = $this->create(IActionHook::class);
		return $actionHook;
	}

	/**
	 * Called by the app just before closing
	 */
	public function close() {
		parent::close();
		$cache = $this->getCacheSystem();
		$cache->set(self::ROUTER,$this->getRouter());
	}
}