<?php 
namespace wfw\engine\core\cache\errors;

/**
 *  Exception à lever si un problème de comaptibilité empêche la création d'un systeme de cache.
 */
class CacheSystemIncompatibility extends \Exception{
	/**
	 *  Constructeur
	 * @param string    $explain Explications
	 */
	public function __construct(string $explain){
		parent::__construct($explain);
	}
}

 