<?php

namespace wfw\engine\core\app\factory;

/**
 * Generic factory for the all app (to avoid dependencie upon a DIC)
 */
interface IGenericAppFactory {
	/**
	 * @param string $class  Class to create
	 * @param array  $params (optionnal) Parameters to pass to the class
	 * @param array  $isA    (optionnal) class or interface list that $class must extends or implements
	 * @return mixed A builded object
	 */
	public function create(string $class,array $params=[],array $isA=[]);
}