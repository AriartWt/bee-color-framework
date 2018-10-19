wfw.define("console",(function(){
	let $params = { debug : false, maxSize : 25 }; let $_logs = [];
	let $keys = {
		t:"margin-left:10px;", var:"color:purple", url:"color:red", nb:"color:blue",
		str:"color:black",  date:"color:orange", time:"color:tan", end:null,
		def:"padding-right:5px;padding-left:5px;background:silver;color:white;border-radius:2px",
		ok:"padding-right:5px;margin-left:5px;color:white;background:green;border-radius:2px",
		err:"padding-right:5px;margin-left:5px;color:white;background:red;border-radius:2px",
		warn:"padding-right:5px;margin-left:5px;color:white;background:orange;border-radius:2px"
	};
	wfw.ready(() => {
		let $p = wfw.defined("settings") ? wfw.settings.get("console") : null;
		if($p){ $params = $p; Object.assign($keys,$p.keys); }
	},true);

	let $redefineError = () => {throw new Error("Cannot redefine console's properties")};
	let $log = function(...$vars){
		let $argsToApply = [];
		$vars.forEach(($elem)=>{
			if(typeof $elem === "string"){
				let $splitted = $elem.split('%'); let $str = ''; let $styles = [];
				if($splitted.length === 1) $argsToApply.push($elem);
				else $splitted.forEach(($el)=>{
					if($el.length === 0) return null;
					let $spl = $el.split(' '); let $cmd = $spl.shift();
					$styles.push(($cmd in $keys) ? $keys[$cmd] : $keys.def);
					$str+="%c "+$spl.join(' ');
				});
				$argsToApply = $argsToApply.concat([$str].concat($styles));
			}else $argsToApply.push($elem);
		});
		$registerLogLine($argsToApply);
		if($params.debug) console.log.apply(console,$argsToApply);
	};
	let $registerLogLine = function($args){
		let $es = (new Error()).stack.replace(/^Error/,"Log line");
		$_logs.push({ date:Date.now(), data:$args, infos:{ call:$es.split("\n")[2], stack:$es } });
		if($_logs.length > $params.maxSize) $_logs.shift();
	};
	let $logs = () => { return JSON.parse(JSON.stringify($_logs)); };
	let $reset = () => $_logs = [];
	Object.defineProperties(this,{
		log : { get : () => $log, set : $redefineError},
		logs : { get : () => $logs, set : $redefineError},
		reset : { get : () => $reset, set : $redefineError}
	});
})());