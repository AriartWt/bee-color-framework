<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/02/18
 * Time: 08:37
 */

namespace wfw\engine\core\data\DBAccess\NOSQLDB\msServer;

use wfw\daemons\modelSupervisor\client\MSClient;
use wfw\daemons\modelSupervisor\server\errors\MustBeLogged;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\data\specification\ISpecification;

/**
 * Acces au MSServer avec connexion et reconnexion automatique.
 */
final class MSServerWriterAccess extends MSClient implements IMSServerAccess
{
    /**
     * Applique une liste d'événements au jeu de models gérés par le MSServer.
     *
     * @param EventList $eventList Liste d'événements à appliquer
     */
    public function applyEvents(EventList $eventList): void
    {
        try{
            parent::applyEvents($eventList);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::applyEvents($eventList);
        }
    }

    /**
     * Envoie une requete au MSServer pour obtenir les objets d'un model correspondant à la requête.
     * La requête doit être lisible par le model.
     * Nécessite les droits de lecture.
     *
     * @param string $class Classe du model concerné
     * @param string $query Requête sur le model
     *
     * @return array
     * @throws MSClientException
     * @throws \Exception
     */
    public function query(string $class, string $query): array
    {
        try{
            return parent::query($class, $query);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            return parent::query($class, $query);
        }
    }

    /**
     * Envoie une requête forçant le serveur à sauvegarder les modifications sur tous les models.
     * Nécessite les droits d'écriture.
     * Action synchrone, bloque toutes les autres requêtes vers le writer jusqu'à la fin de la sauvegarde.
     * Cette action peut-être lente en fonction du nombre et de la taille des models importés.
     * Il est conseillé de ne l'utiliser que dans les parties les plus critiques de l'application, ou de régler
     * une sauvegarde périodique plus courte.
     */
    public function triggerSave(): void
    {
        try{
            parent::triggerSave();
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::triggerSave();
        }
    }

    /**
     * Ajoute ou modifie un index sur un model.
     *
     * @param string                 $class          Classe du model concerné
     * @param string                 $name           Nom de l'index à créer ou modifier
     * @param ISpecification $spec           Specification de l'index (mode de tri)
     * @param bool                   $modifyIfExists (optionnel defaut : true) Si true : si l'index existe il est modifié.
     */
    public function setIndex(string $class, string $name, ISpecification $spec, bool $modifyIfExists = true): void
    {
        try{
            parent::setIndex($class, $name, $spec, $modifyIfExists);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::setIndex($class, $name, $spec, $modifyIfExists);
        }
    }

    /**
     * Supprime un index d'un model
     *
     * @param string $class Classe du model concerné
     * @param string $name  Nom de l'index à supprimer
     */
    public function removeIndex(string $class, string $name): void
    {
        try{
            parent::removeIndex($class, $name);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::removeIndex($class, $name);
        }
    }

    /**
     * Met à jour le snapshot des models.
     * Nécessite les droits d'administration
     */
    public function updateSnapshot(): void
    {
        try{
            parent::updateSnapshot();
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::updateSnapshot();
        }
    }

    /**
     * Reconstruit tous les models en réappliquant tous les événements.
     * Nécessite les droits d'administration
     *
     * ATTENTION : Cette opération peut-être vraiment très lente en fonction du nombre de models,
     * de leurs indexes, de la complexité de l'algorythme d'application des événements et du
     * nombre d'événements à appliquer.
     */
    public function rebuildAllModels(): void
    {
        try{
            parent::rebuildAllModels();
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::rebuildAllModels();
        }
    }

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
    public function rebuildModels(string... $classes): void
    {
        try{
            parent::rebuildModels(...$classes);
        }catch(MustBeLogged $e){
            $this->resetLogin();
            $this->login();
            parent::rebuildModels(...$classes);
        }
    }
}