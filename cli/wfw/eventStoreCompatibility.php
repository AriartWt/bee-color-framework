#!/usr/bin/php -q
<?php

use wfw\Autoloader;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\core\data\DBAccess\SQLDB\MySQLDBAccess;
use wfw\engine\core\data\query\RawQuery;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;

require_once(dirname(__DIR__)."/init.environment.php");

/**
 * @param $search
 * @param $replace
 * @param $subject
 * @param $pos
 * @return mixed|null
 */
function str_replace_first($search, $replace, $subject, &$pos) {
	$pos = strpos($subject, $search);
	if ($pos !== false) {
		return substr_replace($subject, $replace, $pos, strlen($search));
	}else return null;
}


/**
 *
 * @param string $serialized Serialized string to change
 * @param array  $params ["search" => "replace"]
 * @return string Changed serialisation string.
 */
function changeSerializedNamespace(
		string $serialized,
		array $params
):string{
	$res = $serialized;
	foreach($params as $before=>$after){
		$correctedLength = strlen($after)-strlen($before);
		while($replaced = str_replace_first($before,$after,$res,$pos)){
			$res = $replaced;
			$tmp = array_reverse(str_split(substr($res,0,$pos)));
			if($correctedLength !== 0){
				$backward = 0;
				$dotsFound = 0;
				$nullChar = false;
				$lastNumber = [];
				while($dotsFound < 2){
					if($tmp[$backward] === ':') $dotsFound++;
					else if($tmp[$backward] === "\0") $nullChar = true;
					else if($tmp[$backward] !== '"') $lastNumber[]=$tmp[$backward];
					$backward++;
				}
				$lastNumber = intval(implode('',array_reverse($lastNumber)));
				//start
				$st = substr($res,0,$pos-(strlen($lastNumber)+3+($nullChar ? 1 : 0)));
				$md = ':'.($lastNumber+$correctedLength).":";
				$en = "\"".($nullChar ? "\0" : '').substr($res,$pos);

				$res = $st.$md.$en;
			}
		}
	}
	return $res;
}

/**
 * @param $id
 * @return string
 */
function readableId($id):string{
	return \wfw\engine\lib\PHP\types\UUID::restoreDashes($id);
}

foreach(array_slice($argv,1) as $path){
	if(is_string($path) && is_dir($path)){
		(new Autoloader([],$path))->register(false,true);
		$engineConf = new FileBasedConf("$path/engine/config/conf.json",$io = new JSONConfIOAdapter());
		$siteConf = new FileBasedConf("$path/site/config/conf.json",$io);
		$engineConf->merge($siteConf);

		$db = new MySQLDBAccess(
			$engineConf->getString("server/databases/default/host"),
			$dbName = $engineConf->getString("server/databases/default/database"),
			$engineConf->getString("server/databases/default/login"),
			$engineConf->getString("server/databases/default/password")
		);

		$aggregates = $db->execute(new RawQuery("SELECT hex(id) as id,type FROM aggregates"))->fetchAll();
		$events = $db->execute(new RawQuery("SELECT hex(id) as id,type,data FROM events"))->fetchAll();
		$commands = $db->execute(new RawQuery("SELECT hex(id) as id,type,data FROM commands"))->fetchAll();
		//$snapshots = $db->execute(new RawQuery("SELECT unhex(id),data FROM snapshots"))->fetchAll();

		fwrite(STDOUT,"$dbName data loaded, begin transaction...\n");
		$db->beginTransaction();
		try{
			$compressor = new GZCompressor();
			$serializer = new LightSerializer($compressor,new PHPSerializer());
			fwrite(STDOUT,count($aggregates)." aggregates to check...\n");
			$aggregatesChanged = 0;
			foreach($aggregates as $k=>$aggregate){
				$type = $aggregate["type"];
				if(strpos($type,"wfw\\engine\\package\\users") !== 0){
					$aggregate["type"] = str_replace(
						"engine\\package",
						"modules\\BeeColor",
						$type
					);
					$res = $db->execute((new RawQuery(
						"UPDATE aggregates SET type=? WHERE id=unhex(?)"
					))->addParams([
						$aggregate["type"],
						$aggregate["id"]
					]));
					if($res->rowCount() > 0){
						$aggregatesChanged++;
						fwrite(
							STDOUT,
							"Aggregate ".readableId($aggregate["id"])." updated from type $type to ".$aggregate["type"]."\n"
						);
					}else fwrite(STDOUT,"\e[41mRow not updated.\e[0m\n");
				}
			}
			fwrite(STDOUT,"\e[32mAggregates updated ($aggregatesChanged changes)\e[0m\n");
			fwrite(STDOUT,count($commands)." commands to check...\n");
			$commandsChanged = 0;
			foreach($commands as $k=>$command){
				$type = $command["type"];
				if(strpos($type,"wfw\\engine\\package\\users") !== 0){
					$command["type"] = str_replace(
						"engine\\package",
						"modules\\BeeColor",
						$type
					);
					$command["data"] = $compressor->compress(
						$replaced = changeSerializedNamespace(
							$raw = $compressor->decompress($command["data"]),
							[
								"wfw\\engine\\package\\contact" => "wfw\\modules\\BeeColor\\contact",
								"wfw\\engine\\package\\news" => "wfw\\modules\\BeeColor\\news"
							]
						)
					);
					$unserialized = $serializer->unserialize($command["data"]);
					if($unserialized instanceof \wfw\engine\core\command\ICommand){
						$res = $db->execute((new RawQuery(
							"UPDATE commands SET type=?, data=? WHERE id=unhex(?)"
						))->addParams([
							$command["type"],
							$command["data"],
							$command["id"]
						]));
						if($res->rowCount() > 0){
							$commandsChanged++;
							fwrite(STDOUT,
								"Aggregate ".readableId($command["id"])
								." updated from type $type to ".$command["type"]."\n"
							);
						}else fwrite(STDOUT,"\e[41mRow not updated.\e[0m\n");
					}else throw new Error(
						"Unable to load command ".$command["type"]." (previously $type)"
						." (id: ".readableId($command["id"]).")"
					);
				}
			}
			fwrite(STDOUT,"\e[32mCommands updated ($commandsChanged changes)\e[0m\n");
			fwrite(STDOUT,count($events)." events to check...\n");
			$eventsChanged = 0;
			foreach($events as $k=>$event){
				$type = $event["type"];
				if(strpos($type,"wfw\\engine\\package\\users") !== 0){
					$events["type"] = str_replace(
						"engine\\package",
						"modules\\BeeColor",
						$type
					);
					$event["data"] = $compressor->compress(
						$replaced = changeSerializedNamespace(
							$raw = $compressor->decompress($event["data"]),
							[
								"wfw\\engine\\package\\contact" => "wfw\\modules\\BeeColor\\contact",
								"wfw\\engine\\package\\news" => "wfw\\modules\\BeeColor\\news"
							]
						)
					);
					$unserialized = $serializer->unserialize($event["data"]);
					if($unserialized instanceof \wfw\engine\core\domain\events\IDomainEvent){
						$res = $db->execute((new RawQuery(
							"UPDATE events SET type=?, data=? WHERE id=unhex(?)"
						))->addParams([
							$event["type"],
							$event["data"],
							$event["id"]
						]));
						if($res->rowCount() > 0){
							$eventsChanged++;
							fwrite(
								STDOUT,
								"Aggregate ".readableId($event["id"])." updated from type $type to ".$event["type"]."\n"
							);
						}else fwrite(STDOUT,"\e[41mRow not updated.\e[0m\n");
					}else throw new Error(
						"Unable to load event ".$event["type"]." (previously $type)"
						." (id: ".readableId($event["id"]).")"
					);
				}
			}
			fwrite(STDOUT,"\e[32mEvents updated ($eventsChanged changes)\e[0m\n");
			$db->commit();
			fwrite(STDOUT,"\e[32m$dbName updated. Please remove msserver snapshots and restart daemons.\e[0m\n");
		}catch(Error | Exception $e){
			$db->rollBack();
			fwrite(STDOUT,"\e[31m$dbName changes discarded: \e[0m\n");
			fwrite(STDOUT,"\e[41m$e\e[0m\n");
		}
	}else{
		fwrite(STDOUT,"\e[31m$path is not a valid directory.\e[0m\n");
		exit(1);
	}
}
