<?php 
namespace wfw;

/**
 *  Autoloader
 */
class Autoloader{
	/**
	 *  Permet d'ajouter la fonction autoload au registre spl_autoload
	 *
	 * @param bool $removeAllPrevious Remove all previous autoloaders.
	 */
	public static function register(bool $removeAllPrevious = false):void{
		if($removeAllPrevious) foreach(spl_autoload_functions() ?? [] as $fn)
			spl_autoload_unregister($fn);
		spl_autoload_register([__CLASS__,"autoload"]);
	}

	/**
	 * FUnction called to autoload a class
	 * @param string $className Class ta load
	 */
	public static function autoload(string $className):void{
		if(strpos($className,__NAMESPACE__."\\")!==0){
			if(file_exists(__NAMESPACE__."\\site\\lib\\$className")){
				$className = __NAMESPACE__."\\site\\lib\\$className";
			}else if(file_exists(__NAMESPACE__."\\modules\\$className")){
				$className = __NAMESPACE__."\\modules\\$className";
			}else{
				$className = __NAMESPACE__."\\engine\\lib\\$className";
			}
		}
		$expl=explode("\\",$className);
		array_splice($expl,0,1);//on enlève le premier qui est le namespace général
		$file = dirname(__DIR__,2).'/'.implode('/',$expl).".php";
		if(file_exists($file)) require_once($file);
	}
}

 