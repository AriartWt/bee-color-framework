<?php
namespace wfw\engine\core\data\model\snapshoter;

use wfw\engine\core\data\DBAccess\SQLDB\PDOBasedDBAccess;
use wfw\engine\core\domain\events\observers\DomainEventObserver;
use wfw\engine\core\data\model\builder\IModelBuilder;
use wfw\engine\core\data\model\InMemoryEventBasedModel;
use wfw\engine\core\data\model\IModel;
use wfw\engine\core\data\query\RawQuery;
use wfw\engine\lib\data\string\serializer\ISerializer;

/**
 *  Crée un snapshot pour un model complet.
 */
class ModelSnapshoter implements IModelSnapshoter {
	public const MAX_EVENTS_LOAD = 10000;
	/** @var PDOBasedDBAccess $_db */
	private $_db;
	/** @var array $_models */
	private $_models;
	/** @var array $_modelsClass */
	private $_modelsClass;
	/** @var array $_lastEventNumber */
	private $_lastEventNumber;
	/** @var string $_snapshotDir */
	private $_snapshotDir;
	/** @var IModelBuilder $_modelBuilder */
	private $_modelBuilder;
	/** @var ISerializer $_serializer */
	private $_serializer;

	/**
	 * ModelSnapshoter constructor.
	 *
	 * @param PDOBasedDBAccess      $access
	 * @param string                $snapshotDirectory
	 * @param string[]              $models
	 * @param IModelBuilder $builder
	 * @param ISerializer   $serializer
	 */
	public function __construct(
		PDOBasedDBAccess $access,
		string $snapshotDirectory,
		array $models,
		IModelBuilder $builder,
		ISerializer $serializer
	){
		$this->_serializer = $serializer;
		$this->_modelBuilder = $builder;
		$this->_db = $access;
		$this->_modelsClass = (function(string... $models){return $models;})(...$models);
		$this->_models = [];
		if(!is_dir($snapshotDirectory)){
			throw new \InvalidArgumentException("$snapshotDirectory is not a valide directory ");
		}
		$this->_snapshotDir = $snapshotDirectory;
	}

	/**
	 * @return string
	 */
	private function getSnapshotPath():string{
		return $this->_snapshotDir."/models.snapshot";
	}

	/**
	 * @return null|ModelSnapshot
	 */
	private function getSnapshot():?ModelSnapshot{
		$modelFile = $this->getSnapshotPath();
		if(file_exists($modelFile)){
			return $this->_serializer->unserialize(file_get_contents($modelFile));
		}else return null;
	}

	/**
	 * @param InMemoryEventBasedModel[] $models Models impactés.
	 * @param int                       $from   Indice depuis lequel on récupère les événements
	 * @param int                       $to     Indice d'arrêt de récupération des événements
	 *
	 * @return int Nombre d'événements appliqués
	 */
	private function updateModels(array $models,int $from = 0,int $to = 0):int{
		$manager = new DomainEventObserver();
		foreach($models as $model){
			foreach($model->listenEvents() as $e){
				$manager->addEventListener($e,$model);
			}
		}
		//on détermine le nombre d'étapes
		if($to < 1){
			$to = $this->_db->execute(new RawQuery("
				SELECT count(*) FROM events
			"))->fetchColumn();
		}
		$nbSteps = ceil(($to - $from) / self::MAX_EVENTS_LOAD);
		$limit = self::MAX_EVENTS_LOAD;
		//On procéde à l'application des événements
		$totalEvents = 0;
		for($i = 0; $i < $nbSteps; $i++){
			$events = $this->_db->execute(new RawQuery("
				SELECT data 
				FROM events as e
				ORDER BY e.version ASC, e.generation_date ASC, e.id ASC
				LIMIT $limit OFFSET ".($from + ($i * $limit))."
			"));
			foreach($events as $e){
				$manager->dispatch($this->_serializer->unserialize($e["data"]));
			}

			$totalEvents += $events->rowCount();;
		}
		//On retourne le nombre d'événements appliqués
		return $totalEvents;
	}

	/**
	 *  Met à jour le snapshot
	 *
	 * @param string[] $modelsToRebuild Liste de models à reconstruire.
	 * @throws \InvalidArgumentException
	 */
	public function updateSnapshot(string... $modelsToRebuild):void{
		//On obtient le snapshot précédent s'il existe
		/*$models = $this->_models;*/
		$previous = $this->getSnapshot();
		if(!is_null($previous)){
			$this->_models = $previous->getModels();
			$this->_lastEventNumber = $previous->getLastEventNumber();
			foreach($modelsToRebuild as $m){
				if(is_string($m) && isset($this->_models[$m])){
					unset($this->_models[$m]);
				}
			}
		}else{
			$this->_lastEventNumber = 0;
		}

		//Si de nouveaux models ont été ajoutés, on les mets à jour
		$additionnalModels = [];
		foreach($this->_modelsClass as $model){
			if(!class_exists($model) || !is_a($model,IModel::class,true)){
				throw new \InvalidArgumentException("$model doesn't implements ".IModel::class);
			}else{
				$additionnalModels[$model] = $this->_modelBuilder->buildModel($model);
			}
		}
		$this->updateModels($additionnalModels, 0, $this->_lastEventNumber );

		foreach($additionnalModels as $k=>$m){
			$this->_models[$k]=$m;
		}
		//On met à jour tous les models
		$this->_lastEventNumber += $this->updateModels(
			$this->_models,
			$this->_lastEventNumber
		);
		$this->save();
	}

	/**
	 * @return IModel[]
	 */
	public function getModels():array{
		return $this->_models;
	}

	/**
	 *  Sauvegarde le snapshot
	 */
	private function save(){
		file_put_contents(
			$this->getSnapshotPath(),
			$this->_serializer->serialize(
				new ModelSnapshot(
					$this->_models,
					$this->_lastEventNumber
				)
			)
		);
	}

	/**
	 *  Met à jour le snapshot à la dernière version de chaque model
	 */
	public function rebuildSnapshot():void{
		$this->updateSnapshot(...$this->_modelsClass);
	}
}