<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * RTS event observer. Note that the special appKey * mean 'all'.
 * An app registered as listener on * will recieve all events.
 */
interface IRTSEventObserver extends IRTSEventDispatcher {
	/**
	 * @param null|string $appKey If given, return only listeners registered for an appKey
	 * @return IRTSEventListener[] Return all listeners
	 */
	public function getListeners(?string $appKey):array;

	/**
	 * @param string|null       $appKey       If null, will add listeners to all keys
	 * @param IRTSEventListener ...$listeners Listeners that listen to IRTSEvents
	 */
	public function addListeners(?string $appKey, IRTSEventListener ...$listeners):void;

	/**
	 * @param null|string       $appKey       If null, will remove listeners from all keys
	 * @param IRTSEventListener ...$listeners Listeners to remove
	 */
	public function removeListeners(?string $appKey, IRTSEventListener ...$listeners):void;

	/**
	 * Remove all listeners
	 * @param null|string $appKey If given, remove all listeners for a key
	 */
	public function removeAllListeners(?string $appKey):void;
}