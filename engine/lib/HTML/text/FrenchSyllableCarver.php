<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/02/18
 * Time: 07:47
 */

namespace wfw\engine\lib\HTML\text;

use wfw\engine\lib\PHP\types\PHPString;

/**
 * Découpeur de syllables pour les textes en français.
 */
final class FrenchSyllableCarver implements ISyllableCarver
{
    private const VOWELS = ["a","e","i","o","u","y"];

    /**
     *   Permet de savoir si une lettre est une voyelle
     * @param  string    $char lettre à tester
     * @return boolean         True si la lettre est une voyelle
     */
    private function isVowel(string $char):bool{
        return in_array(strtolower((new PHPString($char))->removeAccents()),self::VOWELS);
    }

    /**
     * @param string $word Mot à découper
     * @return string[] syllables
     */
    public function carve(string $word): array
    {
        $res=array();

        $splitted= array_reverse((new PHPString($word))->split());

        $nextIsSyllabe=false;
        $tmpSyll=array();
        $prevLetter="";

        foreach($splitted as $k=>$letter){
            $tmpSyll[]=$letter;
            if(!$this->isVowel($letter) || ($nextIsSyllabe && $letter=="h" )){
                //on tiens compte des cas de h (ch, ph..) et gn
                if($nextIsSyllabe
                    && strtolower($letter)!=="h"
                    && (strtolower($letter)!=="n" && $prevLetter!=="g"))
                {
                    $res[]=implode('',array_reverse($tmpSyll));
                    $tmpSyll=array();
                    $nextIsSyllabe=false;
                }
            }else{
                $nextIsSyllabe=true;
            }
            $prevLetter=$letter;
        }
        if(count($tmpSyll)>0){
            $res[]=implode('',array_reverse($tmpSyll));
        }

        return array_reverse($res);
    }
}