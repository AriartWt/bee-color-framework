<?php
namespace wfw\engine\core\app\context;

use Dice\Dice;
use PHPMailer\PHPMailer\PHPMailer;
use SessionHandlerInterface;
use wfw\daemons\modelSupervisor\client\IMSInstanceAddrResolver;
use wfw\daemons\modelSupervisor\client\MSInstanceAddrResolver;
use wfw\engine\core\action\ActionHandlerFactory;
use wfw\engine\core\action\ActionHookFactory;
use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHook;
use wfw\engine\core\action\IActionHookFactory;
use wfw\engine\core\action\MultiHook;
use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\app\factory\DiceBasedFactory;
use wfw\engine\core\app\factory\IGenericAppFactory;
use wfw\engine\core\command\CommandHandlerFactory;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\command\ICommandHandlerFactory;
use wfw\engine\core\command\ICommandInflector;
use wfw\engine\core\command\inflectors\NamespaceBasedInflector;
use wfw\engine\core\command\SynchroneCommandBus;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\MSServerWriterAccess;
use wfw\engine\core\data\DBAccess\SQLDB\IDBAccess;
use wfw\engine\core\data\DBAccess\SQLDB\MySQLDBAccess;
use wfw\engine\core\domain\events\DomainEventListenerFactory;
use wfw\engine\core\domain\events\IDomainEventDispatcher;
use wfw\engine\core\domain\events\IDomainEventListenerFactory;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\domain\events\observers\ConfBasedDomainEventObserver;
use wfw\engine\core\domain\events\observers\DomainEventObserver;
use wfw\engine\core\domain\events\store\DBBasedEventStore;
use wfw\engine\core\domain\events\store\IEventStore;
use wfw\engine\core\domain\repository\AggregateRootRepository;
use wfw\engine\core\domain\repository\IAggregateRootRepository;
use wfw\engine\core\lang\ILanguageLoader;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\lang\LanguageLoader;
use wfw\engine\core\lang\Translator;
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
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\cache\systems\APCUBasedCache;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\conf\io\adapters\errors\ConfFileFailure;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\core\errors\handlers\DefaultErrorHandler;
use wfw\engine\core\errors\IErrorHandler;
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
use wfw\engine\core\security\data\sanitizer\HTMLPurifierBasedSanitizer;
use wfw\engine\core\security\data\sanitizer\IHTMLSanitizer;
use wfw\engine\core\security\IAccessControlCenter;
use wfw\engine\core\security\IAccessRuleFactory;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\rules\ValidToken;
use wfw\engine\core\session\handlers\PHPSessionHandler;
use wfw\engine\core\session\ISession;
use wfw\engine\core\session\Session;
use wfw\engine\core\view\ILayoutFactory;
use wfw\engine\core\view\ILayoutResolver;
use wfw\engine\core\view\IViewFactory;
use wfw\engine\core\view\LayoutFactory;
use wfw\engine\core\view\LayoutResolver;
use wfw\engine\core\view\ViewFactory;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\compressor\IStringCompressor;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\lib\data\string\json\JSONEncoder;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\HTML\resources\css\CSSManager;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\lib\HTML\resources\js\JsScriptManager;
use wfw\engine\lib\network\mail\IMailFactory;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\MailFactory;
use wfw\engine\lib\network\mail\providers\PHPMailerProvider;
use wfw\engine\package\contact\data\model\ContactModelAccess;
use wfw\engine\package\contact\data\model\IContactModelAccess;
use wfw\engine\package\contact\domain\repository\ContactRepository;
use wfw\engine\package\contact\domain\repository\IContactRepository;
use wfw\engine\package\contact\security\ContactAccessControlPolicies;
use wfw\engine\package\general\handlers\response\AjaxHandler;
use wfw\engine\package\general\handlers\response\ErrorHandler;
use wfw\engine\package\general\security\GeneralAccessControlPolicies;
use wfw\engine\package\miel\security\MielAccessControlPolicies;
use wfw\engine\package\news\data\model\ArticleModelAccess;
use wfw\engine\package\news\data\model\IArticleModelAccess;
use wfw\engine\package\news\domain\repository\ArticleRepository;
use wfw\engine\package\news\domain\repository\IArticleRepository;
use wfw\engine\package\news\security\NewsAccessControlPolicies;
use wfw\engine\package\uploader\security\UploaderAccessControlPolicies;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\data\model\UserModelAccess;
use wfw\engine\package\users\domain\repository\IUserRepository;
use wfw\engine\package\users\domain\repository\UserRepository;
use wfw\engine\package\users\lib\confirmationCode\IUserConfirmationCodeGenerator;
use wfw\engine\package\users\lib\confirmationCode\UUIDBasedUserConfirmationCodeGenerator;
use wfw\engine\package\users\lib\mail\IUserMailChangedMail;
use wfw\engine\package\users\lib\mail\IUserRegisteredMail;
use wfw\engine\package\users\lib\mail\IUserResetPasswordMail;
use wfw\engine\package\users\lib\mail\UserMailChangedMail;
use wfw\engine\package\users\lib\mail\UserRegisteredMail;
use wfw\engine\package\users\lib\mail\UserResetPasswordMail;
use wfw\engine\package\users\security\UsersAccessControlPolicies;

/**
 * Contexte de production
 */
class DefaultContext implements IWebAppContext {
	/** @var Dice $_dice */
	private $_dice;
	/** @var IConf $_conf */
	private $_conf;
	/** @var Router $_router */
	private $_router;
	/** @var ITranslator $_translator */
	private $_translator;
	/** @var IAction $_action */
	private $_action;

	/**
	 * ProdContext constructor.
	 *
	 * @param string      $defaultLayoutClass Layout par défaut à utiliser
	 * @param null|string $errorViewPath      Chemin d'accés à la vue d'erreur par défaut
	 * @param null|String $ajaxViewPath       Chemin d'accés à la vue ajax par défaut
	 * @param array       $connections        Connexions d'urls
	 * @param array       $langs              Langues disponibles
	 * @param array       $accessRules        Liste des régles d'accés
	 * @param array       $hooks              Liste des hooks
	 * @param array       $diceRules          Regles à ajouter à Dice
	 * @param array       $globals            Contient la variables globales de php aux index _GET,_POST,_FILES,_SERVER
	 * @param array       $confFiles          Liste des fichiers de configuration à charger
	 * @param null|string $projectName        Nom du projet. Sert de namespace pour les clés du cache.
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $defaultLayoutClass,
		?string $errorViewPath = null,
		?string $ajaxViewPath = null,
		array $connections = [],
		array $langs = [],
		array $accessRules = [],
		array $hooks = [],
		array $diceRules = [],
		array $globals = [],
		?array $confFiles = null,
		?string $projectName = null
	){
		if(is_null($projectName))
			$projectName = dirname(__DIR__,4);
		$genericFactory = new DiceBasedFactory($this->_dice = $dice = new Dice());
		$this->getErrorHandler()->handle();

		if(count($accessRules) === 0) $accessRules = [
			RequireAuthentification::class => [
				array_merge(
					ContactAccessControlPolicies::REQUIRE_AUTH,
					MielAccessControlPolicies::REQUIRE_AUTH,
					NewsAccessControlPolicies::REQUIRE_AUTH,
					UploaderAccessControlPolicies::REQUIRE_AUTH,
					UsersAccessControlPolicies::REQUIRE_AUTH
				),
				true
			],
			ValidToken::class => []
		];
		if(count($hooks) === 0) $hooks = [
			NotFoundHook::class => [
				array_merge(
					UsersAccessControlPolicies::RESTRICT_MODE,
					GeneralAccessControlPolicies::DISABLE_ZIP_CODES
				)
			]
		];
		$this->_dice->addRules([
			ICacheSystem::class => [
				'instanceOf' => APCUBasedCache::class,
				'shared' => true ,
				'constructParams'=>[$projectName]
			]
		]);

		$this->_conf = $conf = $this->initConfs($confFiles ?? [
			dirname(__DIR__,3)."/config/conf.json",
			dirname(__DIR__,4)."/site/config/conf.json"
		]);

		//Trying to dynamicaly resolve the msserver socket addr. If MSServerPool is unavailable,
		//we maked the assumption that the sockets are in their default locations.
		$msinstanceAddr = $this->getMSServerAddr(
			$msserverAddr = $this->_conf->getString("server/msserver/addr")
				?? "/srv/wfw/global/daemons/modelSupervisor/data/ModelSupervisor.socket"
		) ?? "/srv/wfw/global/daemons/modelSupervisor/data/"
			.basename(dirname(__DIR__,4))
			."/".basename(dirname(__DIR__,4)).".socket";

		$this->_dice->addRules([
			ILanguageLoader::class => [
				'instanceOf' => LanguageLoader::class,
				'shared' => true,
				'constructParams' => [ $conf->getString("app/ui/lang/replacement_pattern") ]
			],
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
							array_keys($langs),
							$conf->getArray("server/language/availables") ?? []
						)
					),
					null
				]
			]
		]);
		$cache = $this->getCacheSystem();
		if(!$cache->contains(self::CACHE_KEYS[self::ROUTER])){
			$this->_router = $router = $this->_dice->create(IRouter::class);
		}else $this->_router = $router = $cache->get(self::CACHE_KEYS[self::ROUTER]);

		$this->_translator = $translator =  $this->initTranslator($langs,null);
		$instance = $this;

		$this->_dice->addRules([
			'*' => [
				'substitutions' => [
					IConf::class => [ Dice::INSTANCE => function() use ($conf){ return $conf; } ],
					/*IRouter::class => [ Dice::INSTANCE => function() use($router){return $router;}],*/
					IMailProvider::class => [
						Dice::INSTANCE => function() use($instance,$conf){
							return $this->initMailProvider($conf);
						},
						"shared" => true
					],
					ITranslator::class => [
						Dice::INSTANCE => function() use($translator){ return $translator; }
					],
					IGenericAppFactory::class => [
						Dice::INSTANCE => function() use ($genericFactory) {return $genericFactory;},
						"shared"=>true
					]
				]
			],
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
			IMailFactory::class => [
				'instanceOf' => MailFactory::class,
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
			ICommandBus::class => [ 'instanceOf' => SynchroneCommandBus::class, 'shared' => true ],
			ICommandInflector::class => [
				'instanceOf' => NamespaceBasedInflector::class, 'shared' => true
			],
			ICommandHandlerFactory::class => [
				'instanceOf' => CommandHandlerFactory::class,'shared'=>true
			],
			IEventStore::class => [ 'instanceOf' => DBBasedEventStore::class ],
			IDBAccess::class => [
				'instanceOf' => MySQLDBAccess::class,
				'constructParams' => [
					$conf->getString('server/databases/default/host'),
					$conf->getString('server/databases/default/database'),
					$conf->getString('server/databases/default/login'),
					$conf->getString('server/databases/default/password')
				],
				'shared'=>true
			],
			IMSInstanceAddrResolver::class => [
				"instenceOf" => MSInstanceAddrResolver::class,
				"constructParams" => [ $msserverAddr ]
			],
			IMSServerAccess::class => [
				'instanceOf' => MSServerWriterAccess::class,
				'constructParams' => [
					$msinstanceAddr,
					$conf->getString('server/msserver/login'),
					$conf->getString('server/msserver/password')
				],
				'shared' => true
			],
			IDomainEventDispatcher::class => [
				'instanceOf' => ConfBasedDomainEventObserver::class,
				'constructParams' => [ $this->getDomainEventListeners() ]
			],
			IDomainEventObserver::class => [
				'instanceOf' => DomainEventObserver::class,
				'shared' => true
			],
			IDomainEventListenerFactory::class => [
				'instanceOf' => DomainEventListenerFactory::class,
				'shared' => true
			],
			ISession::class => [
				'instanceOf' => Session::class,
				'constructParams' => [ "user", $conf->getString("server/sessions/timeout") ],
				'shared' => true
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
			IUserModelAccess::class => [ 'instanceOf' => UserModelAccess::class, 'shared' => true ],
			IArticleRepository::class => [ 'instanceOf' => ArticleRepository::class, 'shared' => true],
			IAggregateRootRepository::class => ['instanceOf' => AggregateRootRepository::class, 'shared'=>true],
			LightSerializer::class => ['substitutions' => [ ISerializer::class => PHPSerializer::class ]],
			ISerializer::class => ['instanceOf'=>LightSerializer::class, 'shared'=>true],
			IStringCompressor::class => ['instanceOf'=>GZCompressor::class, 'shared'=>true],
			IJSONEncoder::class => ["instanceOf"=>JSONEncoder::class, 'shared'=>true],
			IArticleModelAccess::class => ['instanceOf'=>ArticleModelAccess::class, 'shared'=>true],
			IHTMLSanitizer::class => ['instanceOf'=>HTMLPurifierBasedSanitizer::class, 'shared'=>true],
			IUserConfirmationCodeGenerator::class => [ 'instanceOf'=>UUIDBasedUserConfirmationCodeGenerator::class,'shared'=>true],
			IUserRegisteredMail::class => [ 'instanceOf'=>UserRegisteredMail::class],
			IUserMailChangedMail::class => [ 'instanceOf'=>UserMailChangedMail::class],
			IUserResetPasswordMail::class => [ 'instanceOf'=>UserResetPasswordMail::class],
			IUserRepository::class => [ 'instanceOf'=>UserRepository::class,'shared'=>true],
			IContactRepository::class => ['instanceOf'=>ContactRepository::class,'shared'=>true],
			IContactModelAccess::class => ['instanceOf'=>ContactModelAccess::class,'shared'=>true]
		]);
		$this->_action = $action = $this->getRouter()->parse($this->getRequest());
		$this->_dice->addRules([
			'*' => [
				'substitutions' => [
					IAction::class => [Dice::INSTANCE => function() use($action){ return $action; }]
				]
			]
		]);
		$this->_translator->changeCurrentLanguage($action->getLang());
		$this->_dice->addRules($diceRules);
	}

	/**
	 * @param string $msserverAddr Resolver socket
	 * @return string|null MSServer socket instance
	 */
	protected function getMSServerAddr(string $msserverAddr):?string{
		$cache = $this->getCacheSystem();
		if($cache->contains("server/msserver/addr") && is_string($res = $cache->get("server/msserver/addr")))
			return $res;
		else{
			if(strpos($msserverAddr,"/")!==0)
				$msserverAddr = dirname(__DIR__,4)."/$msserverAddr";
			try{
				$msinstanceAddr = (new MSInstanceAddrResolver($msserverAddr))->find(
					$this->_conf->getString('server/msserver/db')
				);
				$cache->set("server/msserver/addr",$msinstanceAddr);
				return $msinstanceAddr;
			}catch(\Exception | \Error $e){
				return null;
			}
		}
	}
	
	/**
	 * @param IConf $conf Configurations à utiliser
	 * @return IMailProvider
	 */
	protected function initMailProvider(IConf $conf):IMailProvider{
		$mail = new PHPMailer(true);
		
		//Setup smtp
		$smtpKey = "server/mailer/smtp";
		if($conf->getObject($smtpKey)){
			$mail->SMTPDebug = 2; // Enable verbose debug output
			$mail->isSMTP();
			$mail->Host = $conf->getString("$smtpKey/host");
			if($conf->getObject("$smtpKey/auth")){
				$mail->SMTPAuth = true;
				$mail->Username = $conf->getString("$smtpKey/auth/login");
				$mail->Password = $conf->getString("$smtpKey/auth/password");
				$mail->SMTPSecure = $conf->getString("$smtpKey/auth/secure");
				$mail->Port = $conf->getString(("$smtpKey/auth/port"));
			}
		}
		
		//Setup DKIM
		$dkimKey = "server/mailer/dkim";
		if($conf->getObject($dkimKey)){
			$mail->DKIM_domain = $conf->getString("$dkimKey/domain");
			$mail->DKIM_private = $conf->getString("$dkimKey/private");
			$mail->DKIM_selector = $conf->getString("$dkimKey/selector");
			$mail->DKIM_passphrase = $conf->getString("$dkimKey/passphrase");
			if($conf->getString("$dkimKey/identity")){
				$mail->DKIM_identity = $conf->getString("$dkimKey/identity");
			}
		}
		
		return new PHPMailerProvider(
			$mail,
			$conf->getString("server/mailer/dkim/identity")
			?? (!is_null($conf->getObject("server/mailer/dkim")) ? true : false )
		);
	}

	/**
	 * Ajoute un ensemble de règles à dice.
	 * @param array $rules Régles à ajouter
	 */
	protected final function addDiceRules(array $rules){
		$this->_dice->addRules($rules);
	}

	/**
	 * @param array $confFiles Fichiers de configurations à charger
	 * @return IConf Configurations
	 */
	protected function initConfs(array $confFiles):IConf{
		$conf = $this->getCacheSystem()->get(self::CACHE_KEYS[self::CONF]);
		if(is_null($conf)){
			if(count($confFiles)===0) throw new \InvalidArgumentException(
				"Can't create empty configurations ! At least one valide conf file must be given !"
			);
			$jsonAdapter = new JSONConfIOAdapter();
			$conf = new FileBasedConf(array_shift($confFiles),$jsonAdapter);
			foreach($confFiles as $file){
				try{
					$conf->merge(new FileBasedConf($file,$jsonAdapter));
				}catch(ConfFileFailure $e){}
			}
			$this->getCacheSystem()->set(self::CACHE_KEYS[self::CONF],$conf);
			return $conf;
		}else{
			return $conf;
		}
	}

	/**
	 * @param array       $langs   Liste des langues à charger.
	 * @param null|string $default Langue par défaut
	 * @return ITranslator
	 */
	protected function initTranslator(array $langs,?string $default=null):ITranslator{
		$translator = $this->getCacheSystem()->get(self::CACHE_KEYS[self::TRANSLATOR]);
		if(is_null($translator)){
			/** @var ILanguageLoader $loader */
			$loader = $this->_dice->create(ILanguageLoader::class);
			$translator =  new Translator($loader,$langs,$default);
			$this->getCacheSystem()->set(self::CACHE_KEYS[self::TRANSLATOR],$translator);
			return $translator;
		}else{
			return $translator;
		}
	}

	/**
	 * @return array
	 */
	protected function getDomainEventListeners():array{
		$listeners = $this->getCacheSystem()->get(self::CACHE_KEYS[self::DOMAIN_EVENT_LISTENERS]);
		$site = dirname(dirname(dirname(dirname(__DIR__)))).'/site';
		$engine = dirname(dirname(dirname(__DIR__)));
		if(is_null($listeners)){
			if(file_exists("$site/config/load/domain_events.listeners.php"))
				$listeners = require("$site/config/load/domain_events.listeners.php");
			else $listeners = require("$engine/config/default.domain_events.listeners.php");
			$this->getCacheSystem()->set(self::CACHE_KEYS[self::DOMAIN_EVENT_LISTENERS],$listeners);
		}
		return $listeners;
	}

	/**
	 * @return IErrorHandler Gestionnaire d'erreurs.
	 */
	protected final function getErrorHandler(): IErrorHandler{
		/** @var IErrorHandler $handler */
		$handler =  $this->_dice->create(DefaultErrorHandler::class);
		return $handler;
	}

	/**
	 * @return ICacheSystem Système de cache de l'application.
	 */
	public final function getCacheSystem(): ICacheSystem{
		/** @var ICacheSystem $cache */
		$cache = $this->_dice->create(ICacheSystem::class);
		return $cache;
	}

	/**
	 * @return ISession Session associée à l'utilisateur courant.
	 */
	public final function getSession(): ISession{
		/** @var ISession $session */
		$session = $this->_dice->create(ISession::class);
		return $session;
	}

	/**
	 * @return IConf Configuration de l'application.
	 */
	public final function getConf(): IConf{ return $this->_conf; }

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
		$router = $this->_dice->create(IActionRouter::class);
		return $router;
	}

	/**
	 * @return IResponseRouter Permet de router une réponse vers son handler.
	 */
	public final function getResponseRouter(): IResponseRouter{
		/** @var IResponseRouter $router */
		$router = $this->_dice->create(IResponseRouter::class);
		return $router;
	}

	/**
	 * @return IRequest Requête courante
	 */
	public final function getRequest(): IRequest{
		/** @var IRequest $request */
		$request = $this->_dice->create(IRequest::class);
		return $request;
	}

	/**
	 * @return IRenderer Renderer de vues
	 */
	public final function getRenderer(): IRenderer{
		/** @var IRenderer $renderer */
		$renderer = $this->_dice->create(IRenderer::class);
		return $renderer;
	}

	/**
	 * @return ILayoutResolver
	 */
	public final function getLayoutResolver():ILayoutResolver{
		/** @var ILayoutResolver $resolver */
		$resolver = $this->_dice->create(ILayoutResolver::class);
		return $resolver;
	}

	/**
	 * @return IAccessControlCenter Retourne le centre de contrôle des accés.
	 */
	public final function getAccessControlCenter(): IAccessControlCenter{
		/** @var IAccessControlCenter $accessControlCenter */
		$accessControlCenter = $this->_dice->create(IAccessControlCenter::class);
		return $accessControlCenter;
	}

	/**
	 * @return INotifier Notifier
	 */
	public final function getNotifier(): INotifier{
		/** @var INotifier $notifier */
		$notifier = $this->_dice->create(INotifier::class);
		return $notifier;
	}

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public final function getTranslator():ITranslator{ return $this->_translator; }

	/**
	 * @return IAction Action correspondant à la requête courante.
	 */
	public final function getAction(): IAction { return $this->_action; }

	/**
	 * @return IActionHook Hook.
	 */
	public final function getActionHook(): IActionHook {
		/** @var IActionHook $actionHook */
		$actionHook = $this->_dice->create(IActionHook::class);
		return $actionHook;
	}

	/**
	 * Called by the app just before closing
	 */
	public function close() {
		$cache = $this->getCacheSystem();
		$cache->set(self::CACHE_KEYS[self::CONF],$this->getConf());
		$cache->set(self::CACHE_KEYS[self::TRANSLATOR],$this->getTranslator());
		$cache->set(self::CACHE_KEYS[self::ROUTER],$this->getRouter());
	}
}