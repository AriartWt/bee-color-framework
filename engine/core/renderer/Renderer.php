<?php
namespace wfw\engine\core\renderer;

use wfw\engine\core\view\IView;

/**
 * Permet de rendre une vue.
 */
final class Renderer implements IRenderer {
	/**
	 * @param IView $view
	 * @return mixed
	 */
	public function render(IView $view): void {
		echo $view->render();
	}
}