<?php
namespace wfw\daemons\modelSupervisor\client;

use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\data\specification\ISpecification;

/**
 * Interface d'un client du Supervisor.
 */
interface IMSClient {
	/**
	 * Obtient une session au client auprès du MSServer
	 */
	public function login():void;

	/**
	 * @return bool True si le client est connecté au MSServer.
	 */
	public function isLogged():bool;

	/**
	 * Demande la destruction de la session du client auprés du MSServer
	 */
	public function logout():void;

	/**
	 * Envoie une requete au MSServer pour obtenir les objets d'un model correspondant à la requête.
	 * La requête doit être lisible par le model.
	 * Nécessite les droits de lecture.
	 *
	 * @param string $class Classe du model concerné.
	 * @param string $query Requête sur le model
	 *
	 * @return array
	 */
	public function query(string $class,string $query):array;

	/**
	 * Envoie une requête forçant le serveur à sauvegarder les modifications sur tous les models.
	 * Nécessite les droits d'écriture.
	 * Action synchrone, bloque toutes les autres requêtes vers le writer jusqu'à la fin de la sauvegarde.
	 * Cette action peut-être lente en fonction du nombre et de la taille des models importés.
	 * Il est conseillé de ne l'utiliser que dans les parties les plus critiques de l'application, ou de régler
	 * une sauvegarde périodique plus courte.
	 */
	public function triggerSave():void;

	/**
	 * Applique une liste d'événements au jeu de models gérés par le MSServer.
	 * Necessite les droits d'écriture.
	 *
	 * @param \wfw\engine\core\domain\events\EventList $eventList Liste d'événements à appliquer
	 */
	public function applyEvents(EventList $eventList):void;

	/**
	 * Ajoute ou modifie un index sur un model.
	 * Necessite les droits d'administration
	 *
	 * @param string                 $class          Classe du model concerné
	 * @param string                 $name           Nom de l'index à créer ou modifier
	 * @param ISpecification $spec           Specification de l'index (mode de tri)
	 * @param bool                   $modifyIfExists (optionnel defaut : true) Si true : si l'index existe il est modifié.
	 */
	public function setIndex(string $class, string $name,ISpecification $spec,bool $modifyIfExists=true):void;

	/**
	 * Supprime un index d'un model
	 * Nécessite les droits d'administration.
	 *
	 * @param string $class Classe du model concerné
	 * @param string $name  Nom de l'index à supprimer
	 */
	public function removeIndex(string $class, string $name):void;

	/**
	 * Met à jour le snapshot des models.
	 * Nécessite les droits d'administration
	 */
	public function updateSnapshot():void;

	/**
	 * Reconstruit tous les models en réappliquant tous les événements.
	 * Nécessite les droits d'administration
	 *
	 * ATTENTION : Cette opération peut-être vraiment très lente en fonction du nombre de models,
	 * de leurs indexes, de la complexité de l'algorythme d'application des événements et du
	 * nombre d'événements à appliquer.
	 */
	public function rebuildAllModels():void;

	/**
	 * Reconstruit les models spécifiés en réappliquant tous les événements.
	 * Nécessite les droits d'administration
	 *
	 * ATTENTION : Cette opération peut-être vraiment très lente en fonction du nombre de models,
	 * de leurs indexes, de la complexité de l'algorythme d'application des événements et du
	 * nombre d'événements à appliquer.
	 *
	 * @param string[] $classes Liste des models à reconstruire.
	 */
	public function rebuildModels(string... $classes):void;
}