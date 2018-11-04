<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\modelManager;

use wfw\daemons\modelSupervisor\server\components\writer\errors\ModelIndexAlreadyExists;
use wfw\daemons\modelSupervisor\server\components\writer\errors\ModelNotFound;

use wfw\engine\core\data\model\CrossModelSpecification;
use wfw\engine\core\data\model\ICrossModelAccess;
use wfw\engine\core\data\model\ICrossModelQuery;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\data\model\IEventListenerModel;
use wfw\engine\core\data\model\loaders\IModelLoader;
use wfw\engine\core\data\model\IModel;
use wfw\engine\core\data\model\storage\IModelStorage;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\core\domain\events\observers\DomainEventObserver;

/**
 * @brief Gère un ensemble de models
 */
final class ModelManager implements IModelManager, ICrossModelAccess {
	/** @var IModel[] $_models */
	private $_models;
	/** @var IModelLoader $_loader */
	private $_loader;
	/** @var IModelStorage $_storage */
	private $_storage;
	/** @var DomainEventObserver $_domainEventManager */
	private $_domainEventManager;
	/** @var IModel[] $_waitForSave */
	private $_waitForSave;

	/**
	 * ModelManager constructor.
	 *
	 * @param IModelLoader  $loader  Permet de charger les models depuis leur espace de stockage
	 * @param IModelStorage $storage Permet d'enregistrer les models sur un espace de stockage
	 */
	public function __construct(IModelLoader $loader,IModelStorage $storage) {
		$this->_models = [];
		$this->_loader = $loader;
		$this->_storage = $storage;
		$this->_waitForSave = [];
		$this->_domainEventManager = new MMDomainEventManager();

		$this->reloadModels();
	}

	/**
	 * Dispatche les événements contenus dans $eventList
	 *
	 * @param EventList $eventList Liste des événements à appliquer
	 */
	public function dispatch(EventList $eventList): void {
		foreach($eventList->toArray() as $e){
			$this->_domainEventManager->dispatch($e);
		}
	}

	/**
	 * Teste l'existence d'un model dans le manager
	 *
	 * @param string $name Classe du model à tester
	 *
	 * @return bool True si le model existe
	 */
	public function existsModel(string $name): bool {
		return isset($this->_models[$name]);
	}

	/**
	 * Retourne un model par sa classe
	 *
	 * @param string $name Classe du model
	 *
	 * @return IModel
	 * @throws ModelNotFound Si le model n'existe pas
	 */
	private function getModel(string $name):IModel{
		if($this->existsModel($name)){
			return $this->_models[$name];
		}else{
			throw new ModelNotFound("Unknown model $name");
		}
	}

	/**
	 * Teste l'existence d'un indexe dans un model
	 *
	 * @param string $model
	 * @param string $index
	 *
	 * @return bool True si l'indexe existe
	 * @throws ModelNotFound Si le model n'existe pas.
	 */
	public function existsIndex(string $model, string $index): bool {
		if($this->existsModel($model)){
			return $this->getModel($model)->existsIndex($index);
		}else{
			throw new ModelNotFound("Unknown model $model.");
		}
	}

	/**
	 * Supprime un index dans un model
	 *
	 * @param string $model Model concerné
	 * @param string $index Index à supprimer
	 *
	 * @return bool True si l'index a été supprimé
	 * @throws ModelNotFound Si le model n'existe pas.
	 */
	public function removeIndex(string $model, string $index): bool {
		if($this->existsModel($model)){
			$model = $this->getModel($model);
			if($model->existsIndex($index)){
				$model->removeIndex($index);
				$this->_waitForSave[get_class($model)] = [
					'model' => $model,
					'date' => microtime(true)
				];
				return true;
			}else{
				return false;
			}
		}else{
			throw new ModelNotFound("Unknown model $model.");
		}
	}

	/**
	 * Crée ou modifie un indexe sur un model.
	 *
	 * @param string                 $model          Model concerné
	 * @param string                 $index          Nom de l'index
	 * @param ISpecification $spec           Specification de l'index
	 * @param bool                   $modifyIfExists (optionnel defaut : false) Si true : modifie l'indexe s'il existe, lève une exception sinon.
	 *
	 * @return bool True si l'index a été modifié, false s'il a été créé.
	 * @throws ModelNotFound Si le model n'est pas trouvé
	 * @throws ModelIndexAlreadyExists Si l'indexe existe et que $modifyIfExists === false
	 */
	public function setIndex(
		string $model,
		string $index,
		ISpecification $spec,
		bool $modifyIfExists = false
	): bool {
		if($this->existsModel($model)){
			$model = $this->getModel($model);
			if($model->existsIndex($index)){
				if(!$modifyIfExists){
					throw new ModelIndexAlreadyExists("$index index already exists in $model !");
				}else{
					$model->removeIndex($index);
					$res = true;
				}
			}
			$model->createIndex($index,$spec);
			$this->_waitForSave[get_class($model)] = [
				'model' => $model,
				'date' => microtime(true)
			];
			return $res??false;
		}else{
			throw new ModelNotFound("Unknown model $model.");
		}
	}

	/**
	 * Effectue une recherche sur un model
	 *
	 * @param string $model Nom du model.
	 * @param mixed  $query Requête de recherche
	 *
	 * @return array Résultats
	 */
	public function query(string $model, $query): array {
		if($this->existsModel($model)){
			return $this->getModel($model)->find($query,$this);
		}else{
			throw new ModelNotFound("Unkown model $model");
		}
	}

	/**
	 * Déclenche la sauvegarde de tous les models contenus dans _waitForSave
	 * @return array Liste des models sauvegardés sous la forme "class"=>microtime(true) (date de derniere modification)
	 */
	public function save():array{
		$toSave = $this->_waitForSave;
		foreach($this->_domainEventManager->getImpactedListeners() as $class=>$arr){
			if(isset($toSave[$class]) && $toSave[$class]["date"]<$arr["date"]){
				$toSave[$class]["date"] = $arr["date"];
			}else{
				$toSave[$class] = $arr;
			}
		}
		foreach($toSave as $class=>$arr){
			$this->_storage->set($class,$arr["model"]);
		}
		$res = [];
		foreach($toSave as $class=>$arr){
			$res[$class] = $arr["date"];
		}
		return $res;
	}

	/**
	 * @return bool True si une sauvegarde est nécessaire, false sinon.
	 */
	public function needASave():bool{
		return count($this->_waitForSave) !== 0 || count($this->_domainEventManager->getImpactedListeners()) !== 0;
	}

	/**
	 * Remet à zero la liste des models en attente de sauvegarde si ceux-ci n'ont pas été modifiés entre temps.
	 * @param array $models Sous la forme "class"=>"date"
	 */
	public function reset(array $models):void{
		foreach($models as $class=>$time){
			if(isset($this->_waitForSave[$class]) && $this->_waitForSave[$class]["date"] <= $time){
				unset($this->_waitForSave[$class]);
			}
		}
		$this->_domainEventManager->reset($models);
	}

	/**
	 * Permet de recharger les models en les récupérant depuis leur espace de sotckage.
	 */
	public function reloadModels(): void {
		foreach($this->_models as $class=>$model){
			$this->_domainEventManager->removeEventListenerByClassName($class);
		}
		//On Cherche les models à gérer
		foreach($this->_loader->getModelList() as $modelName){
			/** @var IEventListenerModel $model */
			$model = $this->_loader->load($modelName);
			if(is_null($model)) throw new \InvalidArgumentException(
				"No version of $modelName is created yet ! Models have to be initialized !"
			);

			$this->_models[get_class($model)] = $model;
			foreach($model->listenEvents() as $listened){
				$this->_domainEventManager->addEventListener($listened,$model);
			}
		}
	}

	/**
	 * Permet d'éxecuter une requete cross-models. Cette requête retourne une specification qui
	 * sera appliquée à un subset de données ou à toutes les données du model à l'origine de la
	 * requête.
	 *
	 * @param ICrossModelQuery $query Requête
	 * @return CrossModelSpecification
	 */
	public function execute(ICrossModelQuery $query): CrossModelSpecification {
		return $query->createSpec(
			$this->query($query->getModel(),$query->getQuery())
		);
	}
}