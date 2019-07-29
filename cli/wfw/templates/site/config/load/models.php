<?php

use wfw\engine\core\conf\IModuleDescriptor;
use wfw\engine\core\conf\WFWModulesCollector;

WFWModulesCollector::collectModules();
return array_merge(
	require dirname(__DIR__,3)."/engine/config/default.models.php",
	require dirname(__DIR__)."/site.models.php",
	...array_map(
		function($module){
			if(is_a((string) $module, IModuleDescriptor::class, true)){
				/** @var IModuleDescriptor $module */
				return $module::models();
			}else return [];
		}, WFWModulesCollector::modules()
	)
);