<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\modelManager;

use wfw\daemons\modelSupervisor\server\components\writer\errors\ModelIndexAlreadyExists;
use wfw\daemons\modelSupervisor\server\components\writer\errors\ModelNotFound;
use wfw\engine\core\data\model\IModel;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\data\specification\ISpecification;

/**
 * @brief Permet de gérer un ensemble de models
 */
interface IModelManager {
	/**
	 * Effectue une recherche sur un model
	 *
	 * @param string $model Nom du model.
	 * @param mixed  $query Requête de recherche
	 *
	 * @return array Résultats
	 */
	public function query(string $model,$query):array;

	/**
	 * Dispatche les événements contenus dans $eventList
	 *
	 * @param \wfw\engine\core\domain\events\EventList $eventList Liste des événements à appliquer
	 */
	public function dispatch(EventList $eventList):void;

	/**
	 * Teste l'existence d'un model dans le manager
	 *
	 * @param string $name Classe du model à tester
	 * @return bool True si le model existe
	 */
	public function existsModel(string $name):bool;

	/**
	 * Teste l'existence d'un indexe dans un model
	 *
	 * @param string $model
	 * @param string $index
	 *
	 * @return bool True si l'indexe existe
	 * @throws ModelNotFound Si le model n'existe pas.
	 */
	public function existsIndex(string $model,string $index):bool;

	/**
	 * Supprime un index dans un model
	 *
	 * @param string $model Model concerné
	 * @param string $index Index à supprimer
	 *
	 * @return bool True si l'index a été supprimé
	 * @throws ModelNotFound Si le model n'existe pas.
	 */
	public function removeIndex(string $model,string $index):bool;

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
	public function setIndex(string $model,string $index,ISpecification $spec,bool $modifyIfExists = false):bool;

	/**
	 * Déclenche la sauvegarde des models en attente.
	 * @return array Liste des models mis à jour sous la forme "class" => "derniere modification"
	 */
	public function save():array;

	/**
	 * @return bool True si une sauvegarde est nécessaire, false sinon.
	 */
	public function needASave():bool;

	/**
	 * Remet à zero la liste des models en attente de sauvegarde si ceux-ci n'ont pas été modifiés entre temps.
	 * @param array $models Sous la forme "class"=>"date"
	 */
	public function reset(array $models):void;

	/**
	 * Permet de recharger les models en les récupérant depuis leur espace de sotckage.
	 */
	public function reloadModels():void;
}