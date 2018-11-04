<?php
namespace wfw\engine\core\data\model\loaders;

use wfw\engine\core\data\DBAccess\NOSQLDB\redis\RedisAccess;
use wfw\engine\core\data\DBAccess\NOSQLDB\redis\IRedis;
use wfw\engine\core\data\model\InMemoryEventBasedModel;
use wfw\engine\core\data\model\IModel;

/**
 * Class ModelLoader
 *
 * @package wfw\engine\core\data\model
 */
class RedisBasedModelLoader implements IModelLoader {
	/** @var IRedis $_redis */
	private $_redis;
	/** @var array $_toload */
	private $_toload;

	/**
	 * ModelLoader constructor.
	 *
	 * @param RedisAccess           $access
	 * @param array                 $allowedToLoad
	 */
	public function __construct(RedisAccess $access,array $allowedToLoad) {
		/** @var IRedis $access */
		$this->_redis = $access;
		foreach($allowedToLoad as $model){
			if(!is_string($model) || !class_exists($model) || !is_a($model,InMemoryEventBasedModel::class,true)){
				throw new \InvalidArgumentException("$model is not a valide model name !");
			}
		}
		$this->_toload = $allowedToLoad;
	}

	/**
	 *  Charge un modèle
	 *
	 * @param string $model Clé permettant d'identifier le modèle à charger
	 *
	 * @return IModel|null
	 */
	public function load(string $model): ?IModel {
		if(!is_bool(array_search($model,$this->_toload))){
			$res = null;
			if($this->_redis->exists($model)){
				$model = $this->_redis->get($model);
				if(!is_null($model)){
					$res = unserialize($model);
				}
			}
			return $res;
		}else{
			throw new \InvalidArgumentException("$model is not a valide model name !");
		}
	}

	/**
	 *  Retourne la liste des models acceptés par le loader
	 * @return array
	 */
	public function getModelList(): array {
		return $this->_toload;
	}
}