wfw.define("dom/events/appears",function($elem,$appears,$disappears){
	if(typeof $appears !== "function") throw new Error("Arg 2 must be a function !");
	let $done = false;
	let $autoPlayLaunch = ()=>{
		let $scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		if($scrollTop + document.documentElement.clientHeight >= $elem.offsetTop){
			if(!$done){ $appears(); $done = true; }
			else{
				if(typeof $disappears === "function"){
					$disappears();
					$done = false;
				}
			}
		}
	};
	document.addEventListener("scroll",$autoPlayLaunch);
	window.addEventListener("resize",$autoPlayLaunch);
	$autoPlayLaunch();
});