<?php
namespace wfw\engine\core\view;

/**
 * Layout
 */
interface ILayout extends IView {
	/**
	 * @param IView $view Vue à rendre dans le layout.
	 */
	public function setView(IView $view):void;
}