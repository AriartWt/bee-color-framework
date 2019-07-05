<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppEventDispatcher;
use wfw\daemons\rts\server\app\events\IRTSAppEventObserver;

/**
 * Basic RTS apps manager
 */
final class RTSAppsManager implements IRTSAppsManager {
	/** @var IRTSApp[][] $_apps */
	private $_apps;
	/** @var IRTSAppEventDispatcher $_observer */
	private $_observer;

	/**
	 * RTSAppsManager constructor.
	 *
	 * @param IRTSAppEventObserver $observer
	 * @param array                $appsToCreate
	 * @param IRTSApp[]            $apps
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		IRTSAppEventObserver $observer,
		array $appsToCreate = [],
		IRTSApp ...$apps
	){
		$this->_apps = [];
		$createdApps = [];
		$this->_observer = $observer;
		foreach($appsToCreate as $appClass => $params){
			if(!is_a($appClass, IRTSApp::class, true))
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
	 * @return IRTSAppEvent[]
	 */
	public function dispatchData(?string $appKey, string $data): array {
		$events = [];
		if(is_null($appKey)) foreach($this->_apps as $k=>$apps)
			foreach($apps as $app) $events[] = $app->receiveData($data);
		else if(isset($this->_apps[$appKey])) foreach($this->_apps[$appKey] as $app)
			$events[] = $app->receiveData($data);
		return array_merge(...$events);
	}

	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSAppEvent ...$events Event to dispacth
	 */
	public function dispatch(IRTSAppEvent ...$events): void {
		$this->_observer->dispatch(...$events);
	}

	/**
	 * @return IRTSApp[]
	 */
	public function getAll(): array {
		return array_merge(...array_values($this->_apps));
	}

	/**
	 * @return string[] all apps name that are currently managed
	 */
	public function getAppNames(): array {
		return array_keys($this->_apps);
	}
}