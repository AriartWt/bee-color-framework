<?php
namespace wfw\engine\package\news\lib\helper;

use wfw\engine\lib\PHP\types\PHPString;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\news\lib\helper\IArticle;

/**
 * Adapt an Aricle (dto) to a regular article
 */
final class ArticleAdapter implements IArticle {
	/** @var Article $_article */
	private $_article;
	/** @var string $_shortDescription */
	private $_shortDescription;

	/**
	 * ArticleAdapter constructor.
	 *
	 * @param Article $article
	 */
	public function __construct(Article $article) {
		$this->_article = $article;
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML(mb_convert_encoding($article->getContent(), 'HTML-ENTITIES', 'UTF-8'));
		$xpath = new \DOMXPath($dom);
		$nodes = $xpath->query("*/text()");
		if($nodes->item(0))
			$this->_shortDescription = $this->innerHTML($nodes->item(0));
		else $this->_shortDescription = $article->getContent();
		$tags = ['</p>','<br />','<br>','<hr />','<hr>','</h1>','</h2>','</h3>','</h4>','</h5>','</h6>','</div>',"</span>"];
		$str = strip_tags(str_replace($tags,"\n",$this->_shortDescription));
		$str = str_replace("\n","<br>",$str);
		$str = preg_replace("/(<br>){2,}/","<br>",$str);
		$str = preg_replace("/^<br>/","",$str);
		$str = preg_replace("/<br>$/","",$str);
		$this->_shortDescription = mb_substr($str,0,200,"utf-8");
		if(strlen($this->_shortDescription) < strlen($str)) $this->_shortDescription.="...";
		$this->_shortDescription = str_replace(['"',"'"],["&quot;","&apos;"],$this->_shortDescription);
	}

	/**
	 * @param \DOMNode $node
	 * @return string
	 */
	private function innerHTML(\DOMNode $node){
		$innerHTML = $node->ownerDocument->saveHTML();
		$children  = $node->childNodes ?? [];
		foreach ($children as $child) {
			$innerHTML .= $node->ownerDocument->saveHTML($child);
		}
		return $innerHTML;
	}

	/**
	 * @return string
	 */
	public function getCreationDate(): string {
		return $this->printdate($this->_article->getCreationDate());
	}

	/**
	 * @param float $timestamp
	 * @return string
	 */
	private function printdate(float $timestamp):string{
		return strftime("%d-%m-%Y à %H:%M:%S",$timestamp);
	}

	/**
	 * @return string
	 */
	public function getEditDate(): string {
		if(count($this->_article->getEditions())>0){
			$editions = $this->_article->getEditions();
		   return  $this->printdate(array_pop($editions)["date"]);
		}else return '';
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->_article->getContent();
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->_article->getTitle();
	}

	/**
	 * @return string
	 */
	public function getImage(): string {
		return $this->_article->getVisualLink();
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->_article->getId();
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->_shortDescription ?? "";
	}

	/**
	 * @return string
	 */
	public function getSlug():string{
		return (new PHPString($this->getTitle()))->removeAccents()->stripNonAlphanum()->encodeURIComponent();
	}
}