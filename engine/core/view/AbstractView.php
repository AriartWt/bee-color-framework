<?php

namespace wfw\engine\core\view;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\SvgImporter;

/**
 * Vue abstraite
 */
abstract class AbstractView extends View {
	/** @var ICSSManager $_css */
	protected $_css;
	/** @var IRouter $_router */
	protected $_router;
	/** @var SvgImporter $_svg */
	protected $_svg;
	/** @var string $_cssFile */
	protected $_cssFile;
	/** @var string $_package */
	protected $_package;

	/**
	 * Legals constructor.
	 *
	 * @param IRouter      $router
	 * @param ICSSManager  $css
	 * @param ICacheSystem $cache
	 * @param string       $cssFile Nom du fichier css à charger (sans l'extension)
	 * @param string       $package Nom du package (defaut : web)
	 */
	public function __construct(
		IRouter $router,
		ICSSManager $css,
		?ICacheSystem $cache = null,
		?string $cssFile = null,
		string $package = "web"
	) {
		parent::__construct();
		$this->_css = $css;
		$this->_cssFile = $cssFile;
		$this->_router = $router;
		if($cache){
			$this->_svg = new SvgImporter(
				dirname(dirname(dirname(__DIR__)))
				."/site/package/$package/webroot/Image/svg",
				$cache
			);
		}
		$this->_package = $package;
	}

	/**
	 * @return null|SvgImporter
	 */
	public function getSvg(): ?SvgImporter {
		return $this->_svg;
	}

	/**
	 * @return IRouter
	 */
	public function getRouter(): IRouter {
		return $this->_router;
	}

	/**
	 * @return string
	 */
	public function render(): string {
		if($this->_cssFile){
			$this->_css->register(
				$this->_router->webroot("Css/$this->_package/$this->_cssFile.css")
			);
		}
		return parent::render();
	}
}