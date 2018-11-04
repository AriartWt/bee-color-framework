<?php
namespace wfw\engine\core\data\DBAccess\NOSQLDB\redis;

use Redis;
use wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors\AuthentificationFailed;
use wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors\ConnectionFailed;
use wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors\RedisFailure;
use wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors\RedisExtensionNotFound;

/**
 *  Décorateur Redis. Connecte et entre automatiquement le mot de passe dans redis pour un accés simplifié.
 *        Leve une exception si l'extension Redis n'est pas installée.
 *        Leve une exception si le mot de passe fourni est incorrect.
 */
final class RedisAccess {
	/**
	 *  Connexion à un serveur redis
	 * @var Redis $_redis
	 */
	private $_redis;
	/** @var string $_host */
	private $_host;
	/** @var int $_port */
	private $_port;
	/** @var null|string $_password */
	private $_password;
	/** @var string $_namespace */
	private $_namespace;
	/** @var array $_argsName */
	private $_argsName = [];

	/**
	 *  RedisRepository constructor.
	 *
	 * @param string      $host     Nom de l'host du serveur Redis.
	 * @param int         $port     Nom du port du serveur Redis.
	 * @param null|string $password (optionnel) Mot de passe.
	 *
	 * @throws RedisExtensionNotFound
	 */
	public function __construct(string $host, int $port, ?string $password=null) {
		if(class_exists("Redis")){
			$this->_host = $host;
			$this->_port = $port;
			$this->_password = $password;

			$reflect = new \ReflectionClass(IRedis::class);
			foreach($reflect->getMethods() as $method){
				$params = [];
				foreach($method->getParameters() as $param){
					$params[]=$param->getName();
				}
				$this->_argsName[$method->getName()]=$params;
			}

			$this->initRedisClient();
		}else{
			throw new RedisExtensionNotFound("Current PHP version does'nt support REDIS. Please install php-redis and restart.");
		}
	}

	/**
	 *  Défini un namespace pour toutes les opérations.
	 * @param string|null $namespace Espace de nom
	 */
	public function setCurrentNamespace(?string $namespace){
		$this->_namespace = $namespace;
	}

	/**
	 *  Obtient le namespace courant.
	 * @return string
	 */
	public function getCurrentNamespace():string{
		return $this->_namespace;
	}

	/**
	 *  Initialise la connexion au serveur Redis.
	 *
	 * @throws AuthentificationFailed
	 */
	private function initRedisClient():void{
		$this->_redis = new Redis();
		$this->_redis->connect($this->_host,$this->_port);
		if(!is_null($this->_password)){
			if(!$this->_redis->auth($this->_password)){
				$this->_redis->close();
				throw new AuthentificationFailed("Cannot connect to $this->_host:$this->_port. Access denied -- wrong password");
			}
		}
		if($this->_redis->ping() !== "+PONG"){
			throw new ConnectionFailed("Connection error");
		}
	}

	/**
	 * @return array
	 */
	public function __sleep(){
		$this->_redis->close();
		$this->_redis = null;
		return ["_host","_port","_password","_argsName","_namespace"];
	}

	public function __wakeup() {
		$this->initRedisClient();
	}

	public function __destruct() {
		if(!is_null($this->_redis)){
			$this->_redis->close();
			$this->_redis = null;
		}
	}

	/**
	 * @param string $name      Methode
	 * @param array  $arguments Arguments
	 *
	 * @return mixed
	 * @throws RedisFailure
	 */
	public function __call($name, $arguments) {
		if(method_exists($this->_redis,$name)){
			if($this->_namespace !== ""){
				foreach($this->_argsName[$name] as $k=>$param){
					if(strpos($param,"key") === 0 && isset($arguments[$k])){
						if(is_array($arguments[$k])){
							foreach($arguments[$k] as &$tmpArg){
								$tmpArg = $this->_namespace.$tmpArg;
							}
						}else if(is_string($arguments[$k])){
							$arguments[$k] = $this->_namespace.$arguments[$k];
						}
					}
				}
			}
			return $this->_redis->$name(...$arguments);
		}else{
			throw new RedisFailure("Method not found : $name");
		}
	}
}