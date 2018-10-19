wfw.define("dom/events/clickndbl",function($elem,$click,$dbl,$customDelay){
	let $clicks = 0; let $timer = null; let $delay;
	if(wfw.defined("settings")) $delay = wfw.settings.get("dom/events/dblclick/delay");
	$delay = $customDelay ? $customDelay : ($delay) ? $delay : 200;
	$elem.addEventListener("click",function($e){ $clicks++;
		if($clicks === 1) $timer = setTimeout(()=>{ $click($e,$elem); $clicks = 0; },$delay);
		else{ clearTimeout($timer); $dbl($e,$elem); $clicks = 0; }
	});
	$elem.addEventListener("dblclick",($e)=>$e.preventDefault());
	return $elem;
});