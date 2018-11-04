<?php
namespace wfw\engine\core\view;

use Dice\Dice;

/**
 * Factory basée sur Dice pour la création d'un layout
 */
final class LayoutFactory implements ILayoutFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * LayoutFactory constructor.
	 *
	 * @param Dice $dice Dice
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * Crée un layout
	 *
	 * @param string $layoutClass Layout à instancier. Doit implémenter ILayout
	 * @param array  $params      Paramètres.
	 * @return ILayout
	 */
	public function create(string $layoutClass, array $params = []): ILayout {
		if(is_a($layoutClass,ILayout::class,true)){
			/** @var ILayout $layout */
			$layout = $this->_dice->create($layoutClass,$params);
			return $layout;
		}else{
			throw new \InvalidArgumentException("$layoutClass doesn't implements ".ILayout::class);
		}
	}
}