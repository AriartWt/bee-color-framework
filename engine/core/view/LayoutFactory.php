<?php
namespace wfw\engine\core\view;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory basée sur Dice pour la création d'un layout
 */
final class LayoutFactory implements ILayoutFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * LayoutFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Crée un layout
	 *
	 * @param string $layoutClass Layout à instancier. Doit implémenter ILayout
	 * @param array  $params      Paramètres.
	 * @return ILayout
	 */
	public function create(string $layoutClass, array $params = []): ILayout {
		return $this->_factory->create($layoutClass,$params,[ILayout::class]);
	}
}