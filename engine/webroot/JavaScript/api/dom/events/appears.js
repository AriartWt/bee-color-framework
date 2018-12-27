wfw.define("dom/events/appears",function($elem,$appears,$disappears,$doc){
	if(typeof $appears !== "function") throw new Error("Arg 2 must be a function !");
	$doc = $doc || document;
	let $done = false;
	let $autoPlayLaunch = ()=>{
		let $scrollTop = window.pageYOffset || $doc.documentElement.scrollTop || $doc.body.scrollTop || 0;
		if($scrollTop + $doc.documentElement.clientHeight >= $elem.offsetTop){
			if(!$done){$appears(); $done = true;}
			else{
				if(typeof $disappears === "function"){
					$disappears();
					$done = false;
				}
			}
		}
	};
	$doc.addEventListener("scroll",$autoPlayLaunch);
	window.addEventListener("resize",$autoPlayLaunch);
	$autoPlayLaunch();
});