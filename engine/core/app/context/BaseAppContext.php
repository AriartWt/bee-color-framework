<?php

namespace wfw\engine\core\app\context;

use Dice\Dice;
use PHPMailer\PHPMailer\PHPMailer;
use SessionHandlerInterface;
use wfw\daemons\modelSupervisor\client\IMSInstanceAddrResolver;
use wfw\daemons\modelSupervisor\client\MSInstanceAddrResolver;
use wfw\engine\core\app\factory\DiceBasedFactory;
use wfw\engine\core\app\factory\IGenericAppFactory;
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\cache\systems\APCUBasedCache;
use wfw\engine\core\command\CommandHandlerFactory;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\command\ICommandHandlerFactory;
use wfw\engine\core\command\ICommandInflector;
use wfw\engine\core\command\inflectors\NamespaceBasedInflector;
use wfw\engine\core\command\security\CommandAccessRuleFactory;
use wfw\engine\core\command\security\CommandSecurityCenter;
use wfw\engine\core\command\security\ICommandSecurityCenter;
use wfw\engine\core\command\security\rules\CommandAccessRulesCollector;
use wfw\engine\core\command\SynchroneCommandBus;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\conf\io\adapters\errors\ConfFileFailure;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\core\conf\WFW;
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
use wfw\engine\core\errors\handlers\DefaultErrorHandler;
use wfw\engine\core\errors\IErrorHandler;
use wfw\engine\core\lang\ILanguageLoader;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\lang\LanguageLoader;
use wfw\engine\core\lang\Translator;
use wfw\engine\core\notifier\FlashNotifier;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\printer\IPrinter;
use wfw\engine\core\notifier\printer\JSONPrinter;
use wfw\engine\core\security\data\sanitizer\HTMLPurifierBasedSanitizer;
use wfw\engine\core\security\data\sanitizer\IHTMLSanitizer;
use wfw\engine\core\session\handlers\cli\CLIFileSessionHandler;
use wfw\engine\core\session\ISession;
use wfw\engine\core\session\Session;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\compressor\IStringCompressor;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\lib\data\string\json\JSONEncoder;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\network\mail\IMailFactory;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\MailFactory;
use wfw\engine\lib\network\mail\providers\PHPMailerProvider;
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

/**
 * Shared context between CLI and WEB
 */
class BaseAppContext implements IAppContext {
	/** @var Dice $_dice */
	private $_dice;
	/** @var IConf $_conf */
	private $_conf;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * BaseAppContext constructor.
	 *
	 * @param array       $langs
	 * @param array       $commandRules
	 * @param array       $queryRules
	 * @param array       $diceRules
	 * @param null|string $projectName
	 * @param array       $cacheRule
	 */
	public function __construct(
		array $langs = [],
		array $commandRules = [],
		array $queryRules = [],
		array $diceRules = [],
		?string $projectName = null,
		array $cacheRule = []
	){
		if(!$projectName) $projectName = dirname(__DIR__,4);
		$genericFactory = new DiceBasedFactory($this->_dice = $dice = new Dice());
		$this->getErrorHandler()->handle();

		$this->_dice->addRules(empty($cacheRule) ? [
			ICacheSystem::class => [
				'instanceOf' => APCUBasedCache::class,
				'shared' => true ,
				'constructParams'=>[ $projectName ]
			]
		] : $cacheRule);

		$this->loadModules();
		$commandRules = $this->getCommandRules($commandRules);
		$queryRules = $this->getQueryRules($queryRules);
		$langs = $this->getLangs($langs);

		$this->_conf = $conf = $this->initConfs($this->getConfs());

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
			]
		]);

		$this->_translator = $translator =  $this->initTranslator($langs,null);
		$instance = $this;
		$commandRulesCollector = new CommandAccessRulesCollector(
			new CommandAccessRuleFactory($genericFactory),$commandRules
		);

		$this->_dice->addRules([
			'*' => [
				'substitutions' => [
					IConf::class => [ Dice::INSTANCE => function() use ($conf){ return $conf; } ],
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
			IMailFactory::class => [
				'instanceOf' => MailFactory::class,
				'shared' => true
			],
			ICommandBus::class => [ 'instanceOf' => SynchroneCommandBus::class, 'shared' => true ],
			ICommandInflector::class => [
				'instanceOf' => NamespaceBasedInflector::class, 'shared' => true,
				'constructParams' => [ $this->getCommandHandlers() ]
			],
			ICommandSecurityCenter::class => [
				'instanceOf' => CommandSecurityCenter::class, 'shared'=>true,
				'constructParams' => [ $commandRulesCollector->collect() ]
			],
			ICommandHandlerFactory::class => [
				'instanceOf' => CommandHandlerFactory::class, 'shared'=>true
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
			SessionHandlerInterface::class => [ 'instanceOf' => CLIFileSessionHandler::class ],
			INotifier::class => [ 'instanceOf' => FlashNotifier::class, 'shared' => true ],
			IPrinter::class => [ 'instanceOf' => JSONPrinter::class, 'shared' => true ],
			IUserModelAccess::class => [ 'instanceOf' => UserModelAccess::class, 'shared' => true ],
			IAggregateRootRepository::class => ['instanceOf' => AggregateRootRepository::class, 'shared'=>true],
			LightSerializer::class => ['substitutions' => [ ISerializer::class => PHPSerializer::class ]],
			ISerializer::class => ['instanceOf'=>LightSerializer::class, 'shared'=>true],
			IStringCompressor::class => ['instanceOf'=>GZCompressor::class, 'shared'=>true],
			IJSONEncoder::class => ["instanceOf"=>JSONEncoder::class, 'shared'=>true],
			IHTMLSanitizer::class => ['instanceOf'=>HTMLPurifierBasedSanitizer::class, 'shared'=>true],
			IUserConfirmationCodeGenerator::class => [ 'instanceOf'=>UUIDBasedUserConfirmationCodeGenerator::class,'shared'=>true],
			IUserRegisteredMail::class => [ 'instanceOf'=>UserRegisteredMail::class],
			IUserMailChangedMail::class => [ 'instanceOf'=>UserMailChangedMail::class],
			IUserResetPasswordMail::class => [ 'instanceOf'=>UserResetPasswordMail::class],
			IUserRepository::class => [ 'instanceOf'=>UserRepository::class,'shared'=>true]
		]);
		$this->_dice->addRules($this->getDi());
		$this->_dice->addRules($diceRules);
	}

	/**
	 * Ajoute un ensemble de règles à dice.
	 * @param array $rules Régles à ajouter
	 */
	protected final function addDiceRules(array $rules){
		$this->_dice->addRules($rules);
	}

	/**
	 * @param string $class
	 * @param array  $params
	 * @return object
	 */
	protected final function create(string $class,array $params=[]){
		return $this->_dice->create($class,$params);
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
	 * @param string $msserverAddr Resolver socket
	 * @return string|null MSServer socket instance
	 */
	protected function getMSServerAddr(string $msserverAddr):?string{
		$cache = $this->getCacheSystem();
		if($cache->contains("server/msserver/addr") && is_string($res = $cache->get("server/msserver/addr"))) {
			return $res;
		}else{
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
	 * @param array $confFiles Fichiers de configurations à charger
	 * @return IConf Configurations
	 */
	protected function initConfs(array $confFiles):IConf{
		$conf = $this->getCacheSystem()->get(self::CONF);
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
			$this->getCacheSystem()->set(self::CONF,$conf);
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
		$translator = $this->getCacheSystem()->get(self::TRANSLATOR);
		if(is_null($translator)){
			/** @var ILanguageLoader $loader */
			$loader = $this->_dice->create(ILanguageLoader::class);
			$translator =  new Translator($loader,$langs,$default);
			$this->getCacheSystem()->set(self::TRANSLATOR,$translator);
			return $translator;
		}else{
			return $translator;
		}
	}

	protected function loadModules():void{
		$modules = $this->getCacheSystem()->get(self::MODULES);
		if(is_null($modules)){
			WFW::collectModules();
			$this->getCacheSystem()->set(
				self::MODULES,
				WFW::modules()
			);
		}else WFW::restoreModulesFromCache($modules);
	}

	/**
	 * @return array
	 */
	protected function getDi():array{
		return WFW::di();
	}

	/**
	 * @return array
	 */
	protected function getDomainEventListeners():array{
		$listeners = $this->getCacheSystem()->get(self::DOMAIN_EVENT_LISTENERS);
		if(is_null($listeners)){
			$listeners = WFW::domainEventListeners();
			$this->getCacheSystem()->set(self::DOMAIN_EVENT_LISTENERS,$listeners);
		}
		return $listeners;
	}

	/**
	 * @return array
	 */
	protected function getCommandHandlers():array{
		$handlers = $this->getCacheSystem()->get(self::COMMAND_HANDLERS);
		if(is_null($handlers)){
			$handlers = WFW::commandHandlers();
			$this->getCacheSystem()->set(self::DOMAIN_EVENT_LISTENERS,$handlers);
		}
		return $handlers;
	}

	/**
	 * @return array Confs files
	 */
	protected function getConfs():array{
		$confFiles = $this->getCacheSystem()->get(self::CONF_FILES);
		if(is_null($confFiles)){
			$confFiles = WFW::confs();
			$this->getCacheSystem()->set(self::CONF_FILES,$confFiles);
		}
		return $confFiles;
	}

	/**
	 * @param array|null $commands
	 * @return array
	 */
	protected function getCommandRules(?array $commands=null):array{
		$rules = $this->getCacheSystem()->get(self::COMMAND_RULES);
		if(is_null($rules)){
			$rules = WFW::commandsPolicy(!empty($commands) ? $commands : null);
			$this->getCacheSystem()->set(self::COMMAND_RULES,$rules);
		}
		return $rules;
	}

	/**
	 * @param array|null $queries
	 * @return array
	 */
	protected function getQueryRules(?array $queries=null):array{
		$rules = $this->getCacheSystem()->get(self::QUERY_RULES);
		if(is_null($rules)){
			$rules =  WFW::queriesPolicy(!empty($queries) ? $queries : null);
			$this->getCacheSystem()->set(self::QUERY_RULES,$rules);
		}
		return $rules;
	}

	/**
	 * @param array $langs
	 * @return array
	 */
	protected function getLangs(array $langs):array{
		$lc = $this->getCacheSystem()->get(self::LANGS);
		if(is_null($lc)){
			$langs = WFW::langs($langs);
			$this->getCacheSystem()->set(self::LANGS,$langs);
		}else $langs = $lc;
		return $langs;
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
	 * @return IConf Configuration de l'application.
	 */
	public final function getConf(): IConf{
		return $this->_conf;
	}

	/**
	 * @return ITranslator Gestionnaire de langues.
	 */
	public final function getTranslator():ITranslator{
		return $this->_translator;
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
	 * Called by the app just before closing
	 */
	public function close() {
		$cache = $this->getCacheSystem();
		$cache->set(self::CONF,$this->getConf());
		$cache->set(self::TRANSLATOR,$this->getTranslator());
	}
}