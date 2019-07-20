<?php
namespace wfw\cli\tester\launchers\PHPUnit;

use wfw\cli\tester\launchers\ITestsLauncher;
use wfw\engine\core\conf\IConf;
use wfw\engine\lib\PHP\objects\StdClassOperator;

/**
 * Lance des tests en utilisant phpUnit
 */
final class PHPUnitTestsLauncher implements ITestsLauncher{
	/** @var IConf $_conf */
	private $_conf;
	/** @var bool $_disableOutPut */
	private $_disableOutPut;

	/**
	 * PHPUnitTestsLauncher constructor.
	 *
	 * @param IConf $conf          Configuration des tests à lancer
	 * @param bool  $disableOutput Désactive les messages du launcher dans la console.
	 */
	public function __construct(IConf $conf,bool $disableOutput) {
		$this->_conf = $conf;
		$this->_disableOutPut = $disableOutput;
	}

	/**
	 * Lance les tests
	 *
	 * @param string[] $tests Liste de tests à lancer
	 */
	public function launchTests(string... $tests): void {
		foreach($tests as $t){
			$t = str_replace('"','',$t);
			if(strpos($t,"/") !== 0) $t = dirname(__DIR__,4)."/tests/PHPUnit/$t";
			$this->output("Launching tests for $t\n");
			$this->exec($t);
		}
	}

	/**
	 * Ecris un message dans la sortie standard, si la sortie n'est pas désactivée.
	 * @param string $message Message à écrire
	 */
	private function output(string $message):void{
		if(!$this->_disableOutPut) fwrite(STDOUT,$message);
	}
	/**
	 * Lance les tests
	 *
	 * @param string[] $sequences Liste de séquences à lancer
	 */
	public function launchSequences(string... $sequences): void {
		foreach ($sequences as $k=>$sequence){
			if(empty($sequence)) $sequences[$k] = "*";
		}
		$this->output("Launching sequences ".implode('\n',$sequences)."\n");

		$objectPath = new StdClassOperator($this->_conf->getObject("sequences/PHPUnit"));
		foreach($sequences as $s){
			$this->launchTestSequence($s,$objectPath);
		}
	}

	/**
	 * Traite et lance une séquence de tests
	 * @param string           $sequence Sequence à traiter
	 * @param StdClassOperator $objectPath Objet contenant toutes les séquences du launcher courant
	 * @throws \InvalidArgumentException
	 */
	private function launchTestSequence(string $sequence,StdClassOperator $objectPath){
		$tmp = explode("/",$sequence);
		$path = [];
		$current = $objectPath;
		try{
			foreach($tmp as $part){
				if(preg_match("/\*$/",$part)){
					$part = str_replace("*",'',$part);
					$tmpRes = new \stdClass();
					foreach($current as $k=>$v){
						if(preg_match("/^$part.*$/",$k)){
							$tmpRes->$k = $v;
						}
					}
					$current = new StdClassOperator($tmpRes);
				}else{
					$path[] = $part;
					$current = new StdClassOperator($current->find($part));
				}
			}
		}catch(\Exception $e){
			throw new \InvalidArgumentException(
				implode("/",$path) ." is not a valid PHPUnit test sequence ! "
				."Please check your conf (keys under sequences/PHPUnit)"
			);
		}
		$basePath = dirname(__DIR__,4)."/tests/PHPUnit/".implode("/",$path);
		foreach($this->getTestSequenceObjects($current,$basePath) as $toRun){
			$toRun->start();
		}
	}

	/**
	 * Retourne toutes les séquences de tests contenues dans un objet partiuclier. Recursivement.
	 *
	 * @param object $obj Objet dont on souhaite obtenir les séquences de tests.
	 * @param string $path Chemin de l'objet courant dans l'objet global
	 * @return PHPUnitTestSequence[]
	 */
	private function getTestSequenceObjects(object $obj,string $path):array{
		if($this->isTestSequenceObject($obj)){
			$environments = $obj->environments ?? [];
			foreach($environments as $k=>$e){
				$environments[$k] = $this->_conf->getString("environments/$e");
			}
			$coveragePath = $this->_conf->getString("launcherCommands/PHPUnit/--coverage-html");
			$coveragePath = str_replace(dirname(__DIR__,4),$coveragePath,$path);
			return [new PHPUnitTestSequence(
				[$path],
				$environments,
				$obj->description??"No sequence description",
				$obj->codePath??'',
				"/tmp",
				$coveragePath
			)];
		} else {
			$res = [];
			foreach($obj as $k=>$o){
				$res = array_merge($res,$this->getTestSequenceObjects($o,"$path/$k"));
			}
			return $res;
		}
	}

	/**
	 * Verifie si un objet est représente une séquence de tests
	 * @param object $obj Objet à tester
	 * @return bool
	 */
	private function isTestSequenceObject(object $obj):bool{
		foreach($obj as $k=>$v){
			if(!preg_match("/^(environments|description|codePath)$/",$k)) return false;
		}
		return true;
	}

	/**
	 * @param string $path Execute les tests contenus dans $path
	 * @param string $bootstrapPath Fichier de démarrage context
	 */
	private function exec(string $path,?string $bootstrapPath = null):void{
		if(is_null($bootstrapPath)) $bootstrapPath = dirname(__DIR__,3)."/init.environment.php";
		system(dirname(__DIR__,3)."/tester/launchers/PHPUnit/phpunit.phar --bootstrap \"$bootstrapPath\""
			." --testdox \"$path\" 2>&1");
	}
}