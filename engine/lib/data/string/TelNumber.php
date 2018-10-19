<?php 

namespace wfw\engine\lib\data\string;

/**
 * Permet des opérations sur les numéros de téléphones.
 */
final class TelNumber{

    /**
     *  Uniformise le numéro de téléphone passé en entrée afin de le faire correspondre au format
     * suivant : +[code pays][espace](0)[numéro]
     *
     * @param  string $number Numéro à standardiser
     * @param  string $defaultCountry
     * @return string         Numéro standardisé
     * @throws \Exception
     */
	public static function standardize($number,$defaultCountry){
		$indicateur=false;
		if(preg_match("/^\+/",$number)||preg_match("/^00/",$number)){
			$indicateur=explode(' ',$number)[0];
			$numero=str_replace($indicateur." ","",$number);
		}else{
			$numero=$number;
		}
		if(!$indicateur){
			$indicateur=$defaultCountry;
		}else if(preg_match("/^00/",$indicateur)){
			$indicateur=preg_replace("/^00/","+",$indicateur);
		}
		$numero=str_replace(array(" ",".","-"),"",$numero);
		if(preg_match("/^0/",$number)){
			$numero=preg_replace("/^0(.*)/","(0)$1",$numero);
		}else if(!preg_match("/^\(0\)/",$numero)){
			$numero="(0)".$numero;
		}
		if(strlen($numero)==12){
			return $indicateur." ".$numero;
		}else{
			throw new \Exception("Unexpected tel number length. 12 was expected but ".strlen($numero)."given. Input number: \"".$number."\". Parsed number:\"".$numero."\" Country Code : ".$indicateur);
		}
	}

	/**
	 *  Retourne un numéro formaté (voir TelNumber::standardize) sous une forme lisible dépendant des configurations
	 * 		  Exemple : defaultFormat="international"; separator="." => +33 03.12.45.78.96
	 * 		            defaultFormat="national"; separator="." => 03.12.45.78.96
	 * @param  string $number Numéro de téléphone d'entrée
	 * @param string $country Pays
	 * @param string $numberFormat Format : internationnal nationnal ou mixed
	 * @param string $separator Séparateur entre les groupements de numéros
	 * @return string         Numéro de téléphone formaté prêt à être affiché. 
	 */
	public static function format($number,$country,$numberFormat,$separator){
		if(!$number){
			return null;
		}
		$indic=false;
		if(preg_match("/^\+/",$number) || preg_match("/^00/",$number)){
			$indic=explode(" ",$number)[0];
			$num=explode(" ",$number)[1];
		}else{
			$num=$number;
		}
		
		$num=preg_replace("/(([0-9]|\([0]\)){2})/","$1".$separator,$num);
		$num=substr($num,0,-1);
		
		if($numberFormat=="international"){
			if($indic){
				return $indic." ".$num;
			}else{
				return $num;
			}
		}else if($numberFormat=="national"){
			return preg_replace("/^\(0\)/","0",$num);
		}else if($numberFormat=="mixed"){
			if($indic!==$country){
				return $indic." ".$num;
			}else{
				return preg_replace("/^\(0\)/","0",$num);
			}
		}else{
			throw new \Exception("Unknown number format \"".$numberFormat."\"");
		}
	}
}

