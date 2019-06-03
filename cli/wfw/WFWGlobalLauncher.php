#!/usr/bin/php -q
<?php

use wfw\cli\wfw\templates\confs\SiteConfTemplate;
use wfw\cli\wfw\templates\db\DBTemplate;
use wfw\daemons\kvstore\server\conf\KVSConfs;
use wfw\daemons\modelSupervisor\client\MSInstanceAddrResolver;
use wfw\daemons\modelSupervisor\server\conf\MSServerPoolConfs;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
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
use wfw\engine\lib\network\http\HTTPRequest;
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
	new ArgvOpt(
			'update','Met à jour les fichiers wfw du projet ciblé avec les fichiers contenus dans le dossier spécifié '
			.'(update [-self(global) | -all(tous) | -projet,projet2,...(projets spécifiés)] [sources path]',
			2,null,true
	),
	new ArgvOpt(
			'maintenance',"Permet de mettre en maintenance un ou plusieurs projets."
			.'(state [-all(tous) | -projet,projet2,...(projets spécifiés)] [(optionnal) enable|disable (default:enable)]',
			null, null,true
	),
	new ArgvOpt('remove','Supprime un projet du gestionnaire',null,null,true),
	new ArgvOpt('locate',"Localiste le projet. Si pas d'argument, retourne le chemin vers le projet global",null,null,true),
	new ArgvOpt('restore',"Réstore tous les symlinks de configurations des projets vers /etc/wfw",null,null,true),
	new ArgvOpt('list',"Retourne une liste de tous les projets installés.",null,null,true),
	new ArgvOpt('[PROJECT] [cmd](args...)',"Execute une commande sur un projet",null,null,true)
])),$argv);

try{
	if(count($argv) < 2)
		throw new InvalidArgumentException("At least one arg expected ! --help for command usage");
	$db = new JSONFile(__DIR__.'/global.db.json');
	if(!file_exists(__DIR__.'/global.db.json')) $db->write([]);
	$data = $db->read(true);
	$validName = function(string $name):bool{
		return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$name)
			&& !in_array($name,["all","self","update","locate","remove","create","import","maintenance","list","restore"]);
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
	}else if($argvReader->exists('list')){
		foreach($data as $path){
			fwrite(STDOUT,dirname($path)."\n");
		}
	}else if($argvReader->exists('restore')){
		foreach($data as $project=>$p){
			$p=dirname($p);
			if(is_link("/etc/wfw/$project")){
				unlink("/etc/wfw/$project");
				fwrite(STDOUT,"Project $project config link /etc/wfw/$project removed.\n");
			}
			$exec("ln -s \"$p/site/config\" \"/etc/wfw/$project\"");
			fwrite(STDOUT,"Project $project config link /etc/wfw/$project created.\n");
		}
	} else if($argvReader->exists('maintenance')){
		$args = $argvReader->get('maintenance');
		$projects = $args[0];
		$enable = ($args[1] ?? "enable") === "enable";
		$state = "maintenance";
		$projects = strpos($projects,"-") === 0 ? substr($projects,1):$projects;
		$projects = explode(",",$projects);
		$pMap = []; $valids = array_merge(["all"],array_keys($data));
		foreach($projects as $v){
			if(!in_array($v,$valids)) throw new InvalidArgumentException(
				"Unknown project to change state : $v"
			);
			$pMap[$v]=isset($data[$v])?substr($data[$v],0,-4):null;
		}
		$projects = array_flip($projects);
		if(isset($projects["all"])){
			$pMap = $data;
			foreach($data as $k=>$v){
				$pMap[$k] = substr($v,0,-4);
			}
		}
		foreach($pMap as $name=>$path){
			if(!$enable && is_file("$path/wfw.$state")) {
				unlink("$path/wfw.$state");
				fwrite(STDOUT,"$name : $path/wfw.$state removed\n");
			}else if($enable && !is_file("$path/wfw.$state")){
				touch("$path/wfw.$state");
				fwrite(STDOUT,"$name : $path/wfw.$state created\n");
			}else fwrite(STDOUT,"$name : Nothing to do.\n");
		}
	}else if($argvReader->exists('update')){
		$args = $argvReader->get('update');
		$projects = $args[0];
		$path = $args[1];
		$projects = strpos($projects,"-") === 0 ? substr($projects,1):$projects;
		$projects = explode(",",$projects);
		$pMap = []; $valids = array_merge(["self","all"],array_keys($data));
		foreach($projects as $v){
			if(!in_array($v,$valids)) throw new InvalidArgumentException(
				"Unknown project to update : $v"
			);
			$pMap[$v]=isset($data[$v])?substr($data[$v],0,-4):null;
		}
		$projects = array_flip($projects);
		if(isset($projects["all"])){
			$pMap = $data;
			foreach($data as $k=>$v){
				$pMap[$k] = substr($v,0,-4);
			}
			$pMap["self"] = ROOT;
		}else if(isset($projects["self"])) $pMap["self"] = ROOT;

		//now that we have parsed the user request, we can process it
		//first get current wfw utility confs
		$wfwConf = new FileBasedConf(CLI."/wfw/config/conf.json");
		$unixUser = $wfwConf->getString("unix_user") ?? "www-data";
		$unixPerm = $wfwConf->getString("permissions") ?? 700;
		$tmpDir = $wfwConf->getString('tmp');
		if(strpos($tmpDir,"/")!==0) $tmpDir = ROOT."/$tmpDir";
		//next create a working dir in tmp folder
		if(!is_dir($tmp = "$tmpDir/wfw"))mkdir($tmp,700);
		foreach($pMap as $n=>$p){
			fwrite(STDOUT,"Starting $n update (working dir : $tmp/$n)...\n");
			if(!is_dir("$tmp/$n")) mkdir("$tmp/$n",700);
			//this is the list of all confs file that exists in the framework and that may be
			//updated (to add properties or move them, mostly)
			$confs = [
				"engine" => "engine/config/conf.json",
				"kvs" => "daemons/kvstore/server/config/conf.json",
				"mss" => "daemons/modelSupervisor/server/config/conf.json",
				"sctl" => "daemons/sctl/config/conf.json",
				"wfw" => "cli/wfw/config/conf.json",
				"updator" => "cli/updator/config/conf.json",
				"tester" => "cli/tester/config/conf.json",
				"backup" => "cli/backup/config/conf.json"
			];
			//we get all previous confs, and merge it with new confs from the update, to set
			//default values to new keys in case of
			foreach($confs as $c=>$v){
				touch("$tmp/$n/$c",700);
				file_put_contents("$tmp/$n/$c",'{}');
				$fconf = new FileBasedConf("$tmp/$n/$c",new JSONConfIOAdapter());
				$fconf->merge(new FileBasedConf("$path/$v"));
				$fconf->merge(new FileBasedConf("$p/$v"));
				file_put_contents(
					"$tmp/$n/$c",
					json_encode($fconf->getRawConf(),JSON_PRETTY_PRINT)
				);
				fwrite(STDOUT,"Conf $c ($v) merged...\n");
			}
			fwrite(STDOUT,"All confs have been successfully merged.\n");
			fwrite(STDOUT,"Stoping daemons...\n");
			//shutdown daemons while updating folders
			$exec("wfw self service stop -all");
			fwrite(STDOUT,"Daemons stoped.\n");

			$exec("cp -R \"$path/.\" \"$p\"");
			fwrite(STDOUT,"Updated files imported.\n");
			foreach($confs as $c=>$v){
				if(file_exists("$p/$v")) unlink("$p/$v");
				//mv conf file in the updated directory
				$exec("mv \"$tmp/$n/$c\" \"$p/$v\"");
			}
			fwrite(STDOUT,"Updated confs imported.\n");
			if(!is_link("/etc/wfw/$n") && $n!=="self"){
				fwrite(STDOUT,"No /etc/wfw/$n symlink found.\n");
				$exec("ln -s \"$p/site/config\" \"/etc/wfw/$n\"");
				fwrite(STDOUT,"Have been created.\n");
			}
			//reset all permissions
			$exec("chmod -R $unixPerm \"$p\"");
			$exec("chown -R $unixUser:$unixUser \"$p\"");
			fwrite(STDOUT,"All files now belongs to $unixUser:$unixUser ($unixPerm).\n");

			//clear all caches to be sure all will be reloaded
			(new HTTPRequest("http://127.0.0.1/wfw/clear_caches.php",[],["method" =>  "GET"]))
				->send();
			fwrite(STDOUT,"Cache reloaded.\n");

			fwrite(STDOUT,"Restarting daemons...\n");
			//start and restart daemons
			$exec("wfw self service start -all");
			$exec("wfw self service restart sctl");
			fwrite(STDOUT,"Daemons restarted.\n");
			rmdir("$tmp/$n");
			fwrite(STDOUT,"Working dir $tmp/$n removed.\n");
			fwrite(STDOUT,"$n successfully updated.\n\n");
		}
		rmdir($tmp);
		fwrite(STDOUT,"$tmp removed.\n");
		fwrite(STDOUT,"Done.\n");
	}else if($argvReader->exists('add')){
		$args = $argvReader->get('add');
		$path = "$args[1]/wfw";
		if(!is_file($path))
			throw new InvalidArgumentException("$args[1] is not a valid wfw project !");
		else{
			if(!$validName($args[0]))
				throw new InvalidArgumentException("$args[0] is not a valid project name !");
			fwrite(STDOUT,"Project $args[1] will be added...\n");
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
			//unlink an existing symlink with same name in case it's a corrupted link
			if(is_link("/etc/wfw/$args[0]")) unlink("/etc/wfw/$args[0]");
			//create the symlink to site confs into /etc/wfw
			$exec("ln -s \"$args[1]/$args[0]/config\" \"/etc/wfw/$args[0]\"");

			$a2confPath = CLI."/wfw/a2.d/$args[0].conf";
			if(file_exists($a2confPath)){
				unlink($a2confPath);
				fwrite(STDOUT,"Old file $a2confPath removed.\n");
			}
			fwrite(STDOUT,"Generating apache2 conf file $a2confPath...\n");
			$exec("cat \"".CLI."/wfw/templates/a2-site.conf.template\" | sed -e \"s+@ROOTPATH+$path+g\" >> \"$a2confPath\"");

			//write the project root path in DB
			$data[$args[0]] = $path;
			$db->write($data);
			fwrite(STDOUT,"$args[1] successfully added.\n");
		}
	} else if($argvReader->exists('create')){
		$args = $argvReader->get('create');
		$pName = $args[0];
		$path = $args[1];
		if(!$validName($args[0]))
			throw new InvalidArgumentException("$pName is not a valid project name !");
		if(!is_dir($args[1]))
			throw new InvalidArgumentException("$path is not a valid directory !");

		fwrite(STDOUT,"New $pName project will be created in $path/$pName...\n");
		$path = "$path/$pName";
		if(!file_exists($path)) mkdir($path);

		fwrite(STDOUT,"Generating project's template tree from ".ROOT."/cli/wfw/templates/site...\n");
		$exec("cp -Rp ".ROOT."/cli/wfw/templates/site $path");
		if(!is_dir("$path/site/package")) mkdir("$path/site/package");
		if(!is_dir("$path/site/webroot")) mkdir("$path/site/webroot");
		foreach(["Audio","Css","Image","JavaScript","Video","uploads"] as $v){
			if(!is_dir("$path/site/webroot/$v")) mkdir("$path/site/webroot/$v");
		}
		//create base folders and files
		$dirs = ['engine','cli','wfw'];
		fwrite(STDOUT,"Cloning files and folders from ".ROOT." into $path...\n");
		foreach($dirs as $dir){
			$exec("cp -Rp ".ROOT."/$dir $path");
			fwrite(STDOUT,"Files and folders from ".ROOT."/$dir cloned into $path.\n");
		}

		if(!file_exists("$path/daemons")) mkdir("$path/daemons");
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
		fwrite(STDOUT,"Files and folders from ".ROOT."/daemons cloned into $path...\n");
		$a2confPath = CLI."/wfw/a2.d/$pName.conf";
		if(file_exists($a2confPath)){
			unlink($a2confPath);
			fwrite(STDOUT,"Old file $a2confPath removed.\n");
		}
		fwrite(STDOUT,"Generating apache2 conf file $a2confPath...\n");
		$exec("cat \"".CLI."/wfw/templates/a2-site.conf.template\" | sed -e \"s+@ROOTPATH+$path+g\" >> \"$a2confPath\"");

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
		$adminMail = $wfwConf->getString("admin_mail");
		$dbFile = "$tmpDir/$pName.sql";
		$dbCredentials = "$tmpDir/$pName.credentials";
		$mysqlRootUser = $wfwConf->getString("mysql/root/login");
		$mysqlRootPwd = $wfwConf->getString("mysql/root/password");
		$unixUser = $wfwConf->getString("unix_user") ?? "www-data";
		$unixPerm = $wfwConf->getString("permissions") ?? 700;
		$mysqlPath = $wfwConf->getString("mysql/path") ?? "mysql";

		//so, now we have credentials. We will create the DB.
		$db = new DBTemplate($dbName,$pName,$dbPwd,$dbRootUser,$dbRootPwd);
		if(!file_exists("/tmp/$pName.sql")){
			touch($dbFile);
			chmod($dbFile,0600);
		}
		file_put_contents($dbFile,$db);
		//credentials for login into db with true root user
		if(!file_exists($dbCredentials)){
			touch($dbCredentials);
			chmod($dbCredentials,0600);
		}
		file_put_contents($dbCredentials,"[mysql]\npassword=$mysqlRootPwd");

		fwrite(STDOUT,"Creating EventStore db $dbName...\n");
		$exec(
			"\"$mysqlPath\" --defaults-file=\"{$dbCredentials}\" "
			."-h \"localhost\" -u \"$mysqlRootUser\" "
			."-e \"CREATE DATABASE IF NOT EXISTS \`$dbName\`\""
		);
		fwrite(STDOUT,"Creating EventStore db tables...\n");
		$exec(
			"\"$mysqlPath\" --defaults-file=\"{$dbCredentials}\" "
			."-h \"localhost\" -u \"$mysqlRootUser\" "
			."\"$dbName\" < \"$dbFile\""
		);
		//Delete files from /tmp
		unlink($dbCredentials);
		unlink($dbFile);
		fwrite(STDOUT,"$dbName created.\n");
		//now db and two users have been created : website and root for the new db
		//we have to edit all config files everywhere \o/

		fwrite(STDOUT,"Creating and configuring KVS containers...\n");
		//start with KVS (global framework file):
		$kvs = (new KVSConfs(
				ENGINE."/config/conf.json",
				null,
				DAEMONS,
				true)
			)->getConfFile();
		$users = $kvs->getArray("users");
		$users[$kvsUser] = [ "password" => $kvsPwd ];
		$kvs->set("users",$users);
		$kvs->set("admin_mail",$adminMail);
		$containers = $kvs->getArray("containers");
		$containers[$kvsContainer] = [
			"permissions" => [
				"users" => [ $kvsUser => [ "read" => true, "write" => true, "admin" => true ] ],
				"default_storage" => "IN_MEMORY_PERSISTED_ON_DISK"
			]
		];
		$kvs->set("containers",$containers);
		$kvs->save();
		fwrite(STDOUT,"KVS containers ready.\n");

		fwrite(STDOUT,"Creating and configuring MSServer instance...\n");
		//next MSServer (global framework file):
		$mss = ($msConf = new MSServerPoolConfs(
				ENGINE."/config/conf.json",null,DAEMONS,true)
			)->getConfFile();
		$mss->set("admin_mail",$adminMail);
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
		fwrite(STDOUT,"MSServer instance ready.\n");

		fwrite(STDOUT,"Configuring project...\n");
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

		//sctl's confs
		$sctlConf = new FileBasedConf($sctlPath);
		$sctlConf->set("admin_mail",$adminMail);
		$sctlConf->set("auth.pwd_owner",$wfwConf->getString("unix_user"));
		$sctlConf->save();

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
		fwrite(STDOUT,"Project configured.\n");

		//then, set the unix owner for the new project and give-it to the given user (apache,ngnix..)
		$exec("chmod -R $unixPerm $path");
		$exec("chown -R $unixUser:$unixUser $path");
		fwrite(STDOUT,"Project now belongs to $unixUser:$unixUser ($unixPerm).\n");
		fwrite(STDOUT,"Restarting daemons...\n");
		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
		fwrite(STDOUT,"Daemons restarted.\n");

		fwrite(STDOUT,"Creating default admin user...\n");
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
				fwrite(STDOUT,"Admin user created. Credentials path : $userCredentialsPath\n");
				fwrite(STDOUT,"\e[33m[WARN] Move those credentials in a safe place, they can't be generated again.\n");
				fwrite(STDOUT,"\e[0mProject successfully created.\n");
			}else throw new \Exception(
				"Unable to create an admin user. "
				."Socket $socket not found after $maxAttempts attempts and ".($maxAttempts*$attemptsDelay/1000)." ms"
			);
		}else throw new \Exception(
			"Unable to create an admin user. "
			."Socket $mainSocket not found after $maxAttempts attempts and ".($maxAttempts*$attemptsDelay/1000)." ms"
		);
	} else if($argvReader->exists('remove')){
		$args = $argvReader->get('remove');
		$project = $args[0];
		array_shift($args);
		$prompt = !isset($args["-no-prompt"]);
		if(!isset($data[$project]))
			throw new InvalidArgumentException("$project is not a registered project !");
		else $pName = dirname($data[$project]);
		if($prompt){
			fwrite(STDOUT,"Do you really want to remove $project ? (y/n) : ");
			if(!filter_var(preg_replace(["/^y$/","/^n$/"],["yes","no"],fgets(STDIN)), FILTER_VALIDATE_BOOLEAN)){
				fwrite(STDOUT,"$project will not be removed.\n");
				exit(0);
			}
		}
		fwrite(STDOUT,"$project will be removed...\n");

		fwrite(STDOUT,"Removing KVS containers...\n");
		//KVS conf cleaning (global framework file):
		$kvsUser = $project."_msserver";
		$kvsContainer = $project."_db";
		$kvs = (new KVSConfs(
				ENGINE."/config/conf.json",
				null,
				DAEMONS,
				true)
			)->getConfFile();
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
		fwrite(STDOUT,"KVS containers removed.\n");

		fwrite(STDOUT,"Removing MSserver instances...\n");
		//MSServer conf cleaning (global framework file):
		$mss = ($msConf = new MSServerPoolConfs(
				ENGINE."/config/conf.json",null,DAEMONS,true)
			)->getConfFile();
		if(!is_null($mss->getArray("instances/$project"))){
			$instances = $mss->getArray("instances");
			unset($instances[$project]);
			$mss->set("instances",$instances);
			$mss->save();
		}
		fwrite(STDOUT,"MSServer instances removed.\n");
		//unlink ROOT
		if(is_link(ROOT."/$project")){
			unlink(ROOT."/$project");
			fwrite(STDOUT,ROOT."/$project link removed.\n");
		}
		//unlink conf link
		if(is_link("/etc/wfw/$project")) unlink("/etc/wfw/$project");
		fwrite(STDOUT,"/etc/wfw/$project link removed.\n");
		if(is_file(CLI."/wfw/a2.d/$project.conf")){
			unlink(CLI."/wfw/a2.d/$project.conf");
			fwrite(STDOUT,CLI."/wfw/a2.d/$project.conf removed.\n");
		}
		fwrite(STDOUT,"Removing project path from wfw'db...\n");
		//delete data from projects db
		unset($data[$project]);
		$db->write($data);
		fwrite(STDOUT,"$project path removed.\n");

		fwrite(STDOUT,"Restarting daemons...\n");
		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
		fwrite(STDOUT,"Daemons restarted.\n");
		fwrite(STDOUT,"$project have been successfully removed.\n");
		fwrite(STDOUT,"\e[96m[INFO] $project folder still remains on disk ($pName) for safety concerns.\n"
				."If your intent was to totaly remove this project, please do it manually following this steps :\n"
				."\t- remove all logs (defaults : /var/log/wfw/kvs/containers & /var/log/wfw/msserver/instances)\n"
				."\t- remove project files (default : /srv/wfw/$project)\n"
				."\t- remove all kvs data (defaults : /srv/wfw/global/kvstore/data/kvs_db/${project}_db)\n"
				."\t- remove all msserver data (defaults : /srv/wfw/global/modelSupervisor/data/$project)\n"
				."\t- delete mysql db (default : ${project}_EventStore)\n"
				."\t- delete mysql user (default : ${project}_website)\n"
				."\t- disable site confs in apache2 (depends on your config)\n"
		);
		fwrite(STDOUT,"\e[33m[WARN] If you create a project with the same name without cleaning up,"
			." all dbs, datas and files will be overrwritten. Some files and folder of the old project may "
			."remains if not replaced by new ones in the new project.\n"
		);
	} else if($argvReader->exists('import')){
		$args = $argvReader->get("import");
		if(count($args)<2) throw new InvalidArgumentException(
				"At least two args required : projectName and import path !"
		);

		$pName = $args[0];
		$path = $args[1];
		$args = array_flip(array_slice($args,2));
		$keepConf = isset($args["-keepConf"]);
		$prompt = !isset($args["-no-prompt"]);
		if(!$validName($pName))
			throw new InvalidArgumentException("$pName is not a valid project name !");
		if(!is_dir($path))
			throw new InvalidArgumentException("$path is not a valid directory !");
		if(!isset($data[$pName]))
			throw new InvalidArgumentException("Unknown project $pName");

		$pPath = dirname($data[$pName]);
		if($prompt){
			fwrite(STDOUT,"Do you really want to import $path into $pName project's path $pPath ? (y/n) : ");
			if(!filter_var(preg_replace(["/^y$/","/^n$/"],["yes","no"],fgets(STDIN)), FILTER_VALIDATE_BOOLEAN)){
				fwrite(STDOUT,"$path will not be imported.\n");
				exit(0);
			}
		}
		fwrite(STDOUT,"$path will be imported into $pPath...\n");
		$siteConfChanged = false;
		if(!$keepConf && file_exists("$path/site/config/conf.json")){
			$siteConfChanged = true;
			$conf = new FileBasedConf("$path/site/config/conf.json");
			$pConf = new FileBasedConf("$pPath/site/config/conf.json");
			$conf->set("server/databases/default",$pConf->getArray("server/databases/default"));
			$conf->set("server/msserver",$pConf->getArray("server/msserver"));
			$conf->save();
			fwrite(STDOUT,"Project conf updated.\n");
		}
		fwrite(STDOUT,"Copying $path files and folder into $pPath...\n");
		$exec("cp -R \"$path/.\" \"$pPath\"");
		fwrite(STDOUT,"Files and folders copied.\n");

		if($siteConfChanged){
			//rétabli le fichier d'origine.
			$conf->removeKey("server/databases/default");
			$conf->removeKey("server/msserver");
			$conf->save();
		}
		fwrite(STDOUT,"Project packages will be installed...\n");
		//install packages (will create appropriated symlinks)
		$packages = $conf->getArray("server/packages");
		if(is_array($packages) && count($packages)>0){
			$exec("wfw $pName package -install \"".implode("\" \"",$packages)."\"");
		}
		fwrite(STDOUT,"Project packages installed.\n");

		$wfwConf = new FileBasedConf(CLI."/wfw/config/conf.json");
		$unixUser = $wfwConf->getString("unix_user") ?? "www-data";
		$unixPerm = $wfwConf->getString("permissions") ?? 700;

		if(!is_link("/etc/wfw/$pName"))
			$exec("ln -s \"$pPath/site/config\" \"/etc/wfw/$pName\"");

		$a2confPath = CLI."/wfw/a2.d/$pName.conf";
		if(!file_exists($a2confPath)){
			fwrite(STDOUT,"Apache2 conf file $a2confPath not found for this project. Generating a new one...\n");
			$exec("cat \"".CLI."/wfw/templates/a2-site.conf.template\" | sed -e \"s+@ROOTPATH+$pPath+g\" >> \"$a2confPath\"");
			fwrite(STDOUT,"Apache2 conf file created.\n");
		}

		//then, set the unix owner for the new project and give-it to the given user (apache,ngnix..)
		$exec("chmod -R $unixPerm $pPath");
		$exec("chown -R $unixUser:$unixUser $pPath");
		fwrite(STDOUT,"$pPath files and folders now belongs to $unixUser:$unixUser ($unixPerm)\n");

		fwrite(STDOUT,"Cleaning caches...\n");
		//clear all caches to be sure all will reloaded.
		(new HTTPRequest("http://127.0.0.1/wfw/clear_caches.php",[],["method" =>  "GET"]))
			->send();
		fwrite(STDOUT,"Cahes cleaned\n");
		fwrite(STDOUT,"Restarting daemons...\n");
		//restart all daemons to take conf changes in consideration
		$exec("wfw self service restart -all");
		fwrite(STDOUT,"Daemons restarted.\n");
		fwrite(STDOUT,"$path successfully imported into $pPath.\n");
		fwrite(STDOUT,"\e[33m[WARN] If some files have been removed in this project,"
			." they havn't been removed by the import command. You must do it manually.\n"
		);
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