<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEvent;
use wfw\daemons\rts\server\app\events\IRTSEventDispatcher;

/**
 * Interface IAppsManager
 *
 * @package wfw\daemons\rts\server\app
 */
interface IRTSAppsManager extends IRTSEventDispatcher {
	/**
	 * @param IRTSApp ...$apps Apps to add to the manager
	 */
	public function addApps(IRTSApp ...$apps):void;

	/**
	 * @param string|null $appKey Dispatch data for all apps that listen for appKey. If null, dispatch to all
	 *                            listeners
	 * @param string      $data   Data to dispatch
	 * @return IRTSEvent[] Events arrays produced by apps while receiving data
	 */
	public function dispatchData(?string $appKey, string $data):array;
}