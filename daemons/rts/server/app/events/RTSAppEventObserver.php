<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Basic RTSEvent observer
 */
final class RTSAppEventObserver implements IRTSAppEventObserver {
	/** @var IRTSAppEventListener[][] $_listeners */
	private $_listeners = [];

	/**
	 * @param null|string $appKey If given, return only listeners registered for an appKey
	 * @return IRTSAppEventListener[] Return all listeners
	 */
	public function getListeners(?string $appKey): array {
		if(is_null($appKey)) return array_unique(
			array_merge(...array_values($this->_listeners)), SORT_REGULAR
		);
		else if(isset($this->_listeners[$appKey])) return $this->_listeners[$appKey];
		else return [];
	}

	/**
	 * @param string|null          $appKey       If null, will add listeners to all keys
	 * @param IRTSAppEventListener ...$listeners Listeners that listen to IRTSEvents
	 */
	public function addListeners(?string $appKey, IRTSAppEventListener ...$listeners): void {
		if(is_null($appKey)){
			foreach($this->_listeners as $k=>$v) $this->_listeners[$k] = array_merge(
				$this->_listeners[$k],$listeners
			);
		}else{
			if(!isset($this->_listeners[$appKey])) $this->_listeners[$appKey] = [];
			$this->_listeners[$appKey] = array_merge($this->_listeners[$appKey],$listeners);
		}
	}

	/**
	 * @param null|string          $appKey       If null, will remove listeners from all keys
	 * @param IRTSAppEventListener ...$listeners Listeners to remove
	 */
	public function removeListeners(?string $appKey, IRTSAppEventListener ...$listeners): void {
		if(is_null($appKey)){
			foreach($this->_listeners as $k=>$listeners){
				$this->_listeners[$k] = array_diff($this->_listeners[$k],$listeners);
			}
		}else if(isset($this->_listeners[$appKey])){
			$this->_listeners[$appKey] = array_diff($this->_listeners[$appKey],$listeners);
		}
	}

	/**
	 * Remove all listeners
	 *
	 * @param null|string $appKey If given, remove all listeners for a key
	 */
	public function removeAllListeners(?string $appKey): void {
		if(is_null($appKey)) $this->_listeners = [];
		else if(isset($this->_listeners[$appKey])) $this->_listeners[$appKey] = [];
	}

	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSAppEvent ...$events Event to dispacth
	 */
	public function dispatch(IRTSAppEvent ...$events): void {
		$eventsByApps = array_fill_keys(
			array_diff(array_keys($this->_listeners),['*']),[]
		);
		foreach($events as $e){
			$apps = $e->getApps();
			if(is_null($apps)) foreach($this->_listeners as $k => $listeners)
				$eventsByApps[$k][] = $e;
			else foreach($apps as $appKey) if(isset($eventsByApps[$appKey]))
				$eventsByApps[$appKey][] = $e;
		}
		foreach($eventsByApps as $app => $es) foreach($this->_listeners[$app] as $l)
			$l->applyRTSEvents(...$es);
		foreach($this->_listeners['*'] ?? [] as $l) $l->applyRTSEvents($events);
	}
}