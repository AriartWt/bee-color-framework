wfw.require("api/dom/nodeHelper");
wfw.define("ui/adminPanel",(function(){
	let $load = []; let $hide = []; let $show = []; let $iframe = null;let $cmd =  null;let $loaded;
	let $frameDoc = null;let $icon = null; let $panels = {}; let $css = null; let $frameLoad = [];
	let $inIFrame = ()=>{ try{ return window.self !== window.top; } catch(e){ return true; } };
	let $init = () => {
		$iframe = wfw.dom.create("iframe",{src:'about:blank',on:{load:$onframeload}});
		document.body.appendChild($iframe);
		$cmd =  wfw.dom.create("input",{type:"checkbox",id:"panel-command",on:{change:
			 () => {if($cmd.checked) $show.forEach(($fn)=>$fn()); else $hide.forEach(($fn)=>$fn());}
		}});
		$frameDoc = ($iframe.contentDocument || $iframe.contentWindow.document);
		$icon = wfw.dom.appendTo(
			wfw.dom.create('label',{className:"panel-main-button",htmlFor:"panel-command"}),
			wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/settings.svg")
		);
		Array.from(document.head.childNodes).filter(($e)=>{
			return typeof $e.getAttribute !== "function" || $e.getAttribute('data-keep') !== "true"
		}).forEach(($e) => $e.parentNode.removeChild($e));
		Array.from(document.body.childNodes).filter(($e)=>{ return $e!==$iframe; }).forEach(
			($e)=>$e.parentNode.removeChild($e)
		);
		document.head.appendChild($css = wfw.dom.create("link",
			{rel:"stylesheet",href:wfw.webroot+"Css/api/ui/adminPanel.css"}
		));
		document.body.insertBefore($cmd,$iframe);
		document.body.appendChild($icon);
		if(wfw.defined("browserfixes/edgeJumps")) wfw.browserfixes.edgeJumps.disabled = true;
		$load.forEach(($fn)=>$fn());
		$loaded = true;
	};
	let $onframeload = function(){
		$frameDoc = ($iframe.contentDocument || $iframe.contentWindow.document);
		if($iframe.getAttribute("data-loaded") !== "true"){
			$iframe.setAttribute("data-loaded","true");
			if($frameDoc.body.childNodes.length === 0){
				let $a = wfw.dom.create("a",{href:window.location.href});
				$frameDoc.body.appendChild($a); $a.click();
			}
		}else if($frameDoc.location.toString() !== "about:blank"){
			window.history.pushState("",$frameDoc.title,$frameDoc.location.toString());
		}
		document.title = $frameDoc.title;
		let $favicon = $frameDoc.head.querySelector("link[rel=\"shortcut icon\"]");
		if($favicon) document.head.appendChild(wfw.dom.create('link',{
			rel:$favicon.rel,type:$favicon.type, href:$favicon.href
		}));
		$iframe.contentWindow.addEventListener("popstate",()=>{
			window.history.pushState("",$frameDoc.title,$frameDoc.location.toString());
			document.title = $frameDoc.title;
		});
		Array.from($frameDoc.querySelectorAll("a")).forEach($a=>{
			if(!$a.href.match(new RegExp("^"+window.location.protocol+"\\/\\/"+window.location.host+".*"))
				&& $a.target!=="_blank" && !$a.href.match(new RegExp("^mailto:"))){
				$a.addEventListener("click",()=>{ window.location.href = $a.href; });
			}
		});
		$frameLoad.forEach(($fn)=>$fn());
	};
	let $redefineError = () => { throw new Error("Cannot redefine adminPanel's properties") };
	let $existsPanel = ($name)=>{ return $name in $panels; };
	let $getPanel = ($n)=>{
		if(!$existsPanel($n)) throw new Error("Unknown panel "+$n); return $panels[$n];
	};
	let $createPanel = ($name,$params)=>{
		if($existsPanel($name)) throw new Error("Panel "+$name+" already exists !");
		document.body.insertBefore($panels[$name]=wfw.dom.create("div",$params),$icon);
	};
	let $removePanel = ($name)=>{
		if(!$existsPanel($name)) throw new Error("Unknown panel "+$name);
		$panels[$name].parentNode.removeChild($panels[$name]); delete $panels[$name];
	};
	let $createMainPanel = ($name,$title)=>{
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"panel-window"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:$title})
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(
						wfw.dom.create("label",{className:"close",htmlFor:$name+'-panel'}),
						wfw.dom.create('span',{innerHTML:"+"})
					)
				)
			),
			wfw.dom.create("div",{className:"body"})
		);
	};
	let $createButton = ($name,$title,$svg,$panelTitle)=>{
		let $main; let $label;
		let $btn = wfw.dom.appendTo(
			wfw.dom.create("div",{className:"panel-item"}),
			wfw.dom.create("input",
				{type:"checkbox",className:"hidden-input main-panel-display",id:$name+"-panel"}),
			wfw.dom.appendTo(
				$label = wfw.dom.create("label",{className:$name+"-icon",htmlFor:$name+"-panel"}),
				wfw.dom.import.svg($svg),
				wfw.dom.create("span",{innerHTML:$title})
			),
			$main = wfw.dom.create("div",{className:"main-panel",on:{mousedown:($e)=>{
				if($e.currentTarget === $e.target) $label.click()
			}}})
		);
		if(typeof $panelTitle === "string") $main.appendChild($createMainPanel($name,$panelTitle));
		return $btn;
	};
	let $logout = ($url,$title,$dialog,$icon,$ok,$cancel)=>{
		return wfw.dom.appendTo(wfw.dom.create('a',{className:"logout panel-item",href:$url,
			on:{click:($e)=>{
				$e.preventDefault(); let $confirm = null;
				if($e.target.getAttribute("data-clicked") === "true") return ;
				else $e.target.setAttribute("data-clicked",'true');
				document.body.appendChild($confirm = wfw.dom.appendTo(
					wfw.dom.create('div',{className:"logout-dialog"}),
					wfw.dom.create('span',{innerHTML:$dialog}),
					wfw.dom.appendTo(wfw.dom.create('div',{className:"buttons"}),
						wfw.dom.create('a',{href:$url,innerHTML:$ok?$ok:"Oui"}),
						wfw.dom.create('a',{innerHTML:$cancel?$cancel:"Non",on:{click:()=>{
							$confirm.parentNode.removeChild($confirm);
							$e.target.removeAttribute("data-clicked");
						}}})
					)
				));
			}}}),
			wfw.dom.appendTo(wfw.dom.create("div"),
				wfw.dom.import.svg($icon?$icon:wfw.webroot+"/Image/svg/icons/power-button-off.svg"),
				wfw.dom.create("span",{innerHTML:$title})
			)
		);
	};
	wfw.ready(()=>{ if(!$inIFrame()){ $init(); return true; } });
	wfw.init(()=>{ if(!$inIFrame()){ wfw.next(); } });
	let $res={};
	Object.defineProperties($res,{
		get : { get : () => $getPanel, set : $redefineError},
		add : { get : () => $createPanel, set : $redefineError },
		css : { get : () => $css , set : $redefineError },
		icon : { get : () => $icon, set : $redefineError },
		remove : { get : () => $removePanel, set : $redefineError },
		exists : { get : () => $existsPanel, set : $redefineError },
		onshow : { get : () => ($fn) => $show.push($fn), set : $redefineError},
		onhide : { get : () => ($fn) => $hide.push($fn), set : $redefineError},
		onready : { get : () => ($fn) => $load.push($fn), set : $redefineError},
		onframeload : { get : () => ($fn)=>$frameLoad.push($fn) , set : $redefineError},
		framerefresh : { get : () => ()=>$frameDoc.location.reload() , set : $redefineError},
		frame : { get : ()=>($doc) => ($doc) ? $frameDoc : $iframe, set : $redefineError },
		hide : { get : () => ()=>($cmd.checked) ? $cmd.click() : undefined, set : $redefineError},
		show : { get : () => ()=>(!$cmd.checked) ? $cmd.click() : undefined, set : $redefineError},
		createMainPanel : { get : ()=> $createMainPanel, set : $redefineError },
		createButton : { get:()=>$createButton, set:$redefineError },
		createLogout : {get:()=>$logout, set:$redefineError},
		loaded : { get : ()=>()=>!!$loaded, set : $redefineError }
	});
	return $res;
})(),true);