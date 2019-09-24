<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\daemons\modelSupervisor\server\components\requests\IClientDeniedRequest;
use wfw\engine\core\data\model\IModel;

/**
 * La sauvegarde a bien été effectuée.
 */
final class SaveDoneRequest implements IWriterAdminRequest,IClientDeniedRequest {
	/** @var string $_id */
	private $_id;
	/** @var array $_savedModels */
	private $_savedModels;

	/**
	 * SaveDoneRequest constructor.
	 *
	 * @param string $id          Identifiant de la sauvegarde.
	 * @param array  $savedModels Model sauvegardés sous la forme "class"=>microtime(true) date de derniere modification sauvegardée.
	 */
	public function __construct(string $id,array $savedModels) {
		$this->_id = $id;
		$now = microtime(true);
		foreach($savedModels as $class=>$date){
			if(!is_a($class,IModel::class,true) || $date>$now){
				throw new \InvalidArgumentException("$class doesn't implements ".IModel::class." or $date is greater than the current date !");
			}
		}
		$this->_savedModels = $savedModels;
	}

	/**
	 * @return null|string Identifiant de session
	 */
	public function getSessionId(): ?string {
		return null;
	}

	/**
	 * @return string
	 */
	public function getSaveId():string{
		return $this->_id;
	}

	/**
	 * @return array
	 */
	public function getSavedModels():array{
		return $this->_savedModels;
	}

	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return null;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return null;
	}
}