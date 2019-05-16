<?php
namespace wfw\engine\core\view;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Implémentation de base pour une vue.
 */
abstract class View implements IView {
	/** @var string[] $_headers */
	private $_headers = [];
	/** @var callable[] $_postActions */
	private $_postActions = [];
	/** @var bool $_import */
	private $_import=true;
	/** @var string $_html */
	private $_html='';
	/** @var string $_viewPath */
	private $_viewPath;
	/** @var bool $_allowCache */
	private $_allowCache;

	/**
	 * View constructor.
	 *
	 * @param null|string $viewPath (optionnel) Chemin vers la vue.
	 * @param bool        $allowCache (optionnel) Si true, la mise en cache de la vue sera autorisée.
	 */
	public function __construct(?string $viewPath=null, bool $allowCache = false) {
		$this->_viewPath = $viewPath;
		$this->_allowCache = false;
	}

	/**
	 * @return string[] Liste des headers à déclarer pour la vue courante
	 */
	public function getHeaders(): array {
		return $this->_headers;
	}

	/**
	 * @param string ...$header Header à déclarer
	 */
	public function addHeader(string ...$header): void {
		$this->_headers = array_unique(array_merge($header,$this->_headers));
	}

	/**
	 * Rend la vue et retourne automatiquement le reultat de applyPostActions()
	 * @return string Retourne le rendu de la vue.
	 */
	public function render(): string {
		foreach($this->_headers as $header){
			header($header);
		}
		ob_start();
		if($this->_import){
			$path = $this->getViewPath();
			if(is_null($path)){
				$reflected = new \ReflectionClass(static::class);
				$path =dirname($reflected->getFileName())."/".$reflected->getShortName().".view.php";
			}
			require($path);
		}
		echo $this->_html;
		return $this->applyPostActions(ob_get_clean());
	}

	/**
	 * @return null|string Chemin d'accés absolu à la vue. Si null, la vue est résolue par défaut
	 *                     dans le même répertoire que celui de la classe : ClassName.view.php.
	 */
	protected function getViewPath():?string{
		return $this->_viewPath;
	}

	/**
	 * @param string $html HTML a concaténer
	 */
	protected function addHtml(string $html):void{
		$this->_html.=$html;
	}

	/**
	 * @return string HTML concaténé après l'import, à la fin du rendu.
	 */
	protected function getHTML():string{
		return $this->_html;
	}

	/**
	 * @param string $html Réécrit la totalité de l'HTML qui sera concaténé après l'import.
	 */
	protected function replaceHTML(string $html):void{
		$this->_html = $html;
	}

	/**
	 * Permet de désactiver l'import automatique d'un fichier lors du rendu.
	 */
	protected function disableImport():void{
		$this->_import = false;
	}

	/**
	 * Permet d'activer le cache complet de la vue.
	 */
	protected function enableCache():void{
		$this->_allowCache = true;
	}

	/**
	 * Permet d'effectuer des actions sur le buffer pour le modifier après le rendu.
	 *
	 * @param callable $action Le callable sous la forme :
	 *                         function(string $toReplace,string $buffer):string
	 * @return string Identifiant à inscrire dans le buffer à l'endroit du remplacement
	 */
	public function registerPostAction(callable $action): string {
		$uuid = (string) new UUID(UUID::V4);
		$this->_postActions[$uuid] = $action;
		return $uuid;
	}

	/**
	 * Applique les actions sur le rendu et retourne le résultat.
	 *
	 * @param string $buffer Rendu à modifier
	 * @return string
	 */
	public function applyPostActions(string $buffer): string {
		$res = $buffer;
		foreach($this->_postActions as $id=>$fn){
			$res = $fn($id,$res);
		}
		return $res;
	}

	/**
	 * @return array
	 */
	public function infos():array{
		return [];
	}

	/**
	 * Permet de savoir si la mise en cache complète de la vue est autorisée.
	 * @return bool Si true, autorise la mise en cache totale de la page.
	 */
	public function allowCache(): bool {
		return $this->_allowCache;
	}
}