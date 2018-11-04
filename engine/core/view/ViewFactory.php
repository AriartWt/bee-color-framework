<?php
namespace wfw\engine\core\view;

use Dice\Dice;

/**
 * Crée des vues en se basant sur Dice.
 */
final class ViewFactory implements IViewFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * ViewFactory constructor.
	 *
	 * @param Dice $dice dice
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * @param string $viewClass Classe de la vue à créer.
	 * @param array  $params    Paramètres de création
	 * @return IView
	 */
	public function create(string $viewClass, array $params = []): IView {
		if(is_a($viewClass,IView::class,true)){
			/** @var IView $view */
			$view = $this->_dice->create($viewClass,$params);
			return $view;
		}else{
			throw new \InvalidArgumentException("$viewClass doesn't implements ".IView::class);
		}
	}
}