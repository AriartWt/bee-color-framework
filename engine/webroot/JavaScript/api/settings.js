wfw.define("settings",(function($app_infos){
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
	let $redefineError = function(){ throw new Error("Cannot redefine settings properties !") };

	Object.defineProperties(this,{
		get : { get : ()=>$get, set : $redefineError},
		set : { get : ()=>$set, set : $redefineError},
		exists : { get : ()=>$exists, set : $redefineError}
	});
}(window.appInfos || {})));