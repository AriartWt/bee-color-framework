<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/05/18
 * Time: 14:29
 */

namespace wfw\engine\core\security\data\sanitizer;

/**
 * Purifier basé sur HTMLPurifier version 4.10.0, avec ajout des balises HTML5 audio, video, source.
 * Les autres balises HTML5 ne sont pas supportées.
 * Ajoute le support des iframes pour les liens viemo et youtube.
 * Autorise l'utilisation de l'attribut allowfullscreen pour les iframes.
 * Autorise l'utilisation des attributs class, style, id pour tous les éléments.
 * Autorise l'utilisation de l'attribut target pour les liens.
 */
final class HTMLPurifierBasedSanitizer implements IHTMLSanitizer
{
	/** @var \HTMLPurifier $_purifier */
	private $_purifier;
	/** @var bool $_LOADED */
	private static $_LOADED = false;

	/**
	 * HTMLPurifierBasedSanitizer constructor.
	 */
	public function __construct() {
		if(!self::$_LOADED){
			require_once(ENGINE."/lib/htmlPurifier/HTMLPurifier.auto.php");
			self::$_LOADED = true;
		}

		$config = \HTMLPurifier_Config::createDefault();
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('CSS.AllowTricky', true);
		$config->set('Cache.SerializerPath', '/tmp');
		$config->set('Attr.EnableID', true);
		// Allow iframes from:
		// YouTube.com Vimeo.com
		$config->set('HTML.SafeIframe', true);
		$config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
		/*$config->set('HTML.Allowed',implode(',',[
			"*[style|class]",
			"a[href|target]",
			"img[src]",
			"video[src|controls|type|autoplay|loop|preload|poster]",
			"audio[src|controls|type|autoplay|loop|preload]",
			"source[src|type]",
			"p,div,span,font[color],b,strike,ul,ol,h1,h2,h3,br,li"
		]));*/
		$config->set('HTML.AllowedElements', 'a,p,div,span,font,b,strike,ul,ol,video,audio,h1,h2,h3,br,img,li,source');
		$config->set('HTML.AllowedAttributes','a.target,a.href,*.class,*.style,*.id,img.src,img.style,font.color,'
			.'video.src,video.controls,video.muted,video.type,video.autoplay,video.loop,video.preload,video.poster,video.style,'
			.'audio.src,audio.controls,audio.type,audio.autoplay,audio.loop,audio.preload,source.src,source.type');
		// Set some HTML5 properties
		$config->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
		$config->set('HTML.DefinitionRev', 1);
		$config->set('CSS.AllowedProperties',"color,height,width,float,font-weight,text-align");
		
		if ($def = $config->maybeGetRawHTMLDefinition()) {
			// http://developers.whatwg.org/the-video-element.html#the-video-element
			$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
				'loop' => 'Enum#loop',
				'muted' => 'Enum#muted',
				'autoplay' => 'Enum#autoplay',
				'style' => 'Text'
			));
			$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
				'loop' => 'Enum#loop',
				'autoplay' => 'Enum#autoplay'
			));
			$def->addElement('source', 'Block', 'Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
			));
			$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
			$def->addAttribute('img','style','Text');
		}
		$this->_purifier = new \HTMLPurifier($config);
	}

	/**
	 * @param string $html à purifier
	 * @return string html purifié
	 */
	public function sanitizeHTML(string $html): string{
		return $this->_purifier->purify($html);
	}
}