<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppEventDispatcher;

/**
 * Interface IAppsManager
 *
 * @package wfw\daemons\rts\server\app
 */
interface IRTSAppsManager extends IRTSAppEventDispatcher {
	/**
	 * @return IRTSApp[]
	 */
	public function getAll():array;

	/**
	 * @param IRTSApp ...$apps Apps to add to the manager
	 */
	public function addApps(IRTSApp ...$apps):void;

	/**
	 * @param string|null $appKey Dispatch data for all apps that listen for appKey. If null, dispatch to all
	 *                            listeners
	 * @param string      $data   Data to dispatch
	 */
	public function dispatchData(?string $appKey, string $data):void;

	/**
	 * @return string[] all apps name that are currently managed
	 */
	public function getAppNames():array;
}