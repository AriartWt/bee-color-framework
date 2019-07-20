<?php
namespace wfw\cli\tester\launchers\PHPUnit;

use wfw\cli\tester\launchers\TestSequence;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Sequence de test sous PHPUnit
 */
final class PHPUnitTestSequence extends TestSequence {
	/** @var string $_tmpPath */
	private $_tmpPath;
	/** @var string $_cmds */
	private $_coverage;
	/** @var string $_codePath */
	private $_codePath;

	/**
	 * PHPUnitTestSequence constructor.
	 *
	 * @param array  $paths        Liste des dossiers à tester
	 * @param array  $environment  Environnement de tests
	 * @param string $description  Description de la séquence de tests
	 * @param string $codepath     Chemin vers le dossier duquel le code est issu
	 * @param string $tmpPath      Chemin vers un dossier temporaire dans lequel les scripts
	 *                             d'initilisation de contexte seront placés.
	 * @param string $coveragePath Commandes
	 */
	public function __construct(
		array $paths,
		array $environment = [],
		string $description = '',
		string $codepath = '',
		string $tmpPath = "/tmp",
		string $coveragePath = ""
	){
		parent::__construct($paths, $environment, $description);
		$this->_tmpPath = $tmpPath;
		$this->_coverage = $coveragePath;
		$this->_codePath = $codepath;
	}

	/**
	 * Permet de lancer la séquence de tests
	 */
	public function start(): void{
		fwrite(STDOUT,"\n\e[105mTest sequence : ".$this->getDescription()."\e[0m\n");
		if(count($this->getEnvironments()) === 0) $this->execTestSequence();
		else foreach($this->getEnvironments() as $k=>$env){
			$this->execTestSequence($env,$k);
		}
	}

	/**
	 * @param string|null $environment Environment à rendre disponible pour les tests
	 * @param null|string $name Nom de l'environment
	 */
	private function execTestSequence(?string $environment = null,?string $name = null){
		if($environment) fwrite(STDOUT,"\e[105mTest environment :  $environment.\e[0m\n");
		$fileName = "$this->_tmpPath/".(new UUID(UUID::V4)).".phpunit.bootstrap.php";
		$res = "<?php 
			require_once \"".dirname(__DIR__,3)."/init.environment.php\";
		";
		if($environment) $res.=$this->printEnvironment($environment);
		file_put_contents($fileName,$res);
		foreach ($this->getPaths() as $p){
			$this->exec($p,$fileName,$name);
		}
		unlink($fileName);
	}

	/**
	 * @param string $context Contexte à insérer
	 * @return string
	 */
	private function printEnvironment(string $context):string{
		return "
			wfw\\cli\\tester\\contexts\\TestEnv::init(new $context());
		";
	}

	/**
	 * @param string      $path          Execute les tests contenus dans $path
	 * @param string      $bootstrapPath Fichier de démarrage context
	 * @param null|string $envName       Nom de l'environment
	 */
	private function exec(string $path,?string $bootstrapPath = null,?string $envName=null):void{
		if(is_null($bootstrapPath)) $bootstrapPath = dirname(__DIR__,3)."/init.environment.php";
		system(dirname(__DIR__,3)."/tester/launchers/PHPUnit/phpunit.phar --bootstrap \"$bootstrapPath\""
			   ." --whitelist \"".dirname(__DIR__,4)."/$this->_codePath\" --coverage-html \"$this->_coverage".($envName?"/$envName":'')
			   ."\" \"$path\" 2>&1");
	}
}