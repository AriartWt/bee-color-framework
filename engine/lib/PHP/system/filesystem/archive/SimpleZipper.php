<?php
namespace wfw\engine\lib\PHP\system\archive\filsystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use wfw\engine\lib\PHP\system\archive\errors\ZIPFailure;
use ZipArchive;

/**
 *  Facilite les opératon de zip/unzip en se basant sur l'extension php "zip"
 */
class SimpleZipper {
	/** @var string $_source */
	private $_source;

	/**
	 *  SimpleZipper constructor.
	 *
	 * @param string $source Dosser/fichier à zipper/dezipper
	 *
	 * @throws ZIPFailure Si le chemin n'est pas un fichier valide ou si l'extension n'est ZIP n'est pas chargée
	 */
	public function __construct(string $source) {
		if (extension_loaded('zip')) {
			if (file_exists($source)) {
				$this->_source = $source;
			}else{
				throw new ZIPFailure("$source is not a valiade paht !");
			}
		}else{
			throw new ZIPFailure("Required zip extension not found !");
		}
	}

	/**
	 *  Permet de zipper un dossier de façon récursive
	 *
	 * @param string $destination Fichier de destination du zipp
	 *
	 * @return bool
	 * @throws ZIPFailure si un fichier/dossier ne peut être ajouté à l'archive ou si un fichier/dossier ne peut être lu
	 */
	public function zip(string $destination) :bool {
		$source = $this->_source;
		$zip = new ZipArchive();
		if ($zip->open($destination, ZipArchive::CREATE)) {
			$source = realpath($source);
			if (is_dir($source)) {
				$iterator = new RecursiveDirectoryIterator($source);
				$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
				$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
				foreach ($files as $file) {
					$file = realpath($file);
					if (is_dir($file)) {
						$zip->addEmptyDir(str_replace($source.'/', '', $file));
					} else if (is_file($file)) {
						if(is_readable($file)){
							if(!$zip->addFile($file,str_replace($source.'/', '', $file))){
								throw new ZIPFailure("Cannot add file $file in archive !");
							}
						}else{
							throw new ZIPFailure("File $file is not readable !");
						}
					}
				}
			} else if (is_file($source)) {
				if(!$zip->addFile($source,basename($source))){
					throw new ZIPFailure("Cannot add file $source in archive !");
				}
			}
		}
		$var=$zip->close();
		return $var;
	}

	/**
	 *  Dézip un fichier .zip
	 *
	 * @param string $dest Dossier de destination du zip décompressé
	 *
	 * @return boolean True si l'oppération a réussi, false sinon
	 */
	public function unzip(string $dest):bool{
		$path = $this->_source;
		$zip=new ZipArchive();
		$res=$zip->open($path);
		if(is_bool($res) && $res){
			$zip->extractTo($dest);
			$zip->close();
			return true;
		}else{
			return false;
		}
	}
}