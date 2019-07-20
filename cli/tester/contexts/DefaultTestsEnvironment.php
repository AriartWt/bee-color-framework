<?php

namespace wfw\cli\tester\contexts;

use Dice\Dice;
use wfw\engine\core\app\context\DefaultContext;
use wfw\engine\core\app\context\IWebAppContext;
use wfw\engine\core\app\WebApp;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\rules\UserTypeBasedAccess;
use wfw\engine\core\security\rules\ValidToken;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\package\general\layouts\blank\BlankLayout;
use wfw\engine\package\users\domain\types\Client;

/**
 * Contexte de test basé sur Dice.
 *
 * Ce contexte par défaut n'est compatible qu'avec les IWebAppContext dont la classe ROOT possède une
 * propriété _dice dans laquelle une instance de Dice est présente avec une méthode create(string class,array $args)
 */
class DefaultTestsEnvironment implements ITestsEnvironment {
	/** @var null|DefaultContext $_context */
	private $_context;
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * DefaultTestsEnvironment constructor.
	 */
	public function __construct(){}

	/**
	 * Initialise ou réinitialise l'environnement de tests avec les arguments spécifiés.
	 *
	 * @param array $args Paramètres du context à créer.
	 */
	public function init(array $args = []): void {
		$this->_context = $this->createWebAppContext($args);
		$class = get_class($this->_context);
		while(get_parent_class($class)){
			$class = get_parent_class($class);
		}
		$reflect = new \ReflectionClass($class);
		$diceProperty = $reflect->getProperty("_dice");
		$diceProperty->setAccessible(true);
		$this->_dice = $diceProperty->getValue($this->_context);
	}

	/**
	 * Crée un IWebAppContext
	 * @param array  $args
	 * @return DefaultContext
	 */
	private function createWebAppContext(array $args = []):IWebAppContext{
		$class = $args["context"] ?? DefaultContext::class;
		if(!is_a($class,IWebAppContext::class,true))
			throw new \InvalidArgumentException("$class doesn't implements ".IWebAppContext::class);
		return new $class(...$this->initContextArgs($args));
	}

	/**
	 * @param array $args Arguments
	 * @return array
	 */
	private function initContextArgs(array $args=[]):array{
		$globals = $args["globals"] ?? [];
		$globals["_SERVER"]=array_merge([
			"HTTP_ACCEPT_LANGUAGE" => "fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7",
			"REMOTE_ADDR" => "127.0.0.1",
			"PATH_INFO" => "/",
			"REQUEST_URI" => "",
			"REQUEST_METHOD" => "GET"
		],$globals["_SERVER"]??[]);
		return [
			$args["defaultLayoutClass"] ?? BlankLayout::class,
			$args["errorViewPath"] ?? null,
			$args["ajaxViewPath"] ?? null,
			$args["connections"] ?? [],
			$args["langs"] ?? ["fr" => [
				dirname(__DIR__,3)."/engine/config/lang/fr.lang.json",
				dirname(__DIR__,3)."/site/config/lang/fr.lang.json"
			]],
			$args["accessRules"] ?? [
				RequireAuthentification::class => [
					[
						//ATTENTION : avec le fonctionnement actuel, on met les régles
						//les plus précises en premier, mais les régles
						//suivante doivent permettre le package aussi pour que ça fonctionne !
						//Heureusement, très peu de package disposent de certaines méthodes publique/privées,
						//ils sont en générale purement privés, ou purement publiques. Rarement les deux.

						//On autorise publiquement les actions suivantes pour le package users :
						// FrogottentPassword,ResendForgottenPasswordMail
						// Register,ConfirmRegistration
						// ChangeMailConfirmation
						// Login
						"^users/(?!(L|l)ogin|(F|f)orgottenPassword|(C|c)onfirmUserRegistration|(R|r)esendForgottenPassword|(R|r)esetPassword|(R|r)egister|(C|c)angeMailConfirmation).*$",
						//Tous les packages autres que web et lang sont protégés et requièrent
						// une authentification.
						//users est là pour permettre à la régle précédente de s'appliquer
						"^(?!web|lang|users).*$"
					]
				],
				ValidToken::class => [],
				UserTypeBasedAccess::class => [
					[
						Client::class => [
							//On autorise toutes les actions du package users sauf la partie admin
							"^users/(?!admin/).*$"
						]
					]
				]
			],
			$args["diceRules"] ?? [],
			$globals,
			$args["confFiles"] ?? [
				dirname(__DIR__,3)."/engine/config/conf.json",
				dirname(__DIR__,3)."/site/config/conf.json",
				dirname(__DIR__,2)."/tester/config/conf.tests.json"
			],
			$args["baseUrl"] ?? null
		];
	}

	/**
	 * Permet de créer n'importe quel objet en utilisant Dice initialisé par le context courant.
	 *
	 * Si une WebApp est demandée, $params devra correspondre aux paramètres de son context. Si aucun
	 * paramètre n'est passé, le contexte courant sera utilisé, sinon un nouveau contexte est créé.
	 *
	 * Si un IWebAppContext est demandé sans paramète, le context courant est retourné, sinon
	 * un nouveau context avec les paramètres demandés est créé.
	 *
	 * @param string $obj    Classe de l'objet à instancier
	 * @param array  $params Liste des paramètres à passer au constructeur
	 * @return mixed
	 */
	public function create(string $obj, array $params = []){
		if(!$this->_context) throw new IllegalInvocation(
			"Test environment havn't been initialized ! Please call init() at least once !"
		);
		if($obj === WebApp::class){
			if(count($params) === 0) return new WebApp($this->_context);
			else return new WebApp($this->createWebAppContext($params));
		}else if(is_a($obj,IWebAppContext::class,true)){
			if(count($params) === 0) return $this->_context;
			else{
				$params["context"] = $obj;
				return $this->createWebAppContext($params);
			}
		}else return $this->_dice->create($obj,$params);
	}

	/**
	 * Retourne le webAppContext courant
	 *
	 * @return IWebAppContext
	 */
	public function getAppContext(): IWebAppContext{
		return $this->_context;
	}
}