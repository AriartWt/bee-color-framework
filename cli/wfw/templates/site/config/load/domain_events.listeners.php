<?php

use wfw\engine\core\conf\IModuleDescriptor;
use wfw\engine\core\conf\WFWModulesCollector;

WFWModulesCollector::collectModules();
return array_merge(
	require dirname(__DIR__,3)."/engine/config/default.domain_events.listeners.php",
	require dirname(__DIR__)."/site.domain_events.listeners.php",
	...array_map(
		function($module){
			if(is_a((string) $module, IModuleDescriptor::class, true)){
				/** @var IModuleDescriptor $module */
				return $module::domainEventListeners();
			}else return [];
		}, WFWModulesCollector::modules()
	)
);