wfw.require("api/dom/nodeHelper");
wfw.define("dom/resizer",function($elem,$workingContainer,$mode,$refNode){
	let $trHandle=wfw.dom.create("span",{className:"resize-handle tr"});
	let $tlHandle = wfw.dom.create("span",{className:"resize-handle tl"});
	let $blHandle = wfw.dom.create("span",{className:"resize-handle bl"});
	let $brHandle = wfw.dom.create("span",{className:"resize-handle br"});
	let $handles = [$trHandle,$tlHandle,$brHandle,$blHandle]; let $oposite = new WeakMap();
	$oposite.set($trHandle,$blHandle); $oposite.set($tlHandle,$brHandle);
	$oposite.set($brHandle,$tlHandle); $oposite.set($blHandle,$trHandle);
	let $op; let $opRect; let $realasing = false; let $handlesDisplayed=false;
	let $hideHandlers = []; let $resizeHandler = []; let $showHandlers = []; let $posUpdate = [];
	let $allPosUpdate = []; const $modes = {auto:true,constrained:true,unconstrained:true};
	$mode = (($mode in $modes) ? $mode : undefined) || "auto";

	let $mutations = new MutationObserver(($records)=>{
		$records.forEach($r=>$r.removedNodes.forEach($n=>{if($n===$elem){$hideHandles();}}))
	});
	$mutations.observe($refNode,{childList:true,subtree:true});

	let $updatePosition = ($handle)=>{
		let $ol = $elem.offsetLeft; let $ot = $elem.offsetTop;
		let $oh = $elem.offsetHeight; let $ow = $elem.offsetWidth;
		if(($ol + $ow) === 0) return;
		if($handle === $trHandle){
			$trHandle.style.top = ($ot-5)+"px"; $trHandle.style.left = ($ol+$ow-5)+"px";
		}else if($handle === $tlHandle){
			$tlHandle.style.top = ($ot-5)+"px"; $tlHandle.style.left = ($ol-5)+"px";
		}else if($handle === $blHandle){
			$blHandle.style.top = ($ot+$oh-5)+"px"; $blHandle.style.left = ($ol-5)+"px";
		}else if($handle === $brHandle){
			$brHandle.style.top = ($ot+$oh-5)+"px"; $brHandle.style.left = ($ol+$ow-5)+"px";
		}
		$posUpdate.forEach(($fn)=>$fn($handle));
	};
	let $updateAll = ()=>{
		$handles.forEach(($h)=>$updatePosition($h)); $allPosUpdate.forEach(($fn)=>$fn($handles));
	};
	$elem.addEventListener("load",()=>{ $updateAll() });
	let $displayHandles = ()=>{
		$handlesDisplayed = true;
		$workingContainer.querySelectorAll(".resize-handle").forEach(($e)=>{
			$e.parentNode.removeChild($e);
		});
		wfw.dom.appendTo($workingContainer,...$handles); $updateAll();
		$showHandlers.forEach($fn=>$fn($handles));
	};
	let $hideHandles = ()=>{
		$handlesDisplayed = false;
		$handles.forEach(($h)=>{ if($h.parentNode) $h.parentNode.removeChild($h) });
		$hideHandlers.forEach($fn=>$fn($handles));
	};
	let $moveEvent = ($e)=>{
		let $pX; let $pY;
		if($e.pageX || $e.pageY){
			$pX = $e.pageX; $pY = $e.pageY;
		}else if ($e.clientX || $e.clientY){
			$pX = $e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			$pY = $e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
		}
		let $offsetLeft = ($opRect.width/2)+$opRect.left;
		let $offsetTop = ($opRect.width/2)+$opRect.top;

		if($offsetLeft-$pX >= 0) $elem.style.width = ($offsetLeft-$pX)+"px";
		else $elem.style.width = ($pX-$offsetLeft)+"px";

		if(((!$e.shiftKey && $mode !== "constrained")
			|| $mode === "unconstrained") && $elem.tagName !== "VIDEO"
		){
			if($offsetTop-$pY >= 0) $elem.style.height = ($offsetTop-$pY)+"px";
			else $elem.style.height = ($pY-$offsetTop)+"px";
		}else $elem.style.height="initial";
		$resizeHandler.forEach($fn=>$fn($elem));
		$updateAll();
	};
	let $resetMoveEvent = ()=>{
		$realasing = true; setTimeout(()=>$realasing=false,0);
		document.removeEventListener("mousemove",$moveEvent);
		document.removeEventListener("mouseup",$resetMoveEvent);
	};
	$handles.forEach(($h)=>{
		$h.contentEditable=false; $h.draggable = false;
		$h.addEventListener("dragstart",($e)=>$e.preventDefault());
		$h.addEventListener("mousedown",($e)=>{
			$op=$oposite.get($e.target); $opRect = $op.getBoundingClientRect();
			document.addEventListener("mousemove",$moveEvent);
			document.addEventListener("mouseup",$resetMoveEvent);
		});
	});
	$elem.addEventListener("click",()=>$displayHandles());
	$elem.addEventListener("play",()=>$displayHandles());
	$elem.addEventListener("pause",()=>$displayHandles());
	$workingContainer.addEventListener("click",($e)=>{
		if(!$realasing && [$elem,...$handles].filter(($l)=>$l===$e.target).length===0){
			$hideHandles();
		}else $displayHandles();
	});
	let $onHide = ($fn) => $hideHandlers.push($fn);
	let $onResize = ($fn) => $resizeHandler.push($fn);
	let $onDisplay = ($fn) => $showHandlers.push($fn);
	let $onPosUpdate = ($fn) => $posUpdate.push($fn);
	let $onAllPosUpdate = ($fn) => $allPosUpdate.push($fn);
	let $redefineError = ()=>{throw new Error("Can't redefine resizer properties !")};
	let $res = {};
	Object.defineProperties($res,{
		update : { get : ()=>$updateAll, set : $redefineError},
		display : { get : ()=>$displayHandles, set : $hideHandles},
		hide : { get : ()=>$hideHandles, set : $redefineError},
		onHide : { get : ()=>$onHide, set : $redefineError},
		onDisplay : { get : ()=>$onDisplay, set : $redefineError},
		onResize : { get : ()=>$onResize, set : $redefineError},
		onHandlePosUpdate : { get : ()=>$onPosUpdate, set : $redefineError},
		onAllHandlesPosUpdate : {get : ()=>$onAllPosUpdate, set : $redefineError}
	});
	return $res;
});