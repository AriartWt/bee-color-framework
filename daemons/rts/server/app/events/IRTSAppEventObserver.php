<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * RTS event observer. Note that the special appKey * mean 'all'.
 * An app registered as listener on * will recieve all events.
 */
interface IRTSAppEventObserver extends IRTSAppEventDispatcher {
	/**
	 * @param null|string $appKey If given, return only listeners registered for an appKey
	 * @return IRTSAppEventListener[] Return all listeners
	 */
	public function getListeners(?string $appKey):array;

	/**
	 * @param string|null          $appKey       If null, will add listeners to all keys
	 * @param IRTSAppEventListener ...$listeners Listeners that listen to IRTSEvents
	 */
	public function addListeners(?string $appKey, IRTSAppEventListener ...$listeners):void;

	/**
	 * @param null|string          $appKey       If null, will remove listeners from all keys
	 * @param IRTSAppEventListener ...$listeners Listeners to remove
	 */
	public function removeListeners(?string $appKey, IRTSAppEventListener ...$listeners):void;

	/**
	 * Remove all listeners
	 * @param null|string $appKey If given, remove all listeners for a key
	 */
	public function removeAllListeners(?string $appKey):void;
}