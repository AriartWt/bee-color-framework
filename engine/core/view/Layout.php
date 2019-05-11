<?php
namespace wfw\engine\core\view;

/**
 * Classe layout.
 */
abstract class Layout extends View implements ILayout {
	/** @var IView $view */
	private $_view;
	/** @var bool $_useViewCacheDirective */
	private $_useViewCacheDirective;
	/** @var bool $_allowCache */
	private $_allowCache;

	/**
	 * Layout constructor.
	 *
	 * @param null|string $viewPath (optionnel) Chemin vers la vue.
	 */
	public function __construct(?string $viewPath = null) {
		parent::__construct($viewPath);
		$this->_useViewCacheDirective = true;
		$this->_allowCache = false;
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
	 * @return bool
	 */
	public function allowCache(): bool {
		if($this->_useViewCacheDirective) return $this->getView()->allowCache();
		else return false;
	}

	/**
	 * Not take the child views cache policies in consideration anymore and disable cache.
	 */
	protected function disableCache():void {
		$this->_useViewCacheDirective = false;
		$this->_allowCache = false;
	}

	/**
	 * Not take the child views cache policies in consideration anymore and enable cache.
	 */
	protected function enableCache(): void {
		$this->_allowCache = true;
		$this->_useViewCacheDirective = false;
	}

	/**
	 * @return array
	 */
	public function infos():array{
		return [];
	}
}