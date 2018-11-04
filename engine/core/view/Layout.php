<?php
namespace wfw\engine\core\view;

/**
 * Classe layout.
 */
abstract class Layout extends View implements ILayout {
	/** @var IView $view */
	private $_view;

	/**
	 * Layout constructor.
	 *
	 * @param null|string $viewPath (optionnel) Chemin vers la vue.
	 */
	public function __construct(?string $viewPath = null) {
		parent::__construct($viewPath);
	}

	/**
	 * @param IView $view Change la vue chargée par le Layout.
	 */
	public function setView(IView $view):void{
		$this->_view = $view;
	}

	/**
	 * @return IView Vue associée.
	 */
	public function getView():?IView{
		return $this->_view;
	}

	/**
	 * @return array
	 */
	public function infos():array{
		return [];
	}
}