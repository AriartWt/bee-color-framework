#!/usr/bin/php -q
<?php

use wfw\cli\wfw\templates\confs\SiteConfTemplate;
use wfw\cli\wfw\templates\db\DBTemplate;
use wfw\daemons\kvstore\server\conf\KVSConfs;
use wfw\daemons\modelSupervisor\client\MSInstanceAddrResolver;
use wfw\daemons\modelSupervisor\server\conf\MSServerPoolConfs;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\MSServerWriterAccess;
use wfw\engine\core\data\DBAccess\SQLDB\MySQLDBAccess;
use wfw\engine\core\domain\events\observers\DomainEventObserver;
use wfw\engine\core\domain\events\store\DBBasedEventStore;
use wfw\engine\core\domain\repository\AggregateRootRepository;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\PHP\system\filesystem\json\JSONFile;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\repository\UserRepository;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\User;

require_once(dirname(__DIR__)."/init.environment.php");

$argvReader=$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt('add','Ajoute un projet au gestionnaire (add [nom projet] [chemin])',2,null,true),
	new ArgvOpt('create','Crée un nouveau projet et l\'ajoute au gestionnaire (create [nom projet] [chemin]',2,null,true),
	new ArgvOpt('import','Importe un projet dans un projet existant (import [nom du projet] [chemin des fichiers] [(optionnel)-keepConf]))',null,null,true),
	new ArgvOpt('self','Applique les commandes sur le wfw global',null,null,true),
	new ArgvOpt('remove','Supprime un projet du gestionnaire',1,null,true),
	new ArgvOpt('locate',"Localiste le projet. Si pas d'argument, retourne le chemin vers le projet global",null,null,true),
	new ArgvOpt('[PROJECT] [cmd](args...)',"Execute une commande sur un projet",null,null,true)
])),$argv);

try{
	if(count($argv) < 2)
		throw new InvalidArgumentException("At least one arg expected ! --help for command usage");
	$db = new JSONFile(__DIR__.'/global.db.json');
	if(!file_exists(__DIR__.'/global.db.json')) $db->write([]);
	$data = $db->read(true);
	$validName = function(string $name):bool{
		return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$name);
	};
	$exec = function(string $cmd):void{
		$outputs = []; $res = null;
		exec($cmd,$outputs,$res);
		if($res !== 0) throw new Exception(
			"Error trying to exec '$cmd'".
			" code $res, outputs : ".implode("\n",$outputs)
		);
	};
	if($argvReader->exists('self')){
		$cmd = ROOT."/wfw";
		foreach($argvReader->get('self') as $k=>$c){
			if($c === '-help' && $k<1) $c="-$c";
			$cmd .= " \"$c\" ";
		}
		system("$cmd 2>&1");
	}else if(isset($data[$argv[1]])){
		//si le premier argument ne concerne pas une commande connue, alors c'est le nom du projet
		$project = $argv[1];
		if(!isset($data[$project]))
			throw new InvalidArgumentException("$project is not a registered project !");
		$cmd = "$data[$project]";
		foreach(array_values(array_slice($argv,2)) as $k=>$c){
			if($c === '-help' && $k<1) $c="-$c";
			$cmd .= " \"$c\" ";
		}
		system("$cmd 2>&1");
	} else if($argvReader->exists('add')){
		$args = $argvReader->get('add');
		$path = "$args[1]/wfw";
		if(!is_file($path))
			throw new InvalidArgumentException("$args[1] is not a valid wfw project !");
		else{
			if(!$validName($args[0]))
				throw new InvalidArgumentException("$args[0] is not a valid project name !");
			//create the folder into the project if not exists
			if(!is_dir("$args[1]/$args[0]")) mkdir("$args[1]/$args[0]");
			//create the config symlink into the site's folder if not exists
			if(!is_link("$args[1]/$args[0]/config")){
				$prev = getcwd();
				chdir("$args[1]/$args[0]");
				$exec("ln -s \"../site/config\" \"config\"");
				chdir($prev);
			}

			//unlink an existing symlink with same name in case it's a corrupted link
			if(is_link(ROOT."/$args[0]")) unlink(ROOT."/$args[0]");
			//create the symlink to the ROOT folder
			$exec("ln -s \"$args[1]/$args[0]\" \"".ROOT."/$args[0]\"");
			//write the project root path in DB
			$data[$args[0]] = $path;
			$db->write($data);
		}
	} else if($argvReader->exists('create')){
		$args = $argvReader->get('create');
		$pName = $args[0];
		$path = $args[1];
		if(!$validName($args[0]))
			throw new InvalidArgumentException("$pName is not a valid project name !");
		if(!is_dir($args[1]))
			throw new InvalidArgumentException("$path is not a valid directory !");
		$path = "$path/$pName";
		mkdir($path);

		$exec("cp -Rp ".ROOT."/cli/wfw/templates/site $path");

		//create base folders and files
		$dirs = ['engine','cli','wfw','.htaccess'];
		foreach($dirs as $dir){
			$exec("cp -Rp ".ROOT."/$dir $path");
		}

		mkdir("$path/daemons");
		//copy all daemons/* without daemons/*/data
		$daemons = array_diff(scandir(ROOT."/daemons"),['..','.']);
		foreach($daemons as $dir){
			if(is_dir(ROOT."/daemons/$dir")){
				if(!is_dir("$path/daemons/$dir")) mkdir("$path/daemons/$dir");
				$dirs = array_diff(scandir(ROOT."/daemons/$dir"),['..','.','data']);
				foreach($dirs as $d){
					$exec("cp -Rp ".ROOT."/daemons/$dir/$d $path/daemons/$dir/$d");
				}
			}else $exec("cp -Rp ".ROOT."/daemons/$dir $path/daemons/$dir");
		}

		$exec("wfw add $pName $path");
		// \o/ the project files are ready.
		// Now let's create credentials and event_store mysql db
		$kvsPwd =(string) new UUID(UUID::V4);
		$kvsUser = $pName."_msserver";
		$kvsContainer = $pName."_db";
		$mssPwd =(string) new UUID(UUID::V4);
		$mssUser = $pName."_website";
		$dbPwd =(string) new UUID(UUID::V4);
		$dbRootPwd =(string) new UUID(UUID::V4);
		$dbRootUser = "$pName-root";
		$dbName = $pName."_EventStore";
		$firstUser =(string) new UUID(UUID::V4);

		$wfwConf = new FileBasedConf(CLI."/wfw/config/conf.json");
		$tmpDir = $wfwConf->getString('tmp');
		if(strpos($tmpDir,"/")!==0) $tmpDir = ROOT."/$tmpDir";

		$dbFile = "$tmpDir/$pName.sql";
		$dbCredentials = "$tmpDir/$pName.credentials";
		$mysqlRootUser = $wfwConf->getString("mysql/root/login");
		$mysqlRootPwd = $wfwConf->getString("mysql/root/password");
		$unixUser = $wfwConf->getString("unix_user") ?? "www-data";
		$mysqlPath = $wfwConf->getString("mysql/path") ?? "mysql";

		//so, now we have credentials. We will create the DB.
		$db = new DBTemplate($dbName,$pName,$dbPwd,$dbRootUser,$dbRootPwd);
		if(!file_exists("/tmp/$pName.sql")){
			touch($dbFile);
			chmod($dbFile,0600);
			file_put_contents($dbFile,$db);
		}
		//credentials for login into db with true root user
		if(!file_exists($dbCredentials)){
			touch($dbCredentials);
			chmod($dbCredentials,0600);
		}
		file_put_contents($dbCredentials,"[mysql]\npassword=$mysqlRootPwd");

		$exec(
			"\"$mysqlPath\" --defaults-file=\"{$dbCredentials}\" "
			."-h \"localhost\" -u \"$mysqlRootUser\" "
			."-e \"CREATE DATABASE IF NOT EXISTS \`$dbName\`\""
		);
		$exec(
			"\"$mysqlPath\" --defaults-file=\"{$dbCredentials}\" "
			."-h \"localhost\" -u \"$mysqlRootUser\" "
			."\"$dbName\" < \"$dbFile\""
		);
		//Delete files from /tmp
		unlink($dbCredentials);
		unlink($dbFile);
		//now db and two users have been created : website and root for the new db
		//we have to edit all config files everywhere \o/

		//start with KVS (global framework file):
		$kvs = (new KVSConfs(ENGINE."/config/conf.json"))->getConfFile();
		$users = $kvs->getArray("users");
		$users[$kvsUser] = [ "password" => $kvsPwd ];
		$kvs->set("users",$users);
		$containers = $kvs->getArray("containers");
		$containers[$kvsContainer] = [
			"permissions" => [
				"users" => [ $kvsUser => [ "read" => true, "write" => true, "admin" => true ] ],
				"default_storage" => "IN_MEMORY_PERSISTED_ON_DISK"
			]
		];
		$kvs->set("containers",$containers);
		$kvs->save();

		//next MSServer (global framework file):
		$mss = ($msConf = new MSServerPoolConfs(ENGINE."/config/conf.json"))->getConfFile();
		$mss->set("instances/$pName",[
			"models_to_load_path" => "{ROOT}/$pName/config/load/models.php",
			"kvs" => [ "login" => $kvsUser, "password" => $kvsPwd, "container" => $kvsContainer ],
			"users" => [ $mssUser => [ "password"=>$mssPwd ] ],
			"components" => [ "writer" => [
				"kvs" => [ "login" => $kvsUser, "password" => $kvsPwd, "container" => $kvsContainer ],
				"mysql" => [ "host" => "localhost", "database" => $dbName, "login" => $pName, "password" => $dbPwd ],
				"permissions" => [ "users" => [ $mssUser => [ "write"=>true,"read"=>true,"admin"=>true ] ] ]
			] ]
		]);
		$mss->save();

		//next project files :
		$globalConf = new FileBasedConf(ENGINE."/config/conf.json");
		$sctlPath = $globalConf->getString("server/daemons/sctl");
		if(strpos($sctlPath,"/")!==0) $sctlPath = DAEMONS."/$sctlPath";
		//website's engine confs :
		$engine = new FileBasedConf("$path/engine/config/conf.json");
		$engine->set("server/daemons",[
			"kvs"=>$kvs->getConfPath(),
			"model_supervisor"=>$mss->getConfPath(),
			"sctl" => $sctlPath
		]);
		$engine->save();
		//website's confs :
		file_put_contents("$path/site/config/conf.json",new SiteConfTemplate(
			"localhost",$dbName,$pName,$dbPwd,
			$msConf->getSocketPath(),$pName,$mssUser,$mssPwd
		));
		//website's backup confs :
		$backup = new FileBasedConf("$path/cli/backup/config/conf.json");
		$backup->set("databases/$dbName",[
			"login" => $dbRootUser,
			"password" => $dbRootPwd,
			"host" => "localhost"
		]);
		$backup->set("mysql",$mysqlPath);
		$backup->set("mysqldump",$wfwConf->getString("mysqldump_path")??"mysqldump");
		$backup->save();
		//updator's confs :
		$updator = new FileBasedConf("$path/cli/updator/config/conf.json");
		$updator->set('project',$pName);
		$updator->save();
		//tester's confs :
		$tester = new FileBasedConf("$path/cli/tester/config/conf.tests.json");
		$tester->set("msserver/addr",$msConf->getSocketPath());
		$tester->save();
		//then, set the unix owner for the new project and give-it to the given user (apache,ngnix..)
		$exec("chmod -R 700 $path");
		$exec("chown -R $unixUser:$unixUser $path");
		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
		
		//create an admin user :
		$mainSocket = $msConf->getSocketPath();
		$attempts = 0;
		$maxAttempts = 20;
		$attemptsDelay = 250000;//us

		while($attempts < $maxAttempts && !file_exists($mainSocket)){
			$attempts++;
			fwrite(STDOUT,"Waiting for wfw-msserver.service (main socket) to be ready... (attempt n°$attempts/$maxAttempts)\n");
			usleep($attemptsDelay);
		}
		if(file_exists($mainSocket)){
			$socket = (new MSInstanceAddrResolver($msConf->getSocketPath()))->find($pName);

			while($attempts < $maxAttempts && !file_exists($socket)){
				$attempts++;
				fwrite(STDOUT,"Waiting for wfw-msserver.service ($pName socket) to be ready... (attempt n°$attempts/$maxAttempts)\n");
				usleep($attemptsDelay);
			}
			if(file_exists($socket)){
				$msAccess = new MSServerWriterAccess($socket, $mssUser, $mssPwd );
				$msAccess->login();
				$es = new DBBasedEventStore(
					new MySQLDBAccess("localhost", $dbName, $pName, $dbPwd),
					$msAccess,
					new DomainEventObserver(),
					new LightSerializer(new GZCompressor(),new PHPSerializer())
				);
				$ur = new UserRepository(new AggregateRootRepository($es));
				$usr = new User(
						new UUID(),new Login($pName),new Password($firstUser),
						new Email("changeMe@fakemail.com"),new InMemoryUserSettings(),
						new EnabledUser(),new Admin(),'WFWGlobal'
				);
				$ur->add($usr);
				$userCredentialsPath = "$tmpDir/$pName.cred";
				touch($userCredentialsPath);
				chmod($userCredentialsPath,700);
				file_put_contents($userCredentialsPath,"$pName\n$firstUser");
				fwrite(STDOUT,"User credentials path : $userCredentialsPath\n");
			}else throw new \Exception(
				"Unable to create an admin user. "
				."Socket $socket not found after $maxAttempts attempts and ".($maxAttempts*$attemptsDelay/1000)." ms"
			);
		}else throw new \Exception(
			"Unable to create an admin user. "
			."Socket $mainSocket not found after $maxAttempts attempts and ".($maxAttempts*$attemptsDelay/1000)." ms"
		);
	} else if($argvReader->exists('remove')){
		$project = $argvReader->get('remove')[0];
		if(!isset($data[$project]))
			throw new InvalidArgumentException("$project is not a registered project !");

		//KVS conf cleaning (global framework file):
		$kvsUser = $project."_msserver";
		$kvsContainer = $project."_db";
		$kvs = (new KVSConfs(ENGINE."/config/conf.json"))->getConfFile();
		$toSave = false;
		if(!is_null($kvs->get("users/$kvsUser"))){
			$users = $kvs->getArray("users");
			unset($users[$kvsUser]);
			$kvs->set("users",$users);
			$toSave = true;
		}
		if(!is_null($kvs->getArray("containers/$kvsContainer"))){
			$containers = $kvs->getArray("containers");
			unset($containers[$kvsContainer]);
			$kvs->set("containers",$containers);
			$toSave=true;
		}
		if($toSave) $kvs->save();

		//MSServer conf cleaning (global framework file):
		$mss = ($msConf = new MSServerPoolConfs(ENGINE."/config/conf.json"))->getConfFile();
		if(!is_null($mss->getArray("instances/$project"))){
			$instances = $mss->getArray("instances");
			unset($instances[$project]);
			$mss->set("instances",$instances);
			$mss->save();
		}
		//unlink is ROOT
		if(is_link(ROOT."/$project")) unlink(ROOT."/$project");
		//delete data from projects db
		unset($data[$project]);
		$db->write($data);

		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
	} else if($argvReader->exists('import')){
		$args = $argvReader->get("import");
		if(count($args)<2) throw new InvalidArgumentException(
				"At least two args required : projectName and import path !"
		);

		$pName = $args[0];
		$path = $args[1];
		$keepConf = $args[2]??null;
		if($keepConf === "-keepConf") $keepConf = true;
		else if(!is_null($keepConf))
			throw new InvalidArgumentException("Unknown arg $keepConf. Did you mean -keepConf ?");
		else $keepConf = false;
		if(!$validName($args[0]))
			throw new InvalidArgumentException("$pName is not a valid project name !");
		if(!is_dir($args[1]))
			throw new InvalidArgumentException("$path is not a valid directory !");
		if(!isset($data[$args[0]]))
			throw new InvalidArgumentException("Unknown project $pName");

		$pPath = dirname($data[$args[0]]);
		$siteConfChanged = false;
		if(!$keepConf && file_exists("$path/site/config/conf.json")){
			$siteConfChanged = true;
			$conf = new FileBasedConf("$path/site/config/conf.json");
			$pConf = new FileBasedConf("$pPath/site/config/conf.json");
			$conf->set("server/databases/default",$pConf->getArray("server/databases/default"));
			$conf->set("server/msserver",$pConf->getArray("server/msserver"));
			$conf->save();
		}
		$exec("cp -R \"$path/.\" \"$pPath\"");

		if($siteConfChanged){
			//rétabli le fichier d'origine.
			$conf->removeKey("server/databases/default");
			$conf->removeKey("server/msserver");
			$conf->save();
		}
		//install packages (will create appropriated symlinks)
		$packages = $conf->getArray("server/packages");
		if(is_array($packages) && count($packages)>0){
			$exec("wfw $pName package -install \"".implode("\" \"",$packages)."\"");
		}

		$wfwConf = new FileBasedConf(CLI."/wfw/config/conf.json");
		$unixUser = $wfwConf->getString("unix_user") ?? "www-data";

		//then, set the unix owner for the new project and give-it to the given user (apache,ngnix..)
		$exec("chmod -R 700 $pPath");
		$exec("chown -R $unixUser:$unixUser $pPath");
		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
	} else if($argvReader->exists('locate')){
		$args = $argvReader->get('locate');
		if(count($args) === 0) fwrite(STDOUT,ROOT.PHP_EOL);
		else if(isset($data[$args[0]])) fwrite(STDOUT,dirname($data[$args[0]]).PHP_EOL);
		else throw new InvalidArgumentException("$args[0] is not a registered project !");
	}else{
		throw new InvalidArgumentException("Unknown command $argv[1]");
	}
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_global WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	fwrite(
		STDOUT,
		"\e[31mWFW_global ERROR\e[0m : {$e->getMessage()}".PHP_EOL
	);
	exit(2);
}
exit(0);