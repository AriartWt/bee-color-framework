<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\engine\core\data\specification\ISpecification;

/**
 *  Requête de création d'un indexe
 */
final class SetIndex extends IndexRequest {
	/** @var ISpecification $_spec */
	private $_spec;
	/** @var bool $_modifyIfExists */
	private $_modifyIfExists;

	/**
	 * CreateIndexRequest constructor.
	 *
	 * @param string                 $sessId         Identifiant de session
	 * @param string                 $modelName      Nom du model dans lequel créer ou modifier l'index
	 * @param string                 $indexName      Nom de l'index
	 * @param ISpecification $spec           Specifications associés à l'index
	 * @param bool                   $modifyIfExists Si true : l'index est modifié s'il existe, sinon une erreur est renvoyée.
	 */
	public function __construct(
		string $sessId,
		string $modelName,
		string $indexName,
		ISpecification $spec,
		bool $modifyIfExists=true
	) {
		parent::__construct($sessId,$modelName,$indexName);
		$this->_modifyIfExists = $modifyIfExists;
		$this->_spec = $spec;
	}

	/**
	 * @return ISpecification
	 */
	public function getSpec(): ISpecification
	{
		return $this->_spec;
	}

	/**
	 * @return bool
	 */
	public function isModifyIfExists(): bool
	{
		return $this->_modifyIfExists;
	}
}