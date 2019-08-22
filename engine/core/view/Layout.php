<?php
namespace wfw\engine\core\view;

use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

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
	/** @var null|IJsScriptManager $_js */
	private $_js;
	/** @var null|ICSSManager $_css */
	private $_css;
	/** @var string $_version */
	private $_version;

	/**
	 * Layout constructor.
	 *
	 * @param null|string           $viewPath (optionnel) Chemin vers la vue.
	 * @param null|ICSSManager      $css
	 * @param null|IJsScriptManager $js
	 * @param string                $version
	 */
	public function __construct(
		?string $viewPath = null,
		?ICSSManager $css = null,
		?IJsScriptManager $js = null,
		string $version = '0.0'
	) {
		parent::__construct($viewPath);
		$this->_useViewCacheDirective = true;
		$this->_allowCache = false;
		$this->_css = $css;
		$this->_js = $js;
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
	 * @param string $version
	 */
	protected function setVersion(string $version): void{
		$this->_version = $version;
	}

	/**
	 * @return string
	 */
	protected function getVersion():string{
		return $this->_version;
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

	/**
	 * @return IJsScriptManager
	 * @throws IllegalInvocation
	 */
	public function getJSManager():IJsScriptManager{
		if(is_null($this->_js)) throw new IllegalInvocation(
			"No JsScriptManager defined in this layout. Please define it through constructor."
		);
		return $this->_js;
	}

	/**
	 * @return ICSSManager
	 * @throws IllegalInvocation
	 */
	public function getCssManager():ICSSManager{
		if(is_null($this->_css)) throw new IllegalInvocation(
			"No CSSManager defined in this layout. Please define it through constructor."
		);
		return $this->_css;
	}

	/**
	 * @return callable
	 */
	public function getCSSImportCallable():callable{
		if(is_null($this->_css)) return function(){};
		$cssManager = $this->_css;
		$version = sha1($this->_version);
		return function(string $key, string $buffer) use ($cssManager,$version):string{
			return str_replace($key,$cssManager->write("?v=$version"),$buffer);
		};
	}

	/**
	 * @return callable
	 */
	public function getJSImportCallable():callable{
		if(is_null($this->_js)) return function(){};
		$jsManager = $this->_js;
		$version = sha1($this->_version);
		return function(string $key,string $buffer)use($jsManager,$version):string{
			return str_replace($key,$jsManager->write("?v=$version"),$buffer);
		};
	}
}