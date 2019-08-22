<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\query\IQuery;
use wfw\engine\core\query\security\IQueryAccessRuleFactory;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\UserType;

/**
 * Admin users have all permission, other user only the "any_user" permissions.
 * Public querys are made using the "public" (no registration needed) array.
 * Some querys can be attributed to the UserType::class => QueryClass[]
 * Some specific rules can be attribued to each querys QueryClass => [ IQueryAccessRuleClass => [] ]
 * AccessRules will be built and checked accordingly, or loaded from cache.
 *
 * Example :
 * [
 *      "public" => [], //no public query
 *      "any_user" => [ ChangePassword::class => [ IQueryAccessRuleClass => []] ],
 *       Client::class => [ Unregister::class => [] ]
 * ]
 */
final class UserTypeBasedQueryAccessRule implements IQueryAccessRule {
	public const CACHE_KEY = self::class."/results";

	public const ANY = "any user";
	public const PUBLIC = "public query";

	/** @var IUserModelAccess $_model */
	private $_model;
	/** @var array|mixed $_cache */
	private $_cache;
	/** @var IQueryAccessRuleFactory $_factory */
	private $_factory;
	/** @var ICacheSystem $_cacheSystem */
	private $_cacheSystem;
	/** @var IQueryAccessRule[][] $_permissions */
	private $_permissions;

	/**
	 * UserTypeBasedQueryAccessRule constructor.
	 *
	 * @param IUserModelAccess          $model
	 * @param IQueryAccessRuleFactory $factory
	 * @param ICacheSystem              $cache
	 * @param array                     $permissions
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		IUserModelAccess $model,
		IQueryAccessRuleFactory $factory,
		ICacheSystem $cache,
		array $permissions=[]
	){
		$this->_model = $model;
		$this->_factory = $factory;
		$this->_permissions = $this->checkPermissionsFormat($permissions);
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

				if(!is_a($cmdClass,IQuery::class,true)) throw new \InvalidArgumentException(
					"$cmdClass must implements ".IQuery::class
				);
				if(is_array($rules)){
					$rs = [];
					foreach ($rules as $class=>$params){
						if(is_int($class)){
							if(is_string($params)) $rs[] = $this->_factory->create($params);
						}else $rs[] = $this->_factory->create($params);
					}
					if(count($rs) === 0) $res[$type][$cmd] = new AllQueriesAllowed();
					else if(count($rs) === 1) $res[$type][$cmd] = $rs[0];
					else $res[$type][$cmd] = new AndQueryAccessRule(...$rs);
				}else if(is_bool($rules)) $res[$type][$cmd] = $rules
					? new AllQueriesAllowed()
					: new AllQueriesDenied();
				else $res[$type][$cmd] = new AllQueriesAllowed();
				if(!is_array($rules)) throw new \InvalidArgumentException(
					"$cmd rules must be an array"
				);
			}
		}
		return $res;
	}

	/**
	 * @param IQuery $cmd
	 * @return null|bool True if the query can be run, false otherwise. Null if not applicable
	 */
	public function checkQuery(IQuery $cmd): ?bool {
		return $this->checkQueryFrom($cmd,$this->_permissions);
	}

	/**
	 * @param IQuery $cmd
	 * @param IQueryAccessRule[][]    $permissions
	 * @return bool|null
	 */
	private function checkQueryFrom(IQuery $cmd,array $permissions): ?bool{
		$cmdClass = get_class($cmd);
		if(isset($permissions[self::PUBLIC][$cmdClass]))
			return $permissions[self::PUBLIC][$cmdClass]->checkQuery($cmd);
		/** @var IQueryAccessRule $cached */
		$cached = $this->_cache[$cmd->getInitiatorId()][$cmdClass] ?? null;
		if(!is_null($cached)) return $cached->checkQuery($cmd);

		$user = $this->_model->getById($cmd->getInitiatorId());
		if(!$user) return false;
		if($user->getType() instanceof Admin) return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,new AllQueriesAllowed()
		)->checkQuery($cmd);

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
			)->checkQuery($cmd);
		}

		//any check
		if(isset($permissions[self::ANY][$cmdClass])) return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,
			$permissions[self::ANY][$cmdClass]
		)->checkQuery($cmd);

		return $this->setCache(
			$cmd->getInitiatorId(),
			$cmdClass,
			new AllQueriesDenied()
		)->checkQuery($cmd);
	}

	/**
	 * Push the result into the cache and return the result
	 *
	 * @param string             $userId   User tested
	 * @param string             $cmdClass Query tested
	 * @param IQueryAccessRule $res      Rule to cache
	 * @return IQueryAccessRule $res
	 */
	private function setCache(string $userId,string $cmdClass, IQueryAccessRule $res):IQueryAccessRule{
		if(!isset($this->_cache[$userId])) $this->_cache[$userId] = [$cmdClass=>$res];
		else $this->_cache[$userId][$cmdClass]=$res;
		$this->_cacheSystem->set(self::CACHE_KEY,$this->_cache);
		return $res;
	}
}