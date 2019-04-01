<?php
namespace wfw\engine\lib\PHP\types;


/**
 *  Permet des opérations sur les chaines de caractère
 */
class PHPString {
	/**
	 *  Chaine de caractère
	 * @var string $_str
	 */
	protected $_str;

	/**
	 *  Contient la liste des explodes indéxés par délémiters
	 * @var array $_explodes
	 */
	protected $_explodes=[];

	/**
	 *  Résultat d'un splitAtUppercase
	 * @var string $_splitedAtUppercase
	 */
	protected $_splitedAtUppercase;

	/**
	 * PHPString constructor.
	 *
	 * @param string $str
	 */
	public function __construct(string $str){
		$this->_str = $str;
	}

	/**
	 *  Retourne la taille de la chaine courante
	 * @return int
	 */
	public function getLength():int{
		return strlen($this->_str);
	}

	/**
	 *  Crée une chaine permettant d'identifier un appel à explode
	 *
	 * @param string $delimiter Délimiteur
	 * @param int    $limit     Limite d'explosion
	 *
	 * @return string
	 */
	private function getExplodeId(string $delimiter, int $limit):string{
		return $delimiter.$limit;
	}

	/**
	 *  Explose la chaine de caractère courante suivant le motif et la limite passés en paramètre
	 *
	 * @param string $delimiter Délimiter pour l'explosion
	 * @param int    $limit     Limite d'explosion
	 *
	 * @return array
	 */
	public function explode(string $delimiter,int $limit = PHP_INT_MAX):array{
		$explodeId = $this->getExplodeId($delimiter,$limit);
		if(!isset($this->_explodes[$explodeId])){
			$this->_explodes[$explodeId]=explode($delimiter,$this->_str,$limit);
		}
		return $this->_explodes[$explodeId];
	}

	/**
	 *  Permet de savoir si la chaine courante se termine par la chaine passée en paramètre
	 *
	 * @param string $needle Chaine à tester
	 *
	 * @return bool
	 */
	public function endBy(string $needle):bool{
		return substr($this->_str,-strlen($needle))==$needle;
	}

	/**
	 *  Permet de savoir si la chaine courante commence par la chaine passée en paramètre
	 *
	 * @param string $needle Motif
	 *
	 * @return bool
	 */
	public function startBy(string $needle):bool{
		return strpos($this->_str,$needle)===0;
	}

	/**
	 *  Retourne true si la chaine courante contient au moins une occurence de $str
	 *
	 * @param string $str Chaine ou caractère dont on souhaite tester la présence
	 *
	 * @return bool
	 */
	public function contains(string $str):bool{
		$pos = strpos($this->_str,$str);
		if(is_numeric($pos)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 *   Remplace a première occurence de $needle dans la chaine courante par $replace
	 *
	 * @param  string $needle  Chaine à remplacer
	 * @param  string $replace Chaine de remplacement
	 *
	 * @return PHPString Chaine modifiée
	 */
	public function replaceFirst(string $needle,string $replace):PHPString{
		$pos = strpos($this->_str, $needle);
		if ($pos !== false) {
			return new static(substr_replace($this->_str, $replace, $pos, strlen($needle)));
		}else{
			return new static($this->_str);
		}
	}

	/**
	 *  Remplace toutes les occurences de $needle par $replace dans la chaine courante
	 * @param string $needle  Chaine à remplacer
	 * @param string $replace Chaine de remplacement
	 *
	 * @return PHPString
	 */
	public function replaceAll(string $needle, string $replace):PHPString{
		return new static(str_replace($needle,$replace,$this->_str));
	}

	/**
	 *   Remplace le dernière occurence de $needle dans la chaine courante par $replace
	 *
	 * @param  string $needle  Chaine à remplacer
	 * @param  string $replace Chaine de remplacement
	 *
	 * @return PHPString Chaine modifiée
	 */
	function replaceLast(string $needle,string $replace):PHPString{
		$pos = strrpos($this->_str,$needle);
		if ($pos !== false) {
			return new static(substr_replace($this->_str, $replace, $pos, strlen($needle)));
		}else{
			return new static($this->_str);
		}
	}

	/**
	 *  Insert les chaînes de $subs aux emplacements pos associés
	 *
	 * @param array $inserts Subsitutions : array(int pos=>string "to_insert" )
	 *
	 * @return PHPString
	 */
	public function insert(array $inserts):PHPString{
		$res="";
		foreach($this->split() as $k=>$char){
			if(isset($inserts[$k])){
				$res.= $inserts[$k];
			}
			$res.= $char;
		}
		return new static($res);
	}

	/**
	 *   Transforme la chaine de caractére courante en tableau de caractères (marche avec les accents en utf8)
	 * @param  integer   $split_length (optionnel défaut : 1) Si précisé, longueur max de chaque éléments
	 * @return array                   Tableau de caractére
	 */
	public function split(int $split_length = 1):array{
		if ($split_length == 1) {
			return preg_split("//u", $this->_str, -1, PREG_SPLIT_NO_EMPTY);
		} elseif ($split_length > 1) {
			$return_value = [];
			$string_length = mb_strlen($this->_str, "UTF-8");
			for ($i = 0; $i < $string_length; $i += $split_length) {
				$return_value[] = mb_substr($this->_str, $i, $split_length, "UTF-8");
			}
			return $return_value;
		} else {
			return [];
		}
	}

	/**
	 *  Supprime les accents dans une chaine de caractère
	 *
	 * @param string $charset (optionnel défaut : utf-8) Encodage à utiliser
	 *
	 * @return PHPString chaine sans accents
	 */
	public function removeAccents(string $charset='utf-8'):PHPString
	{
		$str = htmlentities($this->_str, ENT_NOQUOTES, $charset);

		$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

		return new static($str);
	}

	/**
	 * @param string $sub Substitution for all non-alphanum chars
	 * @param string $except
	 * @return PHPString
	 */
	public function stripNonAlphanum(string $sub="_",string $except=""):PHPString{
		return new static(preg_replace('/[^a-z0-9'.$except.']+/i', $sub,$this->_str));
	}

	/**
	 *  Explose une chaine de caractère sur ses majuscules
	 * @return array     Tableau contenant la chaine explosée
	 */
	public function splitAtUpperCase():array {
		if(!$this->_splitedAtUppercase){
			$this->_splitedAtUppercase=preg_split('/(?=[A-Z])/', $this->_str, -1, PREG_SPLIT_NO_EMPTY);
		}
		return $this->_splitedAtUppercase;
	}

	/**
	 *  Reproduit le fonctionnement de la fonction javascript encodeURIComponent
	 * @return PHPString chaine encodée
	 */
	function encodeURIComponent():PHPString {
		$revert = array('!'=>'%21', '*'=>'%2A', "'"=>"%27", '('=>'%28', ')'=>'%29');
		return new static(strtr(rawurlencode($this->_str), $revert));
	}

	/**
	 *  Chaine courante
	 * @return string
	 */
	public function __toString():string
	{
		return $this->_str;
	}

	/**
	 *  Crée une chaine de caractère unique de façon aléatoire
	 *
	 * @param null|integer $salt Grain de sel à ajouter
	 *
	 * @return PHPString
	 */
	public static function createUniq(?int $salt=null):PHPString{
		if(!$salt){
			$salt=rand(1,1000000);
		}else{
			$salt*=rand(1,$salt);
		}
		return new static(time()*(rand(1000001,2000000)-$salt));
	}

	/**
	 *  Génère une chaine de caractère aléatoire
	 *
	 * Composée de $length nombre de caractères dont les codes ASCII sont compris entre 32 et 125 inclus
	 *
	 * @param integer $length (optionnel défaut : 5) Longueur de la chaine de sortie
	 *
	 * @return PHPString Chaine aléatoire
	 */
	public static function createRandom(int $length=5):PHPString{
		$res="";
		for($i=0;$i<$length;$i++){
			$res.=chr(rand(32,125));
		}
		return new static($res);
	}
}