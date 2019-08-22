<?php
namespace wfw\engine\core\query\result;

/**
 *  Gestionnaire d'événements métiers
 */
interface IQueryResultObserver extends IQueryResultDispatcher {
	/**
	 * @param string $queryResultClass Evenement
	 * @return IQueryResultListener[] Retourne la liste listeners qui seraient déclenchés par un événement donné.
	 */
	public function getQueryResultListeners(string $queryResultClass):array;

	/**
	 *  Ajoute un listener pour un événement métier
	 *
	 * @param string $queryResultClass Classe de l'événement à écouter. Tiens compte de l'héritage
	 * @param IQueryResultListener $listener         Listener à appeler
	 */
	public function addQueryResultListener(string $queryResultClass, IQueryResultListener $listener);

	/**
	 *  Supprime un ou plusieurs lsitener attachés à un événement
	 * @param string                            $queryResultClass Classe d'événement dont on souhaite supprimer les listener
	 * @param null|IQueryResultListener $listener         (optionnel) Listener à supprimer. Si null, supprime tous les lsitener de la classe d'événement
	 */
	public function removeQueryResultListener(string $queryResultClass, ?IQueryResultListener $listener=null);
}