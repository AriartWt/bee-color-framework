<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/10/18
 * Time: 16:58
 */

namespace wfw\cli\wfw\templates\confs;

/**
 * Template de création du fichier site/config/conf.json
 */
class SiteConfTemplate {
	/** @var string $_dbHost */
	private $_dbHost;
	/** @var string $_dbName */
	private $_dbName;
	/** @var string $_dbLogin */
	private $_dbLogin;
	/** @var string $_dbPassword */
	private $_dbPassword;
	/** @var string $_mssAddr */
	private $_mssAddr;
	/** @var string $_mssDb */
	private $_mssDb;
	/** @var string $_mssLogin */
	private $_mssLogin;
	/** @var string $_mssPassword */
	private $_mssPassword;
	/** @var string $_path */
	private $_path;

	/**
	 * SiteConfTemplate constructor.
	 *
	 * @param string $dbHost
	 * @param string $dbName
	 * @param string $dbLogin
	 * @param string $dbPassword
	 * @param string $mssAddr
	 * @param string $mssDb
	 * @param string $mssLogin
	 * @param string $mssPassword
	 * @param string $path
	 */
	public function __construct(
		string $dbHost, string $dbName, string $dbLogin, string $dbPassword,
		string $mssAddr, string $mssDb, string $mssLogin, string $mssPassword,
		string $path = __DIR__."/site.conf.template.php") {
		$this->_dbName = $dbName;
		$this->_dbHost = $dbHost;
		$this->_dbLogin = $dbLogin;
		$this->_dbPassword = $dbPassword;
		$this->_mssAddr = $mssAddr;
		$this->_mssDb = $mssDb;
		$this->_mssLogin = $mssLogin;
		$this->_mssPassword = $mssPassword;
		$this->_path = $path;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		ob_start();
		require $this->_path;
		return ob_get_clean();
	}
}