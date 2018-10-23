<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/02/18
 * Time: 07:41
 */

namespace wfw\engine\core\router;

use wfw\engine\core\action\Action;
use wfw\engine\core\action\IAction;
use wfw\engine\core\request\IRequest;

/**
 * Router de requêtes par défaut
 */
final class Router implements IRouter
{
	/**
	 * @var array $_routes
	 */
	private $_routes;
	/**
	 * @var array $_langs
	 */
	private $_langs;
	/**
	 * @var null|string $_lang
	 */
	private $_lang;

	/**
	 * @var string $_baseUrl
	 */
	private $_baseUrl;

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
		?string $baseUrl = null)
	{
		$this->_baseUrl = $baseUrl ?? BASE_URL ?? '';
		$this->_langs = [];
		$this->_routes = [];
		foreach($connections as $redir => $url){
			$this->addConnection($redir,$url);
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
	public function url(string $url = ''): string
	{
		trim($url,'/');
		foreach($this->_routes as $v){
			if(preg_match($v['originreg'],$url,$match)){
				$url = $v['redir'];
				foreach($match as $k=>$w){
					$url = str_replace(":$k:",$w,$url);
				}
			}
		}

		$preUrl = (empty($this->_baseUrl)?'':$this->_baseUrl.'/');

		if(!is_null($this->_lang)){
			$preUrl.=$this->_lang;
		}
		return (strpos($preUrl,"/")!==0?'/':'').$preUrl.($url==='/'?'':$url);
	}

	/**
	 * Obtient une URL résolue relativement au dossier public (webroot)
	 *
	 * @param string $url URL relative
	 * @return string
	 */
	public function webroot(string $url = ''): string
	{
		trim($url,'/');
		return strlen($url) > 0 ? "$this->_baseUrl/$url" : '';
	}

	/**
	 * Construit une action à partir d'une requête
	 *
	 * @param IRequest $request Requête
	 * @return IAction Action résultante
	 */
	public function parse(IRequest $request): IAction
	{
		$url = trim($request->getURL(),'/');
		if(empty($url)){
			$url = '/';
		}
		$match = false;
		foreach($this->_routes as $v){
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
				break;
			}
		}

		$tab = explode('/',$url);
		$lang = null;
		if(in_array($tab[0],$this->_langs)){
			$lang = array_shift($tab);
		}
		return new Action(
			$request,
			implode('/',$tab),
			$lang,
			$this->_langs
		);
	}

	/**
	 * @param string $lang Ajoute une langue au router afin qu'elle soit reconnue
	 */
	public function addLang(string $lang): void
	{
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
	 */
	public function addConnection(string $redir, string $url): void
	{
		$r = [];
		$r['params'] = [];
		$r['url'] = $url;

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

		$this->_routes[] = $r;
	}
}