<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/05/18
 * Time: 11:42
 */

namespace wfw\engine\package\uploader\handlers\action;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\conf\IConf;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Handler de base
 */
abstract class UploadHandler implements IActionHandler
{
	/** @var IConf $_conf */
	protected $_conf;
	/** @var string $_path */
	private $_path;
	/** @var string $_folder */
	private $_folder;

	/**
	 * UploadHandler constructor.
	 *
	 * @param IConf       $conf
	 * @param null|string $uploadsConfKey
	 */
	public function __construct(IConf $conf,?string $uploadsConfKey=null) {
		$this->_conf = $conf;
		$this->_path = ROOT."/".$conf->getString($uploadsConfKey ?? "server/uploader/dir");
		$this->_folder = str_replace(SITE."/webroot/",'',$this->_path);
	}

	/**
	 * @return string
	 */
	protected function getUploadFolderPath():string{
		return $this->_path;
	}

	/**
	 * @return string
	 */
	protected function getUploadFolderName():string{
		return $this->_folder;
	}

	/**
	 * @brief Résoud le chemin d'accés au fichier dans le repertoir réel d'uploads.
	 * @param string $path Chemin à résoudre
	 * @return string
	 * @throws \InvalidArgumentException si le chemin n'est pas situé dans le dossiers uploads
	 */
	protected function realPath(string $path):string{
		$res = "/".$this->absolutePath(
			$this->getUploadFolderPath().'/'.str_replace($this->_folder,'',$path)
		);
		if((new PHPString($res))->startBy($this->getUploadFolderPath())) return $res;
		throw new \InvalidArgumentException(
			"$path leads to $res, which is not a subdir of $this->_path"
		);
	}

	/**
	 * Résoud un chemin relatif en chemin absolu.
	 * @param string $path Chemin à résoudre
	 * @return string
	 */
	private function absolutePath(string $path):string{
		$path = str_replace(array('/', '\\'), DS, $path);
		$parts = array_filter(explode(DS, $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.' == $part) continue;
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return implode(DS, $absolutes);
	}

	/**
	 * @return int
	 */
	protected function getUploadDirectorySize():int{
		$bytestotal = 0;
		$path = realpath($this->getUploadFolderPath());
		if($path!==false && $path!='' && file_exists($path)){
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
				$path,
				FilesystemIterator::SKIP_DOTS)
			);
			foreach($iterator as $object){
				$bytestotal += $object->getSize();
			}
		}
		return $bytestotal;
	}
}