<?php
namespace wfw\engine\core\domain\events\store;

use Exception;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\core\data\DBAccess\SQLDB\IDBAccess;
use wfw\engine\core\domain\aggregate\IAggregateRoot;
use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventDispatcher;
use wfw\engine\core\domain\events\store\errors\CorruptedData;
use wfw\engine\core\domain\events\store\errors\Inconsistency;
use wfw\engine\core\data\query\QueryBuilder;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  EventStore basé sur une base de données
 */
final class DBBasedEventStore implements IEventStore {
	/** @var IDBAccess $_db */
	private $_db;
	/** @var IMSServerAccess $_msAccess */
	private $_msAccess;
	/** @var IDomainEventDispatcher $_dispatcher */
	private $_dispatcher;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var bool $_eventDispatchInTransaction */
	private $_eventDispatchInTransaction;
	/** @var array $_cmdCache */
	private $_cmdCache;

	/**
	 *  DBBasedEventStore constructor.
	 *
	 * @param IDBAccess              $access          Accés à la base de données
	 * @param IMSServerAccess        $msAccess        Accés au MSServerWriter
	 * @param IDomainEventDispatcher $dispatcher      Dispatcher permettant d'envoyer les événements une fois leur persistence assurée
	 * @param ISerializer            $serializer      Objet utilisé pour la serialisation/deserialsiation.
	 * @param bool                   $transctDispatch (optionnel defaut : false) Si true : la mise à jour des models se fait dans la transaction.
	 *                                                Si l'appel au MSServer échoue, un rollback est effectué.
	 */
	public function __construct(
		IDBAccess $access,
		IMSServerAccess $msAccess,
		IDomainEventDispatcher $dispatcher,
		ISerializer $serializer,
		bool $transctDispatch = false
	){
		$this->_db = $access;
		$this->_cmdCache = [];
		$this->_msAccess = $msAccess;
		$this->_dispatcher = $dispatcher;
		$this->_serializer = $serializer;
		$this->_eventDispatchInTransaction = $transctDispatch;
	}

	/**
	 *  Retourne un aggrégat grace à son UUID
	 *
	 * @param UUID $aggregateId Identifiant de l'aggrégat
	 *
	 * @return IAggregateRoot|null
	 */
	public function getAggregateRoot(UUID $aggregateId):?IAggregateRoot{
		$events = $this->_db->execute((new QueryBuilder())->raw("
			SELECT e.id as id , a.type as aggregate_type , e.data as data , e.type as event_type , s.data as snapshot
			FROM events as e
			LEFT JOIN aggregates as a ON e.aggregates_id = a.id
			LEFT JOIN snapshots as s ON e.aggregates_id = s.aggregates_id
			WHERE a.id = unhex(?)
			  AND e.version BETWEEN coalesce(s.version,0) AND a.version
			ORDER BY e.version ASC, e.generation_date ASC, e.id ASC
		")->addParam($aggregateId->toHexString()))->fetchAll();
		//debug($events);
		if(count($events)>0){
			/** @var IAggregateRoot $res */
			$res = $events[0]["snapshot"];
			$skipFirst = false;
			if(!is_null($res)){
				//Sinon, on déserialise le snapshot
				$res = $this->_serializer->unserialize($res);
			}
			if(is_null($res) || $res instanceof \__PHP_Incomplete_Class){
				//s'il n'existe pas de snapshot, alors on prend le premier événement qui, par convention,
				// contient la liste des arguments du constructeur de l'aggrégat au moment de sa première
				//génération
				/** @var IAggregateRoot $aggregateClass */
				$aggregateClass = $events[0]["aggregate_type"];
				$event = $this->_serializer->unserialize($events[0]["data"]);
				if($event instanceof IAggregateRootGeneratedEvent){
					$res = $aggregateClass::restoreAggregateFromEvent(
						$this->_serializer->unserialize($events[0]["data"])
					);
					$skipFirst = true;
				}else throw new CorruptedData(
					"First event must be an instanceof ".IAggregateRootGeneratedEvent::class
					.". ".get_class($event)." given."
				);
			}
			foreach($events as $k=>$event){
				//On ne réapplique pas l'événement de création.
				if($skipFirst && $k ===0){ continue; }
				//On applique un à un tous les événements retournés
				//(depuis la création de l'aggrégat ou du dernier snapshot s'il existe)
				$event = $this->_serializer->unserialize($event["data"]);
				if($event instanceof \__PHP_Incomplete_Class) throw new CorruptedData(
					"An event can't be loaded due to a class resolution failure (__PHP_Icomplete_Class),"
					." making the aggregate $aggregateId (".get_class($res).") unusuable.\n"
					." Please import the needed class or fixe this event.\n"
					." Event data : ".json_encode($event)
				);
				$res->apply($event);
			}
			return $res;
		}else{
			return null;
		}
	}
	
	/**
	 * Retourne tous les aggrégats correspondant aux identifiants.
	 * TODO : OTPIMISATION : une seule requete pour tout récupérer.
	 * @param UUID ...$aggregatesId Liste des identifiants d'aggrégats
	 * @return IAggregateRoot[]
	 */
	public function getAllAggregateRoot(UUID... $aggregatesId): array {
		$res = [];
		foreach($aggregatesId as $id){
			$r = $this->getAggregateRoot($id);
			if(!is_null($r)) $res[] = $r;
		}
		return $res;
	}

	/**
	 *  Enregistre une séquence d'événements pour un aggrégat
	 *
	 * @param IAggregateRoot $aggregate Aggregat concerné par les événements
	 * @param ICommand       $command   (optionnel) Commande à l'origine de la mise à jour de l'aggrégat
	 *
	 * @throws Exception
	 */
	public function saveAggregateRoot(IAggregateRoot $aggregate, ?ICommand $command = null) {
		if($aggregate->getEventList()->getLength()>0){
			$this->_db->beginTransaction();
			$builder = new QueryBuilder();
			try{
				if($command){
					if(!$this->existsCommand($command->getId())){
						$this->_db->execute($builder->raw("
							INSERT INTO commands (id, type, generation_date, writing_date, data)
							VALUES (unhex(?),?,FROM_UNIXTIME(?),CURRENT_TIMESTAMP(6),?)
						")->addParams([
							$command->getId()->toHexString(),
							get_class($command),
							$command->getGenerationDate(),
							$this->_serializer->serialize($command)
						]));
					}
				}

				$agg = $this->_db->execute($builder->raw("
					SELECT *
					FROM aggregates
					WHERE id=unhex(?)
				")->addParam($aggregate->getId()->toHexString()))->fetchAll();

				$inserted = false;
				if(count($agg) === 0){
					$this->_db->execute($builder->raw("
						INSERT INTO aggregates (id, type, version)
						VALUES (unhex(?),?,?)
					")->addParams([
						$aggregate->getId()->toHexString(),
						get_class($aggregate),
						$aggregate->getEventList()->getLength()-1
					]));

					$inserted = true;
					$version = -1;
				}else{
					$version = $agg[0]["version"];
				}

				if($version > $aggregate->getVersionBeforeEvents()) throw new Inconsistency(
					"The current aggregate have already been updated by a concurrent access."
					." The current version is $version and was loaded with version "
					.$aggregate->getVersionBeforeEvents()."."
				);

				$insertQuery = $builder->insert()->into(
					"events (id,type,data,version,generation_date,writing_date,aggregates_id,commands_id)"
				);

				foreach($aggregate->getEventList() as $k=>$v){
					/** @var IDomainEvent $v */
					$insertQuery->values("unhex(?)","?","?","?","FROM_UNIXTIME(?)","CURRENT_TIMESTAMP(6)","unhex(?)","unhex(?)")
						->addParams([
							$v->getUUID()->toHexString(),
							get_class($v),
							$this->_serializer->serialize($v),
							$k + $version + 1,
							$v->getGenerationDate(),
							$aggregate->getId()->toHexString(),
							(is_null($command)) ? NULL : $command->getId()->toHexString()
						]);
				}

				$this->_db->execute($insertQuery);

				if(!$inserted){
					$this->_db->execute($builder->raw("
						UPDATE aggregates
						SET version = ?
						WHERE id = unhex(?)
					")->addParams([
						$version + $aggregate->getEventList()->getLength(),
						$aggregate->getId()->toHexString()
					]));
				}

				if($this->_eventDispatchInTransaction){
					$this->_msAccess->applyEvents($aggregate->getEventList());
				}
				$this->_db->commit();
			}catch(Exception $e){
				$this->_db->rollBack();
				echo "$e\n";
				throw $e;
			}
			if(!$this->_eventDispatchInTransaction){
				$this->_msAccess->applyEvents($aggregate->getEventList());
			}
			$this->_dispatcher->dispatchAllDomainEvents($aggregate->getEventList());
			$aggregate->resetEventList();
		}
	}

	/**
	 * Verifie l'existence d'une commande en base de données. Si la commande existe, le résultat est
	 * mis en cache.
	 * @param UUID $uuid Identifiant de la commande
	 * @return bool
	 */
	private function existsCommand(UUID $uuid):bool{
		if(isset($this->_cmdCache[(string)$uuid])) return true;
		else{
			$builder = new QueryBuilder();
			$res = count($this->_db->execute($builder->raw("
				SELECT id FROM commands WHERE id=unhex(?)
			")->addParam($uuid->toHexString()))->fetchAll()) !== 0;

			if(!$res) return false;
			else{
				$this->_cmdCache[(string)$uuid] = true;
				return true;
			}
		}
	}
	
	/**
	 * Enregistre les séquences d'événements de tous les AggregateRoot
	 * @param null|ICommand  $command       Commande à l'origine de la mise à jour des aggrégats
	 * @param IAggregateRoot ...$aggregates Liste des aggrégats
	 */
	public function saveAllAggregateRoots(?ICommand $command = null, IAggregateRoot... $aggregates) {
		foreach($aggregates as $a){
			$this->saveAggregateRoot($a, $command);
		}
	}
}