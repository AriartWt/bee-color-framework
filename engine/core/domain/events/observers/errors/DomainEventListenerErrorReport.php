<?php

namespace wfw\engine\core\domain\events\observers\errors;

use wfw\engine\core\domain\events\IDomainEventListener;

/**
 * Class DomainEventListenerError
 *
 * @package wfw\engine\core\domain\events\observers\errors
 */
final class DomainEventListenerErrorReport {
	/** @var \Throwable $_error */
	private $_error;
	/** @var IDomainEventListener $_listener */
	private $_listener;

	/**
	 * DomainEventListenerErrorReport constructor.
	 *
	 * @param IDomainEventListener $listener Listener that thrown the error
	 * @param \Throwable           $error    Error thrown
	 */
	public function __construct(IDomainEventListener $listener, \Throwable $error) {
		$this->_error = $error;
		$this->_listener = $listener;
	}

	/**
	 * @return \Throwable
	 */
	public function getError(): \Throwable {
		return $this->_error;
	}

	/**
	 * @return IDomainEventListener
	 */
	public function getListener(): IDomainEventListener {
		return $this->_listener;
	}
}