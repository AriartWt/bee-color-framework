wfw.require(
	"api/dom/nodeHelper"
);
(function() {
let $loaded = {};
wfw.define("ui/wheelMenu", function ($params) {
	$params = $params || {}; let $current, $prev, $centerToRotate, $inst = this, $radius = 160;
	let $assoc = new WeakMap(), $html, $items = [], $css = wfw.url("Css/api/ui/wheelMenu.css");

	if( !('items' in $params) || !Array.isArray($params.items) || $params.items.length === 0)
		throw new Error("items have to be a non empty array !");
	if ('css' in $params) $css = $params.css;
	if(!$loaded[$css]){
		document.head.appendChild(wfw.dom.create("link", {rel: "stylesheet", href: $css}));
		$loaded[$css]=true;
	}
	if('radius' in $params) $radius = $params.radius;

	let $repositionItems = () => $items.forEach(($item, $i) => $positionItem($item,$i));
	let $positionItem = (item,position) => {
		let x = 0; let y = 0;
		let angle = -Math.PI / 2;
		let step = (2 * Math.PI) / ($items.length);
		for (let i = 0; i <= position; i++) {
			x = (($centerToRotate.offsetWidth/2)+$radius*Math.cos(angle)-(item.offsetWidth/2));
			y = (($centerToRotate.offsetHeight/2)+$radius*Math.sin(angle)-(item.offsetHeight/2));
			angle += step;
		}
		item.style.top = y + "px";
		item.style.left = x + "px";
	};
	let $updateCenterRotation = ()=>{
		let $step=(2*Math.PI)/($items.length); let $index = $items.indexOf($current);
		let $res=$index>=0 ? $index*$step : 0;
		$res=(180*$res)/Math.PI;
		let $precTrans=$centerToRotate.style.transform;
		$precTrans=parseFloat($precTrans.replace("rotate(","").replace("deg)","").trim());
		if(isNaN($precTrans)) $precTrans=0;

		$res=$res-$precTrans;
		if(Math.abs($precTrans)>180 && $res<0){
			if(Math.abs($res)>180) $res=360+$res;
		}else if($precTrans===0 && $res>180) $res=$res-360;

		if(Math.abs($res)>180) $res=-(360-$res);
		$res=$res%360;
		let $fn=function(step){
			$centerToRotate.style.transform="rotate("+(Math.round(($precTrans+(($res/50)*step))/10)*10)+"deg)";
			if(step<50) setTimeout(()=>$fn(step+1),1);
			else{
				if(Math.abs(parseFloat($centerToRotate.style.transform.replace("rotate(","").replace("deg)","").trim())) === 360){
					$centerToRotate.style.transform="rotate(0deg)";
				}
			}
		};
		$fn(1);
	};
	let $select = ($item)=>{
		$prev = $current; $current = $item;
		if($prev){
			$assoc.get($prev).classList.remove("center-item-active");
			$prev.classList.remove("wheel-item-active");
		}
		$assoc.get($current).classList.add('center-item-active');
		$current.classList.add("wheel-item-active");
		$updateCenterRotation();
	};
	let $createItem = ($params) => {
		let $p = $params.params || {};
		let $res =  wfw.dom.appendTo(wfw.dom.create("li",$p),
			typeof $params.icon === "string"
				? wfw.dom.create("img",{src : $params.icon})
				: $params.icon
		);
		$res.classList.add("wheel-item");
		$res.addEventListener('mouseenter',()=>$select($res));
		$res.addEventListener("click",()=>{
			if($params.btns.length===1) $assoc.get($res).querySelector("li").click();
		});
		return $res;
	};
	let $createItemMenu = ($params) => {
		if(!('btns' in $params) || !Array.isArray($params.btns) || $params.btns.length === 0)
			throw new Error("btns have to be a non empty array !");
		let $btns = $params.btns;
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"center-item"}),
			wfw.dom.appendTo(wfw.dom.create("ul",{className:"wheel-center-item-menu"}),
				...$btns.map($btn=>wfw.dom.create("li",$btn))
			)
		);
	};
	let $update = ()=>{
		$repositionItems();
		$updateCenterRotation();
	};
	$html = wfw.dom.appendTo(wfw.dom.create("div",{className:"wheel-menu"}),
		wfw.dom.appendTo(wfw.dom.create("nav",{className:"wheel-nav"}),
			wfw.dom.appendTo(wfw.dom.create("ul",{className:"center"}),
				...$params.items.map($p=>{
					$items.push($createItem($p));
					return $items[$items.length-1];
				})
			)
		),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"wheel-center"}),
			wfw.dom.appendTo( wfw.dom.create("div",{className:"center-img"}),
				$centerToRotate = wfw.dom.create("div",{className:"wheel-center-bg"})
			),
			wfw.dom.appendTo(
				wfw.dom.create("div",{className:"center-content"}),
				...$params.items.map(($p,$i)=>{
					let $res = $createItemMenu($p);
					$assoc.set($items[$i],$res);
					return $res;
				})
			)
		)
	);
	$repositionItems();
	$select($items[0]);

	let $redefineError = () => { throw new Error("Cann't redefine wheelMenu's properties !"); };
	Object.defineProperties($html,{
		wfw : { get : ()=> $inst, set : ()=> $redefineError }
	});
	Object.defineProperties(this, {
		html : {get: () => $html, set: () => $redefineError},
		update : {get : ()=> $update, set : ()=> $redefineError}
	});
});
})();