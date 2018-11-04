<?php
namespace wfw\engine\plugin\pictureViewer;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\view\View;
use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;
use wfw\engine\lib\HTML\resources\SvgImporter;

/**
 * Picture viewer de base.
 */
final class PictureViewer extends View implements IPictureViewer {
	/** @var IPictureViewerOptions $_options */
	private $_options;
	/** @var ICSSManager $_cssManager */
	private $_cssManager;
	/** @var IJsScriptManager $_jsManager */
	private $_jsManager;
	/** @var IPictureItem[] $_pictures */
	private $_pictures;
	/** @var SvgImporter $_svgImporter */
	private $_svgImporter;
	/** @var int $_incr */
	private static $_incr = 0;
	/** @var int $_id */
	private $_id;

	/**
	 * PictureViewer constructor.
	 *
	 * @param ICSSManager                $cssManager
	 * @param IJsScriptManager           $jsManager
	 * @param ICacheSystem               $cacheSystem
	 * @param IPictureItem[]             $pictures
	 * @param null|IPictureViewerOptions $options (optionnel) Options de création du slider
	 */
	public function __construct(
		ICSSManager $cssManager,
		IJsScriptManager $jsManager,
		ICacheSystem $cacheSystem,
		array $pictures,
		?IPictureViewerOptions $options=null
	){
		$this->_options = $options ?? new PictureViewerOptions();
		parent::__construct($this->_options->viewPath());
		$this->_id = self::$_incr++;
		$this->_cssManager = $cssManager;
		$this->_jsManager = $jsManager;
		$this->_pictures = (function(IPictureItem...$pics){return $pics;})(...$pictures);
		$this->_svgImporter = new SvgImporter('',$cacheSystem);
	}

	/**
	 * @return string
	 */
	public function render(): string {
		if($this->_options->autoplayEnabled()){
			$this->_jsManager->register($this->_options->autoPlayScript());
		}
		$this->_cssManager->register($this->_options->css());
		$this->_cssManager->registerInline($this->createCSSRules());
		return parent::render();
	}

	/**
	 * @brief  Crée toutes les régles CSS dynamiques nécessaire au bon affichage du slider
	 * @return string    Code css
	 */
	private function createCSSRules():string{
		$res="  /*---SLIDER_".$this->_id."_START---*/\n\n";
		foreach($this->_pictures as $k=>$v){
			$res.="    .css-slider #btn_picture_".$this->_id."_$k:checked ~ .slider .picture-list .picture_$k{\n";
			$res.="        opacity:1;\n    }\n";
			if($this->_options->hasBullets()){
				$res.="    .css-slider #btn_picture_".$this->_id."_$k:checked ~ .slider .bullets_$k{\n";
				$res.="        background-color:#FF9900;\n    }\n";
			}
		}
		$res.="\n  /*---SLIDER_".$this->_id."_END---*/\n\n";
		return $res;
	}

	/**
	 * @return IPictureViewerOptions Options de création du slider
	 */
	public function getOptions(): IPictureViewerOptions {
		return $this->_options;
	}

	/**
	 * @return int Identifiant du pictureViewer courant
	 */
	public function getId():int{
		return $this->_id;
	}

	/**
	 * @return IPictureItem[]
	 */
	public function getPictures():array{
		return $this->_pictures;
	}

	/**
	 * @return SvgImporter
	 */
	public function getSvgImporter():SvgImporter{
		return $this->_svgImporter;
	}
}