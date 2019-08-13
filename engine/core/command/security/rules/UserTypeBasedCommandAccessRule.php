<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\security\ICommandAccessRuleFactory;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\UserType;

/**
 * Admin users have all permission, other user only the "any_user" permissions.
 * Public commands are made using the "public" (no registration needed) array.
 * Some commands can be attributed to the UserType::class => CommandClass[]
 * Some specific rules can be attribued to each commands CommandClass => [ ICommandAccessRuleClass => [] ]
 * AccessRules will be built and checked accordingly, or loaded from cache.
 *
 * Example :
 * [
 *      "public" => [], //no public command
 *      "any_user" => [ ChangePassword::class => [ ICommandAccessRuleClass => []] ],
 *       Client::class => [ Unregister::class => [] ]
 * ]
 */
final class UserTypeBasedCommandAccessRule implements ICommandAccessRule {
	public const CACHE_KEY = self::class."/results";

	public const ANY = "any user";
	public const NO_ID = " @noid@ ";
	public const PUBLIC = "public command";

	/** @var IUserModelAccess $_model */
	private $_model;
	/** @var array|mixed $_cache */
	private $_cache;
	/** @var ICommandAccessRuleFactory $_factory */
	private $_factory;
	/** @var ICacheSystem $_cacheSystem */
	private $_cacheSystem;
	/** @var ICommandAccessRule[][] $_permissions */
	private $_permissions;

	/**
	 * UserTypeBasedCommandAccessRule constructor.
	 *
	 * @param IUserModelAccess          $model
	 * @param ICommandAccessRuleFactory $factory
	 * @param ICacheSystem              $cache
	 * @param array                     $permissions
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		IUserModelAccess $model,
		ICommandAccessRuleFactory $factory,
		ICacheSystem $cache,
		array $permissions=[]
	){
		$this->_model = $model;
		$this->_factory = $factory;
		try{
			$this->_permissions = $this->checkPermissionsFormat($permissions);
		}catch(\Error | \Exception $e){var_dump((string)$e); }
		$this->_cache = $cache->get(self::CACHE_KEY) ?? [];
		$this->_cacheSystem = $cache;
	}

	/**
	 * @param array $permissions
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	private function checkPermissionsFormat(array $permissions):array{
		$res = $permissions;
		foreach(array_keys($permissions) as $key){
			if(!in_array($key,[self::PUBLIC,self::ANY])){
				if(!is_a($key,UserType::class,true)) throw new \InvalidArgumentException(
					"$key must implements ".UserType::class
				);
			}
		}
		foreach($permissions as $type=>$cmds){
			foreach($cmds as $cmd=>$rules){
				if(is_int($cmd)){
					if(!is_string($rules)) throw new \InvalidArgumentException(
						"In $type at offset $cmd : value must be a string"
					);
					$cmdClass = $rules;
				} else $cmdClass = $cmd;

				if(!is_a($cmdClass,ICommand::class,true)) throw new \InvalidArgumentException(
					"$cmdClass must implements ".ICommand::class
				);
				if(is_array($rules)){
					$rs = [];
					foreach ($rules as $class=>$params){
						if(is_int($class)){
							if(is_string($params)) $rs[] = $this->_factory->create($params);
						}else $rs[] = $this->_factory->create($params);
					}
					if(count($rs) === 0) $res[$type][$cmd] = new AllCommandsAllowed();
					else if(count($rs) === 1) $res[$type][$cmd] = $rs[0];
					else $res[$type][$cmd] = new AndCommandAccessRule(...$rs);
				}else if(is_bool($rules)) $res[$type][$cmd] = $rules
					? new AllCommandsAllowed()
					: new AllCommandsDenied();
				else $res[$type][$cmd] = new AllCommandsAllowed();
			}
		}
		return $res;
	}

	/**
	 * @param ICommand $cmd
	 * @return null|bool True if the command can be run, false otherwise. Null if not applicable
	 */
	public function checkCommand(ICommand $cmd): ?bool {
		return $this->checkCommandFrom($cmd,$this->_permissions);
	}

	/**
	 * @param ICommand $cmd
	 * @param ICommandAccessRule[][]    $permissions
	 * @return bool|null
	 */
	private function checkCommandFrom(ICommand $cmd,array $permissions): ?bool{
		$cmdClass = get_class($cmd);
		/** @var ICommandAccessRule $cached */
		$cached = $this->_cache[$cmd->getInitiatorId() ?? self::NO_ID][$cmdClass] ?? null;
		if(!is_null($cached)) return $cached->checkCommand($cmd);
		if(isset($permissions[self::PUBLIC][$cmdClass]))
			return $permissions[self::PUBLIC][$cmdClass]->checkCommand($cmd);
		else foreach($permissions[self::PUBLIC] as $class=>$value){
			if(is_a($cmdClass,$class,true)) return $this->setCache(
				$cmd->getInitiatorId(),$cmdClass,new AllCommandsAllowed()
			)->checkCommand($cmd);
		}
		if(is_null($cmd->getInitiatorId())) return false;
		$user = $this->_model->getById($cmd->getInitiatorId());
		if(!$user) return false;
		if($user->getType() instanceof Admin) return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,new AllCommandsAllowed()
		)->checkCommand($cmd);

		$userTypesClasses = array_diff(
			array_keys($permissions),
			[self::PUBLIC,self::ANY]
		);

		foreach($userTypesClasses as $userType){
			if (is_a($user->getType(), $userType)
				&& isset($permissions[$userType][$cmdClass])
			) return $this->setCache(
				$cmd->getInitiatorId(),
				$cmdClass,
				$permissions[$userType][$cmdClass]
			)->checkCommand($cmd);
		}

		//any check
		if(isset($permissions[self::ANY][$cmdClass])) return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,
			$permissions[self::ANY][$cmdClass]
		)->checkCommand($cmd);

		return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,
			new AllCommandsDenied()
		)->checkCommand($cmd);
	}

	/**
	 * Push the result into the cache and return the result
	 *
	 * @param string             $userId   User tested
	 * @param string             $cmdClass Command tested
	 * @param ICommandAccessRule $res      Rule to cache
	 * @return ICommandAccessRule $res
	 */
	private function setCache(?string $userId=null,string $cmdClass, ICommandAccessRule $res):ICommandAccessRule{
		$userId = $userId ?? self::NO_ID;
		if(!isset($this->_cache[$userId])) $this->_cache[$userId] = [$cmdClass=>$res];
		else $this->_cache[$userId][$cmdClass]=$res;
		$this->_cacheSystem->set(self::CACHE_KEY,$this->_cache);
		return $res;
	}
}