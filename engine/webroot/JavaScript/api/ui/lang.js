wfw.require("api/network/wfwAPI");
wfw.define("ui/lang",(function($defSeparator){
	wfw.ready(()=>{
		if(wfw.defined("settings")) $defSeparator = wfw.settings.get("ui/lang/replacement_pattern");
	},true);
	let $strs = {}; let $loaded = {};
	let $redefineError = () => { throw new Error("Cannot redefine lang's properties") };
	let $replace = function($str,$replaces){
		let $i = 0; let $len = $replaces.length;
		while($str.match(/\[\$]/) && $i<$len){ $str = $str.replace(/\[\$]/,$replaces[$i]); $i++; }
		return $str;
	};
	let $get = function($path,$create){
		let $o = $strs;
		$path.split('/').forEach(($key)=>{
			if($o && $key in $o) $o = $o[$key];
			else if($create) $o = $o[$key] = {};
			else throw new Error($path+" not found (error on key '"+$key+"')");
		});
		return $o;
	};
	let $merge = function($data,$prec){
		let $o = ($prec)?$prec:$strs; let $d = $data;
		Object.keys($d).forEach(($k)=>{
			if(typeof $o === "object" && $k in $o) $merge($d[$k],$o[$k]); else $o[$k]=$d[$k];
		});
	};
	let $getAndReplace = function($path,...$replaces){
		if(typeof $path !== "string") throw new Error("arg 1 have to be a string path !");
		$replaces.forEach(($r,$index)=>{
			if(typeof $r !== "string") throw new Error("arg "+$index+" have to be a string !");
		});
		let $o = $get($path);
		if(typeof $o === 'string') return $replace($o,$replaces);
		else throw new Error($path+" is found but is not a valid key !");
	};
	let $load = function($path,$then){
		if(!($path in $loaded)) wfw.network.wfwAPI(wfw.webroot+"/lang/translationsRepository",{
			type : "post", postData : { lang_path : $path },
			'001' : ($d)=>{
				$merge(JSON.parse($d),$get($path,true));
				if(typeof $then === "function") $then();
			},
			error : ($msg,$c) => { throw new Error("Can't load lang pack "+$path+':'+$c+' '+$msg); }
		});
	};
	Object.defineProperties(this,{
		get : { get : () => $getAndReplace, set : $redefineError } ,
		load : { get : () => $load, set : $redefineError }
	});
	return this;
})(window.lang_replacement_pattern || '[$]'));