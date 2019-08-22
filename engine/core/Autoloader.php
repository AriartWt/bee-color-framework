<?php 
namespace wfw;

/**
 *  Autoloader
 */
class Autoloader{
	/** @var array $_excludedPaths */
	protected $_excludedPaths;
	/** @var null|string $_baseDir */
	protected $_baseDir;

	/**
	 * Autoloader constructor.
	 *
	 * @param array       $excludedPaths
	 * @param null|string $baseDir
	 */
	public function __construct(array $excludedPaths=[],?string $baseDir=null) {
		$this->_excludedPaths = $excludedPaths;
		$this->_baseDir = $baseDir ?? dirname(__DIR__,2);
	}

	/**
	 *  Permet d'ajouter la fonction autoload au registre spl_autoload
	 *
	 * @param bool $removeAllPrevious Remove all previous autoloaders.
	 * @param bool $prepend           Prepend the current invocation in the spl_queue
	 */
	public function register(
		bool $removeAllPrevious = false,
		bool $prepend = false
	):void{
		if($removeAllPrevious) foreach(spl_autoload_functions() ?? [] as $fn)
			spl_autoload_unregister($fn);
		spl_autoload_register([$this,"autoload"],true,$prepend);
	}

	/**
	 * FUnction called to autoload a class
	 * @param string $className Class ta load
	 */
	public function autoload(string $className):void{
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
		$current = $this->_excludedPaths;
		if(count($this->_excludedPaths) > 0) foreach($expl as $part){
			if(isset($current[$part])){
				if(empty($current[$part]) || !is_array($current[$part])) return;
				else $current = $current[$part];
			}else break;
		}
		array_splice($expl,0,1);
		$file = "$this->_baseDir/".implode('/',$expl).".php";
		if(file_exists($file)) require_once($file);
	}
}

 