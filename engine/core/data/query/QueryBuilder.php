<?php
namespace wfw\engine\core\data\query;

/**
 *  Permet de créer des requêtes SQL
 */
class QueryBuilder {
	/**
	 *  Requête select.
	 *
	 * @param string[] ...$clause (colonnes...)
	 *
	 * @return ISelectQuery
	 */
	public function select(string ...$clause):ISelectQuery{
		return new SelectQuery(...$clause);
	}

	/**
	 *  Requête update
	 * @return IUpdateQuery
	 */
	public function update():IUpdateQuery{
		return new UpdateQuery();
	}

	/**
	 *  Requête insert
	 * @return IInsertQuery
	 */
	public function insert():IInsertQuery{
		return new InsertQuery();
	}

	/**
	 *  Requête delete
	 * @return IDeleteQuery
	 */
	public function delete():IDeleteQuery{
		return new DeleteQuery();
	}

	/**
	 *  Requête brute.
	 *
	 * @param string $query Requête.
	 *
	 * @return RawQuery
	 */
	public function raw(string $query):RawQuery{
		return new RawQuery($query);
	}
}