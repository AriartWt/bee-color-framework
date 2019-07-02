<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEvent;
use wfw\daemons\rts\server\app\events\IRTSEventDispatcher;
use wfw\daemons\rts\server\app\events\IRTSEventObserver;

/**
 * Basic RTS apps manager
 */
final class RTSAppsManager implements IRTSAppsManager {
	/** @var IRTSApp[][] $_apps */
	private $_apps;
	/** @var IRTSEventDispatcher $_observer */
	private $_observer;

	/**
	 * RTSAppsManager constructor.
	 *
	 * @param IRTSEventObserver $observer
	 * @param array             $appsToCreate
	 * @param IRTSApp[]         $apps
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		IRTSEventObserver $observer,
		array $appsToCreate = [],
		IRTSApp ...$apps
	){
		$this->_apps = [];
		$createdApps = [];
		$this->_observer = $observer;
		foreach($appsToCreate as $appClass => $params){
			if(!is_a($appClass,IRTSApp::class,true))
				throw new \InvalidArgumentException("$appClass must implements ".IRTSApp::class);
			$createdApps[] = new $appClass(...(is_array($params) ? $params : []));
		}
		if(!empty($apps)) $apps = [];
		$this->addApps(...array_merge($createdApps,$apps));
	}

	/**
	 * @param IRTSApp ...$apps Apps to add to the manager
	 */
	public function addApps(IRTSApp ...$apps): void {
		foreach($apps as $app){
			if(!isset($this->_apps[$app->getKey()])) $this->_apps[$app->getKey()] = [];
			$this->_apps[$app->getKey()][$app->getId()] = $app;
			$this->_observer->addListeners($app->getKey(),$app);
		}
	}

	/**
	 * @param string $appKey Dispatch data for all apps that listen for appKey
	 * @param string $data   Data to dispatch
	 */
	public function dispatchData(?string $appKey, string $data): void {
		$events = [];
		if(is_null($appKey)) foreach($this->_apps as $k=>$apps)
			foreach($apps as $app) $events[] = $app->receiveData($data);
		else if(isset($this->_apps[$appKey]))
			foreach($this->_apps[$appKey] as $app) $events[] = $app->receiveData($data);
		$this->dispatch(null,array_merge(...$events));
	}

	/**
	 * Send app events to all event listeners
	 *
	 * @param string|null $appKey    If null, will send to all listeners
	 * @param IRTSEvent   ...$events Event to dispacth
	 */
	public function dispatch(?string $appKey, IRTSEvent ...$events): void {
		$this->_observer->dispatch($appKey,...$events);
	}
}