wfw.define("dom/appendTo",function($parent,...$childs){
	$childs.forEach(($child)=>$parent.appendChild($child));
	return $parent;
});
wfw.define("dom/appendCascade",function(...$nodes){
	if($nodes.length < 2 ) throw new Error("At least 2 args required !");
	$nodes = $nodes.reverse(); let $current = $nodes.shift();
	$nodes.forEach(($node)=> {$node.appendChild($current); $current = $node;});
	return $current;
});
wfw.define("dom/insertAfter",function($node, $ref){
	if($ref.nextSibling) $ref.parentNode.insertBefore($node, $ref.nextSibling);
	else $ref.parentNode.appendChild($node);
});
wfw.define("dom/create",function($name, $p){
	let $res = document.createElement($name); $p = ($p && typeof $p === "object") ? $p : {};
	Object.keys($p).forEach(function($key){
		if($key === "data" && $name !== "object"){
			Object.keys($p.data).forEach($k => $res.setAttribute("data-"+$k,$p.data[$k]));
		}else if($key === "className" && Array.isArray($p.className)){
			$p.className.forEach($class => $res.classList.add($class));
		}else if($key === "style"){
			Object.keys($p.style).forEach($k => $res.style[$k]=$p.style[$k]);
		}else if($key === "on"){
			Object.keys($p.on).forEach($k => (Array.isArray($p.on[$k]))
				? $p.on[$k].forEach($j=>$res.addEventListener($k,$p.on[$k][$j]))
				: $res.addEventListener($k,$p.on[$k])
			);
		}else if($key === "options" && $name === "select"){
			Object.keys($p.options).forEach($k => $res.appendChild(wfw.dom.create("option",{
				value : $k, text : $p.options[$k]
			})));
		}else{
			$res[$key]=$p[$key];
		}
	});
	return $res;
});
wfw.define("dom/import/svg",function($url){
	return wfw.dom.create('object',{data:$url,type:"image/svg+xml",on:{load:($o)=>{
		let $svg = $o.target.contentDocument.querySelector("svg");
		$o.target.dispatchEvent(new CustomEvent("svgLoaded",{detail:$svg,bubbles:true}));
		$o.target.parentNode.insertBefore($svg,$o.target);
		$o.target.parentNode.removeChild($o.target);
	}}});
});