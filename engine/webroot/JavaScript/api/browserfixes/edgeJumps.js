wfw.define("browserfixes/edgeJumps",function($e){
	if(!wfw.browserfixes.edgeJumps.disabled){
		$e.preventDefault();window.scrollTo(0, window.pageYOffset - $e.wheelDelta);
	}
});
wfw.ready(()=>{
	if(document.documentMode || /Edge/.test(navigator.userAgent))
		document.body.addEventListener('mousewheel',wfw.browserfixes.edgeJumps)
},true);