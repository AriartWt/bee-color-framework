<?php
namespace wfw\engine\core\lang;

use wfw\engine\lib\PHP\objects\StdClassOperator;

/**
 * Repository basé sur un stdClass.
 */
final class StrRepository implements IStrRepository {
	/** @var StdClassOperator $_repos */
	private $_repos;
	/** @var string $_baseKey */
	private $_baseKey;
	/** @var string $_replacementPattern */
	private $_replacementPattern;

	/**
	 * StrRepository constructor.
	 *
	 * @param \stdClass $repos Objet contenant l'ensemble des chaines.
	 * @param string    $replacementPattern (optionnel) Pattern de remplacement.
	 */
	public function __construct(\stdClass $repos,?string $replacementPattern = null) {
		$this->_repos = new StdClassOperator($repos);
		$this->_baseKey = "";
		$this->_replacementPattern = $replacementPattern ?? "[$]";
	}

	/**
	 * @param string $key Clé d'obtention d'une chaine
	 * @return string Chaine correspondante
	 */
	public function get(string $key): string {
		$res = $this->_repos->find($key);
		if(is_object($res)){
			throw new \InvalidArgumentException("The given path is not a key path !");
		}else{
			return (string) $res;
		}
	}

	/**
	 * @param null|string $basePath Chemin de base ajouté devant les clén pour une résolution
	 *                              relative. Null : resolution absolue.
	 */
	public function changeBaseKey(?string $basePath = null): void {
		$this->_baseKey = $basePath ?? "";
	}

	/**
	 * Obtient la chaine associée à $key et remplace un motif pré-établit par une occurence de
	 * $replace, dans l'ordre dans lequel elles sont spécifiées.
	 *
	 * @param string   $key         Clé
	 * @param string[] ...$replaces Remplacements
	 * @return string Chaine correspondante, dont les motifs de remplacement sont substitués par les
	 *                              termes fournis.
	 */
	public function getAndReplace(string $key, string ...$replaces): string {
		$str=$this->get($key);
		$i=0;
		$replacer = preg_quote($this->_replacementPattern);
		while(preg_match('/'.$replacer.'/',$str) && isset($replaces[$i])){
			$str = preg_replace('/'.$replacer.'/',$replaces[$i],$str,1);
			$i++;
		}
		return $str;
	}

	/**
	 * @param string $key Clé représentant à sous-ensemble de clés
	 * @return null|\stdClass
	 */
	public function getAll(string $key): ?\stdClass {
		try{
			return $this->_repos->find($key);
		}catch(\Exception $e){
			return null;
		}
	}
}