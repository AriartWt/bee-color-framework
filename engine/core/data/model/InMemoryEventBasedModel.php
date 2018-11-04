<?php
namespace wfw\engine\core\data\model;

use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\core\data\specification\ISpecification;

/**
 *  Model de lecture de données de base
 */
abstract class InMemoryEventBasedModel implements IEventListenerModel {
	/** @var ISpecification[] $_indexed */
	protected $_indexes=[];
	/** @var IModelObject[][]|string[][] $_indexes */
	protected $_indexed=[];
	/** @var IModelObject[] $_all */
	protected $_all=[];
	/** @var IModelSearcher $_searcher */
	private $_searcher;
	/** @var int $_length */
	private $_length;

	/**
	 * Repository constructor.
	 *
	 * @param IModelSearcher $searcher Objet permettant d'effectuer des recherches dans le
	 *                                 repository
	 */
	public function __construct(IModelSearcher $searcher) {
		$this->_searcher = $searcher;
		$this->_indexed["id"]=[];
		$this->_length = 0;
		$this->updateIndexList();
	}

	/**
	 *  Retourne la liste des classes des événements qui sont écoutés par le model
	 * @return string[]
	 */
	public abstract function listenEvents():array;

	/**
	 *  Crée un index sur le critère $spec lors de l'ajout d'un nouvel aggregat. Trie les aggrégat déjà présent pour
	 *        rechercher ceux qui correspondent à l'index.
	 *
	 * @param string                 $key  Clé de l'index
	 * @param ISpecification $spec Spec permettant de tester l'aggrégat.
	 */
	public function createIndex(string $key, ISpecification $spec) {
		if(!$this->existsIndex($key)){
			$this->_indexes[$key]=$spec;
			$this->_indexed[$key]=[];
			foreach($this->_all as $item){
				if($spec->isSatisfiedBy($item)){
					$this->_indexed[$key][]=$item;
				}
			}
		}else throw new \InvalidArgumentException("$key already exists !");
	}

	/**
	 *  Supprime un index du repository
	 *
	 * @param string $key Index à supprimer
	 */
	public function removeIndex(string $key) {
		if($this->existsIndex($key)){
			if($key !== "id"){
				array_splice($this->_indexes,array_search($key,array_keys($this->_indexes)),1);
				array_splice($this->_indexed,array_search($key,array_keys($this->_indexed)),1);
			}else throw new \InvalidArgumentException("Cannot remove id index !");
		}else throw new \InvalidArgumentException("$key is not an existing index !");
	}

	/**
	 *  Permet d'obtenir les modelObject du repository en fonction d'une recherche.
	 *
	 * @param mixed                  $search Requête
	 * @param null|ICrossModelAccess $access Acces cross-models pour les cross-models queries
	 * @return IModelObject[]
	 */
	protected final function get($search,?ICrossModelAccess $access = null) : array{
		return $this->_searcher->search(
			$search,
			$this->_all,
			$this->_indexed,
			$access
		);
	}

	/**
	 *  Retourne un IModelObject par son identifiant
	 *
	 * @param string $id Identifiant de l'objet recherché
	 *
	 * @return null|IModelObject
	 */
	protected final function getById(string $id) : ?IModelObject{
		return $this->_indexed["id"][$id]??null;
	}

	/**
	 *  Permet de rechercher des objets dans un repository et retourne les DTO correspondant
	 *
	 * @param mixed                  $search Requête de recherche
	 * @param ICrossModelAccess|null $access (optionnel) Acces cross-models
	 * @return IDTO[]
	 */
	public final function find($search,?ICrossModelAccess $access=null) : array {
		$res = [];
		foreach($this->get($search,$access) as $m){
			$res[]=$m->toDTO();
		}
		return $res;
	}

	/**
	 *  Teste l'existence d'un index
	 *
	 * @param string $key Indexe à tester
	 *
	 * @return bool
	 */
	public function existsIndex(string $key): bool { return isset($this->_indexes[$key]); }

	/**
	 *  Permet de corriger l'indexage d'un objet après modifications
	 * @param IModelObject $obj     Objet à ré-indexer
	 */
	private function reindex(IModelObject $obj){
		foreach($this->_indexes as $index=>$spec){
			$offset = $this->search($obj,$this->_indexed[$index]);
			if($spec->isSatisfiedBy($obj)){
				if($offset<0){
					$this->_indexed[$index][]=$obj;
				}
			}else{
				if($offset>=0){
					array_splice($this->_indexed[$index],$offset,1);
				}
			}
		}
	}

	/**
	 *  Ajoute une entité au repository
	 *
	 * @param IModelObject $entity Entité à ajouter
	 */
	private function add(IModelObject $entity) {
		$this->_all[] = $entity;
		$this->_length++;
		$this->_indexed["id"][(string)$entity->getId()] = $entity;
		foreach($this->_indexes as $name=>$spec){
			if($spec->isSatisfiedBy($entity)){
				if($this->search($entity,$this->_indexed[$name])<0){
					$this->_indexed[$name][]=$entity;
				}
			}
		}
	}

	/**
	 *  Supprime une entité du repository. Attention, un événement de suppression doit avoir été émis par l'aggrégat !
	 *
	 * @param string $id Entité à supprimer
	 */
	private function remove(string $id) {
		$aggregate=$this->_indexed["id"][$id]??null;
		if(is_null($aggregate)){
			throw new \InvalidArgumentException("Object not found in repository : $id");
		}else{
			array_splice($this->_all,$this->search($aggregate,$this->_all),1);
			$this->_length--;
			foreach($this->_indexed as $name=>&$index){
				$pos = $this->search($aggregate,$index);
				if($pos >= 0){
					array_splice($index,$this->search($aggregate,$index),1);
				}
			}
		}
	}

	/**
	 * @param IDomainEvent $e Evenement reçu
	 */
	public final function recieveEvent(IDomainEvent $e): void {
		$report = $this->recieve($e);
		foreach ($report->getCreated() as $obj){
			$this->add($obj);
		}
		foreach($report->getModified() as $obj){
			$this->reindex($obj);
		}
		foreach($report->getRemoved() as $obj){
			$this->remove($obj->getId());
		}
	}

	/**
	 *  Traite la reception d'un événement.
	 *
	 * @param \wfw\engine\core\domain\events\IDomainEvent $e Evenement recu
	 *
	 * @return EventReceptionReport
	 */
	protected abstract function recieve(IDomainEvent $e):EventReceptionReport;

	/**
	 * @return ISpecification[]
	 */
	public function getIndexes(): array { return $this->_indexes; }

	/**
	 * @return array
	 */
	public function getPopulatedIndexes(): array {
		$res = $this->_indexed;
		$res["not_indexed"] = $this->_all;
		return $res;
	}

	/**
	 * @return int Nombre d'éléments contenus dans le model.
	 */
	public function getLength():int{ return $this->_length; }

	/**
	 * @throws \InvalidArgumentException
	 */
	public function __wakeup() { $this->updateIndexList(); }

	/**
	 * Met à jour la liste des indexes à partir du résultat de la méthode indexes()
	 * @throws \InvalidArgumentException
	 */
	private function updateIndexList():void{
		$list = $this->indexes();
		$toUpdate =  [];
		foreach($list as $name=>$index){
			if(!$this->existsIndex($name)) $this->createIndex($name,$index);
			else{
				if(get_class($this->_indexes[$name])===get_class($index)){
					if(method_exists($index,"equals")
						&& !$index->equals($this->_indexes[$name])
					) $toUpdate[$name]=$index;
				}else $toUpdate[$name]=$index;
			}
		}
		foreach ($toUpdate as $name=>$index){
			$this->removeIndex($name);
			$this->createIndex($name,$index);
		}
	}

	/**
	 * @param IModelObject $obj
	 * @param array        $arr
	 * @return int
	 */
	private function search(IModelObject $obj,array $arr):int{
		$keys = array_keys($arr);
		foreach($arr as $k=>$v){
			if($obj->equals($v)) return array_search($k,$keys);
		}
		return -1;
	}

	/**
	 * Doit retourner un tableau name=>ISpecification qui définit les indexes à utiliser
	 * pour le modèle courant.
	 * La liste des indexes et synchronisée avec le modèle au moment de la construction puis à
	 * chaque déserialsiation de sorte que les indexes définis soient toujours en adéquation
	 * avec les indexes disponibles pour les recherches sur les modèles.
	 * Par défaut, le teste d'égalité entre un ancien index et un nouvel index se base sur la classe
	 * de la spécification. Si une methode equals():bool est définie sur la Specification, alors
	 * c'est cette méthode qui sera utilisée pour la comparaison. Cela permet de mettre à jour des
	 * indexes contenant certaines données.
	 *
	 * @return ISpecification[]
	 */
	protected abstract function indexes():array;
}