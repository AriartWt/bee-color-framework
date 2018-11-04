<?php
namespace wfw\engine\core\data\model\loaders;

use wfw\engine\core\data\DBAccess\NOSQLDB\kvs\IKVSAccess;
use wfw\engine\core\data\DBAccess\NOSQLDB\kvs\KVSAccess;
use wfw\engine\core\data\model\InMemoryEventBasedModel;
use wfw\engine\core\data\model\IModel;

/**
 *  ModelLoader basé sur KVStore
 */
class KVStoreBasedModelLoader implements IModelLoader {
	/** @var KVSAccess $_kvs */
	private $_kvs;
	/** @var array $_allowedToLoad */
	private $_allowedToLoad;

	/**
	 * KVStoreBasedModelLoader constructor.
	 *
	 * @param IKVSAccess $access
	 * @param array              $allowedToLoad
	 */
	public function __construct(IKVSAccess $access, array $allowedToLoad) {
		$this->_kvs = $access;
		foreach($allowedToLoad as $model){
			if(!is_string($model) || !class_exists($model)
				|| !is_a($model,InMemoryEventBasedModel::class,true)){
				throw new \InvalidArgumentException("$model is not a valide model name !");
			}
		}
		$this->_allowedToLoad = $allowedToLoad;
	}

	/**
	 *  Charge un modèle
	 *
	 * @param string $model Clé permettant d'identifier le modèle à charger
	 *
	 * @return IModel|null
	 */
	public function load(string $model): ?IModel {
		if(!is_bool(array_search($model,$this->_allowedToLoad))){
			if($this->_kvs->exists($model)){
				return $this->_kvs->get($model);
			}else{
				return null;
			}
		}else{
			throw new \InvalidArgumentException("$model is not a valide model name !");
		}
	}

	/**
	 *  Retourne la liste des models acceptés par le loader
	 * @return array
	 */
	public function getModelList(): array {
		return $this->_allowedToLoad;
	}
}