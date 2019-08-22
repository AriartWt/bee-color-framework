<?php
namespace wfw\engine\core\query\inflectors;

use wfw\engine\core\query\errors\NoQueryHandlerFound;
use wfw\engine\core\query\IQuery;
use wfw\engine\core\query\IQueryHandler;
use wfw\engine\core\query\IQueryHandlerFactory;
use wfw\engine\core\query\IQueryInflector;

/**
 * Tente de résoudre les QueryHandlers en se basant sur le namespace de la querye.
 * Exemple : la query \wfw\engine\packages\users\queries\CreateUser
 *           sera résolue : \wfw\engine\packages\users\queries\handlers\CreateUserHandler
 */
final class NamespaceBasedInflector implements IQueryInflector {
	/** @var IQueryHandlerFactory $_factory */
	private $_factory;
	/** @var IQueryHandler[][] $_handlers */
	private $_handlers;
	/** @var IQueryHandler $_resolved */
	private $_resolved;

	/**
	 * NamespaceBasedInflector constructor.
	 *
	 * @param IQueryHandlerFactory $factory Factory de handlers
	 * @param array                  $handlers
	 */
	public function __construct(IQueryHandlerFactory $factory, array $handlers = []) {
		$this->_factory = $factory;
		$this->_handlers = [];
		$this->_resolved = [];
		foreach ($handlers as $queryClass => $handlerClasses){
			if(!is_a($queryClass,IQuery::class,true))
				throw new \InvalidArgumentException(
					"$queryClass doesn't implements ".IQuery::class
				);
			if(!isset($this->_handlers[$queryClass])) $this->_handlers[$queryClass] = [];
			foreach($handlerClasses as $class=>$params){
				if(!is_a($class,IQueryHandler::class,true))
					throw new \InvalidArgumentException(
						"$class doesn't implements ".IQueryHandler::class
					);
				$this->_handlers[$queryClass][$class] = $factory->buildQueryHandler(
					$class,$params
				);
			}
		}
	}

	/**
	 *  Trouve un handler pour une querye
	 *
	 * @param IQuery $query Comande dont on cherche le handler
	 * @return IQueryHandler[]
	 * @throws NoQueryHandlerFound
	 */
	public function resolveQueryHandlers(IQuery $query): array {
		$handlers = $this->resolveHandlersFromQueryClass(get_class($query));
		if(count($handlers)>0){
			return $handlers;
		}else{
			throw new NoQueryHandlerFound(
				"No query handler found for query ".get_class($query)
			);
		}
	}

	/**
	 * @param string $query Classe de la querye à résoudre.
	 * @return array
	 */
	private function resolveHandlersFromQueryClass(string $query): array {
		$res = [];
		if(isset($this->_resolved[$query])){
			$res[] = $this->_handlers[$query];
		}else{
			$r = [];
			if ($pos = strrpos($query, $search = "\\queries\\") !== false) {
				$handlerClass = substr_replace(
					$query,
					"\\queries\\handlers\\",
					$pos,
					strlen($search)
				);
			}
			try{
				$r[] = $this->_factory->buildQueryHandler(
					($handlerClass ?? $query)."Handler"
				);
			}catch(\Exception $e){}

			$this->_handlers[$query] = array_merge(
				$this->_handlers[$query] ?? [],
				$r
			);
			$res[] = $r;
		}
		foreach($this->_handlers as $class=>$handlers){
			if(is_a($query,$class)) $res[] = $handlers;
		}
		return  array_merge(...$res);
	}
}