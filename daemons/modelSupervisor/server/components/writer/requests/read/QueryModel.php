<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\read;

use wfw\engine\core\data\model\IModel;

/**
 * Demande de lecture d'un model.
 */
final class QueryModel implements IWriterReadRequest {
	/** @var null|string $_sessionId */
	private $_sessionId;
	/** @var string $_modelName */
	private $_modelName;
	/** @var string $_query */
	private $_query;

	/**
	 * QueryModel constructor.
	 *
	 * @param string $sessId
	 * @param string $modelName
	 * @param string $query
	 */
	public function __construct(?string $sessId,string $modelName, string $query) {
		if(!is_a($modelName,IModel::class,true)){
			throw new \InvalidArgumentException(
				"$modelName doesn't implements ".IModel::class." or is not a valid class !"
			);
		}
		$this->_sessionId = $sessId;
		$this->_modelName = $modelName;
		$this->_query = $query;
	}

	/**
	 * @return null|string Identifiant de session
	 */
	public function getSessionId(): ?string {
		return $this->_sessionId;
	}

	/**
	 * @return string Model concerné par la lecture
	 */
	public function getModelName():string{
		return $this->_modelName;
	}

	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return $this->_query;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return $this;
	}

	/**
	 * @return array
	 */
	public function __sleep() {
		return [
			"_sessionId",
			"_modelName"
		];
	}
}