<?php
namespace wfw\engine\core\router;

use wfw\engine\core\action\Action;
use wfw\engine\core\action\IAction;
use wfw\engine\core\request\IRequest;

/**
 * Router de requêtes par défaut
 */
final class Router implements IRouter {
	/** @var array $_routes */
	private $_routes;
	/** @var array $_langRoutes */
	private $_langRoutes;
	/** @var array $_langs */
	private $_langs;
	/** @var null|string $_lang */
	private $_lang;
	/** @var string $_baseUrl */
	private $_baseUrl;
	/** @var string[][] $_cachedURLS */
	private $_cachedURLS;

	/**
	 * RequestRouter constructor.
	 *
	 * @param array       $connections Liste d'url à connecter.
	 * @param array       $langs       Liste de langues à reconnaître.
	 * @param null|string $lang        (optionnel) Langue pour la création des urls
	 * @param string $baseUrl (optionnel) defaut BASE_URL url de base vers le contenu
	 *                        statique
	 */
	public function __construct(
		array $connections = [],
		array $langs = [],
		?string $lang = null,
		?string $baseUrl = null
	){
		$this->_baseUrl = $baseUrl ?? '';
		$this->_langs = [];
		$this->_routes = [];
		$this->_langRoutes = [];
		$this->_cachedURLS = [];
		if(count(array_intersect(array_keys($connections),$langs)) > 0){
			foreach ($connections as $l => $conn){
				foreach($conn as $redir => $url){
					$this->addConnection($redir,$url,["lang"=>$l]);
				}
			}
		}else{
			foreach($connections as $redir => $url){
				$this->addConnection($redir,$url);
			}
		}

		foreach($langs as $l){
			$this->addLang($l);
		}
		$this->_lang = $lang;
	}

	/**
	 * Obtient une URL formattée en fonction du paramètrage du Router.
	 *
	 * @param string $url url réelle relative
	 * @return string URL finale absolue
	 */
	public function url(string $url = ''): string {
		trim($url,'/');
		$tmp = explode("/",$url);
		$tmpLang = null;
		if(in_array($tmp[0]??'',$this->_langs)) $tmpLang = array_shift($tmp);
		$url = implode("/",$tmp);

		if($tmpLang && isset($this->_langRoutes[$tmpLang])) $routes = array_merge(
			[$tmpLang => $this->_langRoutes[$tmpLang]],
			array_diff_key($this->_langRoutes,[$tmpLang])
		);
		else if($this->_lang && isset($this->_langRoutes[$this->_lang]))$routes = array_merge(
			[$tmpLang => $this->_langRoutes[$this->_lang]],
			array_diff_key($this->_langRoutes,[$this->_lang])
		);
		else if(!empty($this->_langRoutes)) $routes = $this->_langRoutes;
		else $routes = [$this->_routes];

		foreach($routes as $r){
			foreach($r as $v){
				if(preg_match($v['originreg'],$url,$match)){
					$url = $v['redir'];
					foreach($match as $k=>$w){
						$url = str_replace(":$k:",$w,$url);
					}
				}
			}
		}

		$preUrl = (empty($this->_baseUrl)?'':$this->_baseUrl.'/');

		if(!is_null($tmpLang)) $preUrl.=$tmpLang."/";
		else if(!is_null($this->_lang)) $preUrl.=$this->_lang."/";

		return preg_replace(
			"#//#","/",
			(strpos($preUrl,"/")!==0?'/':'').$preUrl.($url==='/'?'':$url)
		);
	}

	/**
	 * Obtient une URL résolue relativement au dossier public (webroot)
	 *
	 * @param string $url URL relative
	 * @return string
	 */
	public function webroot(string $url = ''): string {
		trim($url,'/');
		return preg_replace(
			"#//#","/",
			$this->_baseUrl.((strlen($url)>0)?"/$url":'')
		);
	}

	/**
	 * Construit une action à partir d'une requête
	 *
	 * @param IRequest $request Requête
	 * @return IAction Action résultante
	 */
	public function parse(IRequest $request): IAction {
		$cachekey = $request->getURI().json_encode($request->getAcceptedLanguages($this->_langs));
		if(isset($this->_cachedURLS[$cachekey])){
			$url = $this->_cachedURLS[$cachekey]["url"];
			$lang = $this->_cachedURLS[$cachekey]["lang"];
		}else{
			$url = trim($request->getURL(),'/');
			if(empty($url)){
				$url = '/';
			}
			$match = false;
			$tab = explode('/',$url);
			$lang = null;
			if(in_array($tab[0],$this->_langs)){
				$lang = array_shift($tab);
				$this->_lang = $lang;
			}
			$url = implode('/',$tab);

			if($this->_lang && isset($this->_langRoutes[$this->_lang]))
				//will search in the most probable urls first
				$routes = array_merge(
					[$this->_lang => $this->_langRoutes[$this->_lang]],
					array_diff_key($this->_langRoutes,[$this->_lang])
				);
			else if(!empty($this->_langRoutes)) $routes = $this->_langRoutes;
			else $routes = [$this->_routes];

			foreach($routes as $r){
				foreach($r as $v){
					if(preg_match($v['redirreg'],$url,$match)){
						$url = $v['origin'];
						/**
						 * @var  $k string
						 * @var  $v array
						 * @var  $match array
						 */
						foreach($match as $k=>$m){
							$url = str_replace(':'.$k.':',$m,$url);
						}
						break 2;
					}
				}
			}
			$this->_cachedURLS[$cachekey] = [
				"url" => $url,
				"lang" => $lang
			];
		}

		return new Action(
			$request,
			$url,
			$lang,
			$this->_langs
		);
	}

	/**
	 * @param string $lang Ajoute une langue au router afin qu'elle soit reconnue
	 */
	public function addLang(string $lang): void {
		$this->_langs[] = strtolower($lang);
	}

	/**
	 * Connecte deux URL.
	 *
	 * Router::connect('','posts/index');
	 * Router::connect('cockpit','cockpit/posts/index');
	 * Router::connect('blog/:slug-:id','posts/view/id:([0-9]+)/slug:([a-z0-9\-]+)');
	 * Router::connect('blog/*','posts/*');
	 *
	 * @param string $redir URL à connecter
	 * @param string $url   URL de connexion
	 * @param array  $opts (optionnal) depends of the implementation
	 */
	public function addConnection(string $redir, string $url, array $opts=[]): void {
		$r = [];
		$r['params'] = [];
		$r['url'] = $url;
		$r['opts'] = $opts;

		$r['originreg'] = preg_replace('/([a-z0-9]+):([^\/]+)/','${1}:(?P<${1}>${2})',$url);
		$r['originreg'] = str_replace('/*','(?P<args>/?.*)',$r['originreg']);
		$r['originreg'] = '/^'.str_replace('/','\/',$r['originreg']).'$/';
		$r['origin'] = preg_replace('/([a-z0-9]+):([^\/]+)/',':${1}:',$url);
		$r['origin'] = str_replace('/*',':args:',$r['origin']);

		$params = explode('/',$url);
		foreach($params as $k=>$v){
			if(strpos($v,':')){
				$p = explode(':',$v);
				$r['params'][$p[0]] = $p[1];
			}
		}

		$r['redirreg'] = $redir;
		$r['redirreg'] = str_replace('/*','(?P<args>/?.*)',$r['redirreg']);

		foreach($r['params'] as $k=>$v){
			$r['redirreg'] = str_replace(":$k","(?P<$k>$v)",$r['redirreg']);
		}
		$r['redirreg'] = '/^'.str_replace('/','\/',$r['redirreg']).'$/';

		$r['redir'] = preg_replace('/:([a-z0-9]+)/',':${1}:',$redir);
		$r['redir'] = str_replace('/*',':args:',$r['redir']);

		if(isset($opts['lang'])){
			if(!isset($this->_langRoutes[$opts['lang']])) $this->_langRoutes[$opts['lang']] = [];
			$this->_langRoutes[$opts['lang']][] = $r;
		}else $this->_routes[] = $r;
	}

	public function reset(): void {
		$this->_lang = null;
	}
}