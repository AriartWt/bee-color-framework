<?php
namespace wfw\engine\lib\HTML\text;

use wfw\engine\lib\PHP\types\PHPString;

/**
 * Permet de césurer des textes français.
 */
final class FrenchHyphenator implements IHyphenator {
	/** @var ISyllableCarver $_carver */
	private $_carver;
	/** @var string $_hyphens */
	private $_hyphens;
	/** @var int $_minWordLength */
	private $_minWordLength;
	/** @var int $_minLastSyllableLength */
	private $_minLastSyllableLength;

	/**
	 * FrenchHyphenator constructor.
	 *
	 * @param ISyllableCarver $carver Découpeur en syllables.
	 * @param string          $hyphens Caractère de césure
	 * @param int             $minWordLength Taille minimum du mot à césurer
	 * @param int             $minLastSyllableLength Taille minimum d'une syllable en fin de mot.
	 */
	public function __construct(
		ISyllableCarver $carver,
		string $hyphens = "&shy;",
		int $minWordLength = 5,
		int $minLastSyllableLength = 3
	) {
		$this->_carver = $carver;
		$this->_hyphens = $hyphens;
		$this->_minWordLength = $minWordLength;
		$this->_minLastSyllableLength = $minLastSyllableLength;
	}

	/**
	 * @param string $text Texte à césurer.
	 * @return string Texte césuré
	 */
	public function hyphenate(string $text): string {
		$words = explode(' ',$text);
		$res = [];
		foreach($words as $word){
			$res[] = $this->hyphenateSyllabedWord($this->_carver->carve($word));
		}
		return implode(' ',$res);
	}

	/**
	 * @param array $word Mot découpé en syllables dans lequel on souhaite introduire les césures
	 * @return string mot césuré
	 */
	private function hyphenateSyllabedWord(array $word):string{
		$res="";
		$str = implode('',$word);
		$strLength= strlen($str);

		//on ne ocupe pas un mot de moins de 4 lettres
		if($strLength<$this->_minWordLength){//min-word-cesure
			return $str;
		}
		//on ne coupe pas à la première syllable si elle n'est composée que d'une seule lettre
		if(strlen($word[0])==1){
			$disqualified[]=0;
		}
		$onceHyphen=false;
		$wordLength=count($word);
		foreach($word as $k=> $syllable){
			//on ne coupe pas avant ou après un apostrophe
			if($k<$wordLength-1 && $k>1){
				if($k===$wordLength-2){
					if(strlen($word[$wordLength-1])<$this->_minLastSyllableLength){
						$res.=$word[$wordLength-2].$word[$wordLength-1];
						break;
					}
				}
				if($syllable==="'"){
					$res.=$syllable;
				}else if($k==0 && strlen($syllable)==1){
					$res.=$syllable;
				}else if(strpos($syllable,"-")!==false && !$onceHyphen){
					$res.=(new PHPString($syllable))
						->replaceFirst("-","-".$this->_hyphens);
					$onceHyphen=true;
				}else if(isset($word[$k+1]) && strpos($word[$k+1],"-")!==false && !$onceHyphen){
					$res.=$syllable;
				}else{
					$res.=$syllable.$this->_hyphens;
				}
			}else{
				$res.=$syllable;
			}
		}

		return $res;
	}
}