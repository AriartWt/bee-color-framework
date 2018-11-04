<?php
namespace wfw\engine\package\general\lib;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;

/**
 * Helper pour l'inclusion de l'api javascript.
 */
final class JsApiHelper {
	/** @var array $_appInfos */
	private $_appInfos;
	/** @var string $_apiPath */
	private $_apiPath;
	/** @var string[] $_libsToLoad */
	private $_libsToLoad;
	/** @var string $_webroot */
	private $_webroot;
	/** @var null|string $_csrfToken */
	private $_csrfToken;
	/**
	 * JsApiHelper constructor.
	 *
	 * @param IRouter          $router
	 * @param IConf            $conf
	 * @param null|string      $csrfToken
	 */
	public function __construct(
		IRouter $router,
		IConf $conf,
		?string $csrfToken = null
	) {
		$this->_apiPath = $router->webroot("JavaScript/api/api.js");
		$this->_libsToLoad[] = $router->webroot("JavaScript/api/settings.js");
		$this->_libsToLoad[] = $router->webroot("JavaScript/api/console.js");
		$this->_webroot = $router->webroot();
		$params = $conf->getObject("app/params");
		$params->app = [
			"name" => $conf->getString("app/name"),
			"version" => $conf->getString("app/version")
		];
		$params->uploader = [
			"quotas" => $conf->getString("server/uploader/quotas"),
			"max_size" => $conf->getString("server/uploader/max_size"),
		];
		$this->_appInfos = $params;
		$params->ui = $params->ui ?? [
			"lang" => [
				"replacement_pattern" => $conf->getString("server/lang/replacement_pattern")
			]
		];
		$this->_csrfToken = $csrfToken;
	}

	/**
	 * @param IJsScriptManager $jsManager
	 */
	public function register(IJsScriptManager $jsManager):void{
		if(!$jsManager->isRegistered($this->_apiPath)){
			$jsManager->register($this->_apiPath);
			$jsManager->registerVar("webroot",$this->_webroot);
			$jsManager->registerVar("csrfToken",$this->_csrfToken);
			$jsManager->registerVar("appInfos",$this->_appInfos);
		}
	}
}