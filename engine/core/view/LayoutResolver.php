<?php
namespace wfw\engine\core\view;

use wfw\engine\core\action\IAction;
use wfw\engine\package\general\layouts\blank\BlankLayout;

/**
 * Permet de résoudre un Layout en se basant sur une action.
 */
final class LayoutResolver implements ILayoutResolver {
	/** @var string $_defaultLayoutClass */
	private $_defaultLayoutClass;
	/** @var ILayoutFactory $_factory */
	private $_factory;

	/**
	 * LayoutResolver constructor.
	 *
	 * @param ILayoutFactory $factory            Factory de layout
	 * @param string         $defaultLayoutClass Classe du layout par défaut.
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ILayoutFactory $factory,string $defaultLayoutClass) {
		if(is_a($defaultLayoutClass,ILayout::class,true)){
			$this->_defaultLayoutClass = $defaultLayoutClass;
			$this->_factory = $factory;
		}else{
			throw new \InvalidArgumentException(
				"$defaultLayoutClass doesn't implements ".ILayout::class
			);
		}
	}

	/**
	 * @param IAction $action Action permettant de determiner le layout
	 * @return ILayout
	 */
	public function resolve(IAction $action): ILayout {
		if($action->getRequest()->isAjax() || strpos($action->getInternalPath(),"Css") === 0){
			return $this->_factory->create(BlankLayout::class);
		}else{
			return $this->_factory->create($this->_defaultLayoutClass);
		}
	}
}