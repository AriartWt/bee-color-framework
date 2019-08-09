wfw.require(
	'api/ui/adminPanel',
	'api/ui/lang',
	'api/network/wfwAPI'
);
(function() {
wfw.init(()=>wfw.ui.lang.load("plugins/adminPanel/default",wfw.next));
let $lstr = ($key,...$replaces)=>wfw.ui.lang.get("plugins/adminPanel/default/"+$key,...$replaces);

wfw.define("plugins/adminPanel/default", function ($params,$loadOrder,$hbTimeout) {
	let $packages = {}, $modules = {}, $ready = [], $inst=this, $loaded = false;
	$params = $params || {}; $loadOrder = Array.isArray($loadOrder) ? $loadOrder : [];
	$hbTimeout = Number.isInteger($hbTimeout) ? Math.abs($hbTimeout) : 0;
	let $find = function($pName){
		let $o = wfw;
		$pName.split("/").forEach(($part)=> $part in $o ? $o=$o[$part] : $o = null);
		return $o;
	};
	let $findAndInit = function($pName,$params,$inst){
		let $res = null;
		if(wfw.defined($pName)) $res = $find($pName);
		else if(wfw.defined("packages/"+$pName)) $res = $find("packages/"+$pName);
		else throw new Error("Unable to find a matching module for "+$pName);
		if(typeof $res === 'function') return new $res($params,$inst);
		else throw new Error("Found result isn't a constructor but "+typeof $res);
	};
	let $init = () => {
		Object.keys($params).forEach($k => {
			wfw.ui.adminPanel.add($k, {className: "panel panel-" + $k});
			Object.keys($params[$k]).forEach($sk => {
				let $mParams = $params[$k][$sk], $res;
				if($sk === "logout"){
					let $p = Object.keys($mParams).length === 0 ? $defBtnParams.logout : $mParams;
					let $defP = $defBtnParams.logout, $btn;
					wfw.ui.adminPanel.get($k).appendChild( $btn = wfw.ui.adminPanel.createLogout(
						$p.url ? $p.url : $defP.url,
						$p.title ? $p.title : $defP.title,
						$p.confirm ? $p.confirm : $defP.confirm,
						$p.icon ? $p.icon : $defP.icon,
						$p.ok ? $p.ok : $defP.ok,
						$p.cancel ? $p.cancel : $defP.cancel
					));
					$packages[$k + "/" + $sk] = $btn;
				}else{
					if(!('btn' in $mParams)) {
						if ($sk in $defBtnParams) $mParams.btn = $defBtnParams[$sk];
						else throw new Error("No btn field given for module " + $sk);
					}
					wfw.ui.adminPanel.get($k).appendChild($res = wfw.ui.adminPanel.createButton($sk,
						$mParams.btn.title,
						$mParams.btn.icon,
						$mParams.btn.panelTitle
					));
					$packages[$k + "/" + $sk] = $findAndInit($sk,$mParams.params || {},$inst);
					$modules[$sk] = $packages[$k + "/" + $sk];
					if($mParams.autoload && typeof $modules[$sk].load === 'function')
						$modules[$sk].load();
					let $body = $res.querySelector('.body');
					$body.parentNode.appendChild($packages[$k + "/" + $sk].html);
					$body.parentNode.removeChild($body);
					$packages[$k + "/" + $sk].html.classList.add("body");
				}
			});
		});
		let $inputs = Array.from(document.querySelectorAll(".main-panel-display"));
		$inputs.forEach(($i) => {
			$i.addEventListener("change", () => {
				let $m = $modules[$i.id.replace('-panel','')];
				if ($i.checked){
					if(typeof $m.show === 'function') $m.show();
					$inputs.filter(($e) => {
						return $i !== $e
					}).forEach(($e) =>{
						$e.checked = false;
						let $m = $modules[$e.id.replace('-panel','')];
						if(typeof $m.hide === 'function') $m.hide();
					});
				}else if(typeof $m.hide === 'function') $m.hide();
			})
		});
		$loadOrder.forEach($module =>{
			if($modules[$module] && typeof $modules[$module].load === 'function')
				$modules[$module].load($inst);
		});
		$ready.forEach($fn=>$fn());
		$loaded = true;
		if($hbTimeout > 0) setInterval(()=>wfw.network.wfwAPI(wfw.webroot+"general/heartBeat"),$hbTimeout);
	};
	let $defBtnParams = {
		users : {
			title: $lstr('USER_PANEL_HINT'),
			icon: wfw.webroot + 'Image/svg/icons/user-group.svg',
			panelTitle: $lstr('USER_PANEL_TITLE')
		},
		"modules/BeeColor/news" : {
			title: $lstr('NEWS_PANEL_HINT'),
			icon: wfw.webroot + 'Image/svg/icons/news-module.svg',
			panelTitle: $lstr('NEWS_PANEL_TITLE')
		},
		"modules/BeeColor/contact" : {
			title : $lstr('CONTACT_PANEL_HINT'),
			icon : wfw.webroot + 'Image/svg/icons/message.svg',
			panelTitle : $lstr('CONTACT_PANEL_TITLE')
		},
		logout : {
			title : $lstr('LOGOUT'),
			icon : wfw.webroot + 'Image/svg/icons/power-button-off.svg',
			confirm : $lstr('CONFIRM_LOGOUT'),
			ok : $lstr('YES'),
			cancel : $lstr('NO'),
			url : wfw.webroot+"users/logout?csrfToken="+window.csrfToken
		}
	};
	let $redefineError = () => {throw new Error("Can't redefine default adminPanel properties !");};
	Object.defineProperties(this,{
		loaded : { get : ()=>$loaded, set : $redefineError },
		onready : { get : ()=>($fn)=>$ready.push($fn), set : $redefineError },
		getModule : { get : ()=>($n)=>$modules[$n], set : $redefineError }
	});
	if (wfw.ui.adminPanel.loaded()) $init(); else wfw.ui.adminPanel.onready($init);
});
})();