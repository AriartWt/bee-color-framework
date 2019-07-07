<?php 
namespace wfw\engine\lib\HTML\resources\css;

use wfw\engine\lib\HTML\resources\FileIncluder;

/**
 *  Gestionnaire d'inclusion de fichiers css
 */
final class CSSManager extends FileIncluder implements ICSSManager {
	/** @var array $_inlineCSS */
	protected $_inlineCSS=array();
	/** @var bool $_preload */
	private $_preload;

	/**
	 * CSSManager constructor.
	 *
	 * @param bool $preload
	 * @param int  $exceptionFlag
	 */
	public function __construct(bool $preload = true, int $exceptionFlag = self::EMIT_EXCEPTION_OFF) {
		parent::__construct($exceptionFlag);
		$this->_preload = $preload;
	}

	/**
	 *   Ecrit les dépendances CSS dans le code HTML. (AInsi que les inclusions de CSS généré dynamiquement)
	 * @param  string    $add_to_url Partie à ajouter à l'url (cache burst, arguments...)
	 * @return string                Code html à écrire sur le document
	 */
	public function write(string $add_to_url=""):string{
		$res="";
		foreach($this->_registered as $v){
			if($this->_preload) header("Link: <$v$add_to_url>; rel=preload; as=style",false);
			$res.="<link rel=\"stylesheet\" href=\"$v$add_to_url\">";
		}
		if(count($this->_inlineCSS)>0){
			$res.="<style>\n\n/*---AUTO_GENERATED_CSS---*/\n\n"
				.implode("\n\n /* --- */ \n\n",$this->_inlineCSS)."\n\n</style>";
		}
		return $res;
	}
	/**
	 *   Permet d'enregistrer du CSS généré dynamiquement
	 * @param  string    $txt Instructions CSS à écrire
	 * @return void
	 */
	public function registerInline(string $txt):void{
        $this->_inlineCSS[]=$txt;
	}
}
 