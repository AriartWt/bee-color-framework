<?php
namespace wfw\engine\core\view;

/**
 * Permet de créer des Layout.
 */
interface ILayoutFactory {
	/**
	 * Crée un layout
	 * @param string $layoutClass
	 * @param array  $params
	 * @return ILayout
	 */
	public function create(string $layoutClass, array $params=[]):ILayout;
}