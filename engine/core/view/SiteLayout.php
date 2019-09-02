<?php

namespace wfw\engine\core\view;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\lib\HTML\resources\SvgImporter;
use wfw\engine\package\general\lib\JsApiHelper;
use wfw\engine\package\users\domain\types\Admin;

/**
 * Default site layout that can be extended.
 */
class SiteLayout extends Layout {
	private $_svg;
	/** @var IConf $_conf */
	private $_conf;
	/** @var ICacheSystem $_cache */
	private $_cache;
	/** @var IRouter $_router */
	private $_router;
	/** @var ISession $_session */
	private $_session;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var null|string $_adminPanelPath */
	private $_adminPanelPath;

	/**
	 * SiteLayout constructor.
	 *
	 * @param IConf            $conf
	 * @param IRouter          $router
	 * @param ISession         $session
	 * @param IJsScriptManager $js
	 * @param ICacheSystem     $cache
	 * @param INotifier        $notifier
	 * @param ICSSManager      $css
	 * @param null|string      $adminPanelPath
	 * @param string           $xFrameOptions
	 * @param null|string      $viewPath
	 */
	public function __construct(
		IConf $conf,
		IRouter $router,
		ISession $session,
		IJsScriptManager $js,
		ICacheSystem $cache,
		INotifier $notifier,
		ICSSManager $css,
		?string $adminPanelPath = 'JavaScript/web/adminPanel.js',
		string $xFrameOptions = "SAMEORIGIN",
		?string $viewPath = null
	){
		parent::__construct($viewPath, $css, $js, $version = sha1(
			$conf->getString("server/framework/version")
			."-app-".($conf->getString("app/version") ?? '0.0')
		));
		$this->_svg = new SvgImporter(
			dirname(__DIR__,3)
			."/site/package/web/webroot/Image/svg",
			$cache
		);
		$this->_conf = $conf;
		$this->_cache = $cache;
		$this->_router = $router;
		$this->_session = $session;
		$this->_notifier = $notifier;
		$this->_adminPanelPath = $adminPanelPath;
		(new JsApiHelper($router,$conf,$session->get("csrfToken"),"?v=$version"))
			->register($js);
		$this->addHeader("X-FRAME-OPTIONS: $xFrameOptions");
	}

	/**
	 * @return string
	 */
	public function render(): string {
		if(!is_null($this->_adminPanelPath)
			&& $this->_session->exists("user")
			&& $this->_session->get('user')->getType() instanceof Admin
		) $this->getJSManager()->register($this->_router->webroot($this->_adminPanelPath));
		return parent::render();
	}

	/**
	 * @return mixed
	 */
	public function getConf():IConf {
		return $this->_conf;
	}

	/**
	 * @return SvgImporter
	 */
	public function getSvg():SvgImporter{
		return $this->_svg;
	}

	/**
	 * @param string $name
	 * @param string $package
	 * @return string
	 */
	public function svgImport(string $name,string $package="web"):string{
		return $this->_svg->import(
			dirname(__DIR__,3)
			."/site/package/$package/webroot/Image/svg/$name",
			true
		);
	}

	/**
	 * @return mixed
	 */
	public function getRouter():IRouter {
		return $this->_router;
	}

	/**
	 * @return mixed
	 */
	public function getSession():ISession {
		return $this->_session;
	}

	/**
	 * @return ICacheSystem
	 */
	public function getCache():ICacheSystem {
		return $this->_cache;
	}

	/**
	 * @return INotifier
	 */
	public function getNotifier():INotifier {
		return $this->_notifier;
	}
}