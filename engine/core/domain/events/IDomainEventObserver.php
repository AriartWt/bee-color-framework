<?php
namespace wfw\engine\core\domain\events;

/**
 *  Gestionnaire d'événements métiers
 */
interface IDomainEventObserver extends IDomainEventDispatcher {
	/**
	 * @param string $domainEventClass Evenement
	 * @return IDomainEventListener[] Retourne la liste listeners qui seraient déclenchés par un événement donné.
	 */
	public function getEventListeners(string $domainEventClass):array;

	/**
	 *  Ajoute un listener pour un événement métier
	 *
	 * @param string                       $domainEventClass Classe de l'événement à écouter. Tiens compte de l'héritage
	 * @param IDomainEventListener $listener         Listener à appeler
	 */
	public function addEventListener(string $domainEventClass, IDomainEventListener $listener);

	/**
	 *  Supprime un ou plusieurs lsitener attachés à un événement
	 * @param string                            $domainEventClass Classe d'événement dont on souhaite supprimer les listener
	 * @param null|IDomainEventListener $listener         (optionnel) Listener à supprimer. Si null, supprime tous les lsitener de la classe d'événement
	 */
	public function removeEventListener(string $domainEventClass, ?IDomainEventListener $listener=null);
}