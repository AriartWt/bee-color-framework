#!/usr/bin/php -q
<?php


use wfw\cli\backup\Backup;
use wfw\cli\backup\BackupManager;
use wfw\cli\backup\conf\BackupManagerConf;
use wfw\cli\backup\LocalFilesBackup;
use wfw\cli\backup\LocalMysqlDbBackup;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\PHP\types\PHPString;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt("-list","Liste tous les backups du manager",0,null,true),
	new ArgvOpt("-make", "Crée un backup", null, null, true),
	new ArgvOpt("-import", "Importe un backup", null, null, true),
	new ArgvOpt("-export", "Exporte un backup vers un repertoire", 2, null, true),
	new ArgvOpt("-load", "Charge le backup spécifié", 1, null, true),
	new ArgvOpt("-remove","Supprime le ou les backups spécifiés",null,null,true),
	new ArgvOpt("--debug","Affiche le détail des erreurs",0,null,true)
])),$argv);

try{
	$confs = new BackupManagerConf(
		dirname(dirname(__DIR__)).'/engine/config/conf.json',
		dirname(dirname(__DIR__)).'/site/config/conf.json'
	);

	$saveFile = $confs->getManagerFolder().'/manager.backup';
	$serializer = new LightSerializer(new GZCompressor(), new PHPSerializer());
	if(file_exists($saveFile)){
		/** @var BackupManager $manager */
		$manager = $serializer->unserialize(file_get_contents($saveFile));
		$manager->changeMaxBackup($confs->getMaxBackups());
	}
	else $manager = new BackupManager($confs->getMaxBackups());
	$args=[];
	if(count($argv) === 1 || count($argv) === 2 && $argvReader->exists("-make")){
		$dirs = glob(dirname(__DIR__,2)."/*",GLOB_ONLYDIR);
		foreach($dirs  as &$v){ $v = basename($v); }
		$args = array_merge(["dbs"],array_diff($dirs,["backups",".",".."]));
		var_dump($args);
	}

	if($argvReader->exists('-list')){
		foreach($manager as $name=>$backup){
			fwrite(STDOUT,"$name : ".realpath($backup->getLocation()).PHP_EOL);
		}
	}

	if($argvReader->exists('-make') || count($args) > 0){
		$args = (count($args) > 0) ? $args : $argvReader->get('-make');
		if(count($args) === 0)
			throw new InvalidArgumentException("At least one parameter required for -make command !");

		$backupFolder = $confs->getBackupFolder();
		if($args[0] === '-path'){
			array_shift($args);
			$path = array_shift($args);
			if(!(new PHPString($path))->startBy('/'))
				$path = dirname(__DIR__)."/$path";
			if(!is_dir($path))
				throw new InvalidArgumentException("$path is not a valide directory");
			$backupFolder = $path;
		}

		$name = date("d-m-Y_H:i:s",time());
		if($args[0] === "-n"){
			array_shift($args);
			$name=array_shift($args);
		}
		$backupFolder = "$backupFolder/$name";

		$backups=[];
		foreach($args as $k=>$v){
			if($v === "dbs"){
				foreach($confs->getDatabases() as $db=>$infos){
					$backups[] = new LocalMysqlDbBackup(
						"$backupFolder/dump_$db.sql",
						$infos->host,
						$db,
						$infos->login,
						$infos->password,
						$confs->getMysqldumpPath(),
						$confs->getMysql()
					);
				}
			}else{
				if(is_dir(dirname(__DIR__,2)."/$v")){
					$backups[] = new LocalFilesBackup(dirname(__DIR__,2)."/$v",$backupFolder);
				}else{
					throw new InvalidArgumentException(
						"Unknown backup type '$v' : Only direct folders under ".dirname(__DIR__,2)
						." or special keyword 'dbs' are accepted !"
					);
				}
			}
		}
		if(count($backups) === 0) throw new InvalidArgumentException("(make) No backup to do !");
		$manager[$name]=new Backup($backupFolder,...$backups);
		$manager[$name]->make();
	}

	if($argvReader->exists('-import')){
		$args = $argvReader->get('-import');
		if(count($args) === 0 )
			throw new InvalidArgumentException(
					"Import expects at least one path to a backup folder !"
			);
		if(!is_dir($args[0]))
			throw new InvalidArgumentException("$args[0] is not a valide directory path !");
		$name = $args[1] ?? basename($args[0]);
		if(!is_file("$args[0]/export.backup"))
			throw new InvalidArgumentException("$args[0] doesn't contain an export.backup file !");
		$b = $serializer->unserialize(file_get_contents("$args[0]/export.backup"));
		if($b instanceof Backup)
			$b->setLocation($args[0]);
		$manager[$name] = $b;
	}

	if($argvReader->exists('-export')){
		$args = $argvReader->get('-export');
		$name = $args[0];
		$path = $args[1];
		if(!isset($manager[$name]))
			throw new InvalidArgumentException("Unknown backup $name");
		if(!is_dir(dirname($path)))
			throw new InvalidArgumentException(dirname($path)." is not a valide directory !");
		/** @var \wfw\cli\backup\IBackup $b */
		$b = $manager[$name];
		$currentLoc = $b->getLocation();
		if($b instanceof Backup) $b->setLocation($path);
		file_put_contents($currentLoc.'/export.backup',$serializer->serialize($b));
		$b->setLocation($currentLoc);
		if(!is_dir($path))
			mkdir($path,0700);
		$res = null;
		system("cp -Rp \"$currentLoc\" \"$path\"",$res);
		if($res !== 0)
			throw new Exception("Command 'cp -Rp \"$currentLoc\" \"$path\"' failed with code $res");
		$manager->remove($name);
	}

	if($argvReader->exists('-load')) $manager[$argvReader->get('-load')[0]]->load();

	if($argvReader->exists('-remove')){
		$backups = [];
		foreach($argvReader->get('-remove') as $v){
			if($manager->exists($v)) $backups[] = $v;
			else throw new InvalidArgumentException("(remove) Unknown backup '$v'");
		}
		if(count($backups)===0)
			throw new InvalidArgumentException("(remove) No backup name specified");
		foreach($backups as $backup){
			$manager->remove($backup);
		}
	}

	file_put_contents($saveFile,$serializer->serialize($manager));
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_backup WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists("--debug"))
		fwrite(STDOUT,"\e[31mWFW_backup ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
	else
		fwrite(
			STDOUT,
			"\e[31mWFW_backup ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
	exit(2);
}
exit(0);