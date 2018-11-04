<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requestHandler;

use wfw\daemons\modelSupervisor\server\components\writer\WriterWorker;
use wfw\daemons\modelSupervisor\server\IMSServerQuery;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandler;

/**
 * @brief Reçoit les requêtes du MSServer à envoyer au composant WriterComponentWorker
 */
final class WriterRequestHandler implements IMSServerRequestHandler {
	/** @var WriterWorker $_worker */
	private $_worker;

	/**
	 * WriterComponentRequestHandler constructor.
	 *
	 * @param WriterWorker $worker Worker
	 */
	public function __construct(WriterWorker $worker) {
		$this->_worker = $worker;
	}

	/**
	 *  Reçoit et traite la requête
	 *
	 * @param IMSServerQuery $request Requête
	 */
	public function handleModelManagerQuery(IMSServerQuery $request) {
		$this->_worker->sendQuery($request->getInternalRequest());
	}
}