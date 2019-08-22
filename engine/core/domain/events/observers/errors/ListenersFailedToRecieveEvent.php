<?php

namespace wfw\engine\core\domain\events\observers\errors;

use wfw\engine\core\domain\events\IDomainEventListener;

/**
 * thrown when some domain event listeners failed to recieve an event
 */
class ListenersFailedToRecieveEvent extends DomainEventObserverFailure {
	/** @var DomainEventListenerErrorReport[] $_reports */
	private $_reports;

	/**
	 * ListenersFailedToRecieveEvent constructor.
	 *
	 * @param string                           $message
	 * @param DomainEventListenerErrorReport[] $reports
	 */
	public function __construct(string $message = "", DomainEventListenerErrorReport... $reports) {
		parent::__construct($message);
		$this->_reports = $reports;
	}

	/**
	 * @return DomainEventListenerErrorReport[]
	 */
	public function getReports():array{
		return $this->_reports;
	}
}