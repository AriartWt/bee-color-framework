let WFW = function($webroot,$app_infos){
	let $requiredLibs = [], $loadingLibs = [], $loadedLibs = [], $inited = false;
	let $fnsOnReady = [], $laterPush = [], $toProcess = 0, $init = [];
	$webroot = $webroot.charAt($webroot.length-1) === '/' ? $webroot : $webroot+'/';
	let $loaded = function(){ $fnsOnReady.concat($laterPush).some( ($fn) =>{ return $fn() })};
	let $formatUrl = function($lib){ return $url("JavaScript/"+$lib+".js"); };
	let $redefineError = function(){ throw new Error("Cannot redefine wfw's properties !"); };
	let $addRequired = function($lib){
		if($loadedLibs.indexOf($lib)<0 && $loadingLibs.indexOf($lib)<0) $requiredLibs.push($lib);
	};
	let $createNamespace = function($namespace){
		if(typeof $namespace === "string" && $namespace !== ''){
			let $parts = $namespace.split("/"), $obj = wfw;
			for(let $i = 0; $i < $parts.length-1; $i++){
				if(!($parts[$i] in $obj)){
					let $o = {};
					Object.defineProperty($obj,$parts[$i],{set : $redefineError, get : () => $o });
					$obj = $o;
				}else $obj = $obj[$parts[$i]];
			}
			return { namespace : $obj, name : $parts[$parts.length-1] };
		}else{throw new Error("The namespace have to be a string path.");}
	};
	let $loadRequired = function(){
		$requiredLibs = $requiredLibs.filter((function(){
			let $seen = {};
			return function($element){ return !($element in $seen) && ($seen[$element]=1); }
		})());
		$requiredLibs.slice().forEach(function($lib){
			let $script = document.createElement("script");
			$script.setAttribute("src",$lib);
			$script.setAttribute("type","text/javascript");
			$toProcess++;

			$script.addEventListener("load",function(){
				$toProcess--;
				$loadedLibs.push($lib);
				$loadingLibs.splice($loadingLibs.indexOf($lib),1);
				$script.parentNode.removeChild($script);

				if($loadingLibs.length === 0) {
					if($requiredLibs.length > 0) $loadRequired();
					else if($toProcess === 0 ) $nextInit();
				}
			});
			$script.addEventListener("error",function(){
				$toProcess--; $script.parentNode.removeChild($script);
				throw new Error("Unable to load "+$lib);
			});
			$loadedLibs.push($lib);
			$requiredLibs.splice($requiredLibs.indexOf($lib),1);
			document.body.appendChild($script);
		});
		if($requiredLibs.length === 0 && $toProcess === 0) $nextInit();
	};
	window.addEventListener("load",$loadRequired);

	let $onload = function($fn,$first){ ($first) ? $fnsOnReady.push($fn) : $laterPush.push($fn); };
	let $require = function(...$libs){ $libs.forEach(($elem,$index)=>{
		if(typeof $elem === "string")
			$addRequired($elem.match(/^@.*/)?$elem.replace('@',''):$formatUrl($elem));
		else throw new Error("Require : arg "+$index+" is not a valid lib name !");
	}); };
	let $define = function($namespace,$o,$ovrd){
		let $res = $createNamespace($namespace);
		Object.defineProperty($res.namespace,$res.name,{ get : () => $o,
			set : $redefineError, configurable : $ovrd});
	};
	let $defined = function($path){
		let $o = wfw; let $find = true;
		$path.split("/").forEach(($part)=> $part in $o ? $o=$o[$part] : $find=false);
		return $find;
	};
	let $asyncInit = ($fn)=>{ $init.push($fn); };
	let $nextInit = ()=>{
		if($inited) throw new Error("wfw have already been fully initialized !");
		if($init.length > 0) $init.shift()(); else{ $loaded(); $inited=true }
	};
	let $url = ($path,$cache)=>{ return $webroot+$path+(!$cache?$get('app/cache_burst'):''); };

	let $params=JSON.parse($app_infos);
	let $getRef = function($path,$forSet){
		let $o = $params; let $find = true;
		let $paths = (typeof $path === "string") ? $path.split('/') : [];
		let $last = ($forSet !== null)?$paths.pop():null;
		$paths.forEach( ($part) => $find && $part in $o ? $o = $o[$part] : $find = false);
		if($forSet !== null) return {name : $last, o : $o};
		return $find ? $o : null;
	};
	let $get = function($path){
		let $res = $getRef($path,null);
		return ($res !== null) ? JSON.parse(JSON.stringify($res)) : null;
	};
	let $set = function($path,$value){
		let $res = $getRef($path,true);
		if($res !== null) $res.o[$res.name] = $value;
	};
	let $exists = function($path){ return $getRef($path,null) !== null; };
	let $redefineSettingsError = function(){ throw new Error("Cannot redefine settings properties !") };
	let $settings={};
	Object.defineProperties($settings,{
		get : { get : ()=>$get, set : $redefineSettingsError},
		set : { get : ()=>$set, set : $redefineSettingsError},
		exists : { get : ()=>$exists, set : $redefineSettingsError}
	});

	Object.defineProperties(this,{
		ready : { get : () => $onload, set : $redefineError },
		require : { get : () => $require, set : $redefineError },
		define : { get : () => $define, set : $redefineError },
		defined : { get : () => $defined, set : $redefineError },
		webroot : { get : () => $webroot, set : $redefineError },
		init : { get : () => $asyncInit, set : $redefineError },
		next : { get : () => $nextInit, set : $redefineError },
		url : { get : () => $url, set : $redefineError },
		settings : { get : () => $settings, set : $redefineError }
	});
};
let wfw = new WFW(window.webroot ? window.webroot : '/',window.appInfos || {});