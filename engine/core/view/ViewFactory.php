<?php
namespace wfw\engine\core\view;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Crée des vues en se basant sur Dice.
 */
final class ViewFactory implements IViewFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * ViewFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $viewClass Classe de la vue à créer.
	 * @param array  $params    Paramètres de création
	 * @return IView
	 */
	public function create(string $viewClass, array $params = []): IView {
		return $this->_factory->create($viewClass,$params,[IView::class]);
	}
}