<?php
namespace wfw\engine\package\uploader\handlers\action;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Handler de base
 */
abstract class UploadHandler implements IActionHandler {
	/** @var IConf $_conf */
	protected $_conf;
	/** @var string $_path */
	private $_path;
	/** @var string $_folder */
	private $_folder;
	/** @var ITranslator $_translator */
	protected $_translator;

	/**
	 * UploadHandler constructor.
	 *
	 * @param IConf       $conf
	 * @param ITranslator $translator
	 * @param null|string $uploadsConfKey
	 */
	public function __construct(IConf $conf, ITranslator $translator,?string $uploadsConfKey=null) {
		$this->_conf = $conf;
		$this->_translator = $translator;
		$this->_path = dirname(__DIR__,5)."/".$conf->getString($uploadsConfKey ?? "server/uploader/dir");
		if(!is_dir($this->_path)) mkdir($this->_path,0770,true);
		$this->_folder = str_replace(dirname(__DIR__,5)."/site/webroot/",'',$this->_path);
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
	 * Purifie le dernier élément d'un chemin en retirant tous les caractères susceptibles de poser
	 * problème dans un nom de fichier ou de dossier
	 *
	 * @param string $path Chemin à purifier
	 * @return string
	 */
	protected function sanitize(string $path):string{
		$tmp = explode("/",strip_tags($path));
		$tmp[count($tmp)-1] = (new PHPString(array_pop($tmp)))
			->removeAccents()->stripNonAlphanum("_",".-");
		return implode("/",$tmp);
	}

	/**
	 * Résoud un chemin relatif en chemin absolu.
	 * @param string $path Chemin à résoudre
	 * @return string
	 */
	private function absolutePath(string $path):string{
		$path = str_replace(array('/', '\\'), '/', $path);
		$parts = array_filter(explode('/', $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.' == $part) continue;
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return implode('/', $absolutes);
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