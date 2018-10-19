<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 16:19
 */

namespace wfw\cli\tester\contexts;

use wfw\cli\backup\LocalMysqlDbBackup;
use wfw\daemons\modelSupervisor\client\IMSClient;
use wfw\daemons\modelSupervisor\client\MSInstanceAddrResolver;
use wfw\daemons\modelSupervisor\client\MSClient;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\errors\handlers\DefaultErrorHandler;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Singleton-like disgusting thing. Should only be used inside integration tests, never outside.
 * It allow to retrieve a pre-configured context inside those tests.
 * For the fool who read this : Please, don't write Singletons or singleton-like terrible things.
 * Global states are almost ALWAYS bad ideas.
 * If you do so, don't use them.
 * Never.
 * (And prey for those who don't understand why they're evil)
 *
 * This crappy class is used and initialized by the cli/tester to create an environment reachable
 * from all integration tests.
 * It allow some fantasy like manage a test DB.
 */
final class TestEnv
{
	/** @var null|ITestsEnvironment $instance */
	private static $instance;

	/**
	 * Pas de constructeur pour cette classe.
	 */
	private function __construct(){}

	/**
	 * @param ITestsEnvironment $environment Env that we want reachable from everywhere in integration test.
	 */
	public static function init(ITestsEnvironment $environment):void{
		$_SESSION = []; //SetUp the $_SESSION var as is doesn'nt exists in CLI mode.
		if(!self::$instance) self::$instance = $environment;
		else throw new IllegalInvocation("This test environment have already been initialized !");
	}

	/**
	 * @return ITestsEnvironment
	 */
	public static function get():ITestsEnvironment{ return self::$instance; }

	/**
	 * Supprime la base de données de tests courante et recharge une nouvelle DB vide.
	 *
	 * @param string      $sqlDbFile
	 * @param string      $dbName
	 * @param null|string $host
	 * @param null|string $user
	 * @param null|string $password
	 */
	public static function restoreEmptyTestSqlDb(
		string $sqlDbFile=CLI."/tester/config/clean_db.sql",
		string $dbName = "event_store_test",
		?string $host = null,
		?string $user = null,
		?string $password = null
	):void{
		/** @var IConf $conf */
		$conf = self::get()->getAppContext()->getConf();
		$sqlBackup = new LocalMysqlDbBackup(
			$sqlDbFile,
			$host ?? $conf->getString("server/databases/default/host"),
			$dbName,
			$user ?? $conf->getString("server/databases/default/login"),
			$password ?? $conf->getString("server/databases/default/password")
		);
		$sqlBackup->load();
	}

	/**
	 * Reconstruit tous les models du MSServer de Test
	 *
	 * @param string $resolverAddr Adresse de la socket du MSServerPool
	 * @param string $instanceName Nom de l'instance
	 * @param string $user         Login de l'utilisateur test
	 * @param string $password     Password de l'utilisateur de test
	 */
	public static function restoreModels(
		string $resolverAddr = DAEMONS."/modelSupervisor/data/ModelSupervisor.socket",
		string $instanceName = "test",
		string $user = "tester",
		string $password = "test"
	):void{
		$client = self::createMSClient(
			$resolverAddr,
			$instanceName,
			$user,
			$password
		);
		$client->login();
		$client->rebuildAllModels();
		$client->logout();
	}

	/**
	 * Crée une connexion à un MSWriterClient de test
	 *
	 * @param string $resolverAddr Adresse de la socket du MSServerPool
	 * @param string $instanceName Nom de l'instance
	 * @param string $user         Login de l'utilisateur test
	 * @param string $password     Password de l'utilisateur de test
	 * @throws \wfw\daemons\modelSupervisor\client\errors\MSClientFailure
	 * @return IMSClient
	 */
	public static function createMSClient(
		string $resolverAddr = DAEMONS."/modelSupervisor/data/ModelSupervisor.socket",
		string $instanceName = "test",
		string $user = "tester",
		string $password = "test"
	):IMSClient{
		$instanceAddr = (new MSInstanceAddrResolver($resolverAddr))->find($instanceName);
		$client = new MSClient($instanceAddr, $user, $password);
		return $client;
	}

	/**
	 * Restaure le handler sans warning (permet à code coverage de fonctionner)
	 */
	public static function restoreErrorHandler():void{
		(new DefaultErrorHandler(false))->handle();
	}
}