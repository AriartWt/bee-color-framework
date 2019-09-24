wfw.require(
	"api/dom/nodeHelper",
	"api/polyfills/string"
);
(function(){
let $loaded = {}; let $primitiveObject = function($data){this.data = $data};
wfw.define("dom/select",function($params){
	let $html; $params = $params || {}; let $dMap = new WeakMap(), $primitiveIndexer = {};
	let $selected = [], $sDisplay, $inst = this, $txtMap = new WeakMap(), $list, $allE = [];
	let $pMap = new WeakMap(), $eMap = new WeakMap(), $tnMap = new WeakMap(), $gMap = new WeakMap();
	let $placeholder = $params.placeholder ? $params.placeholder : null, $disableClose = false;
	let $groups = Array.isArray($params.groups) ? $params.groups : [];
	let $opts = Array.isArray($params.opts) ? $params.opts : [];
	let $css = $params.css ? $params.css : wfw.webroot+"Css/api/dom/select.css";
	let $on = {change:[]}, $isOpen = false, $searchField, $highlighted;
	let $sortFn = typeof $params.sortSearch === 'function' ? $params.sortSearch : ($a,$b)=>{
		if($a.pos<$b.pos) return -1;
		else if($a.pos===$b.pos) return (($a.value.length<$b.value.length)?-1:+1);
		else return 1;
	};
	if(!$loaded[$css]){
		document.head.appendChild(wfw.dom.create("link",{href : $css, rel : 'stylesheet'}));
		$loaded[$css]=true;
	}

	if(!$params.displayers) $params.displayers={};
	let $displayers = {
		value : ($txt)=>{ return wfw.dom.create("span",{ innerHTML: $txt ? $txt : '' }); },
		opt : ($p,$txtNode)=>{
			let $node = wfw.dom.create("span",{ innerHTML : $p.name ? $p.name : '' });
			$txtNode($node);
			return $node;
		},
		group : ($p,$txtNode)=>{
			let $node = wfw.dom.create("span",{ innerHTML : $p.name ? $p.name : '' });
			$txtNode($node);
			return $node;
		}
	};
	if(typeof $params.displayers.value === 'function') $displayers.value = $params.displayers.value;
	if(typeof $params.displayers.opt === 'function') $displayers.opt = $params.displayers.opt;
	if(typeof $params.displayers.group === 'function') $displayers.group = $params.displayers.group;
	let $rootElems = [];

	let $open=()=>{
		$isOpen=true; $html.classList.remove("close"); $html.classList.add("open");
		setTimeout(()=>{ if($isOpen) $searchField.focus(); },50);
	};
	let $close=()=>{
		if(!$disableClose){
			$isOpen=false; $html.classList.remove("open"); $html.classList.add("close");
			if($highlighted){ $highlighted.classList.remove('highlighted'); $highlighted = false; }
		}
	};
	let $click=($e)=>{
		$e.stopPropagation(); if($isOpen){ $close(); $e.preventDefault(); } else $open();
	};

	let $setSearchNode = ($e,$node) => {
		$tnMap.set($e,$node); $txtMap.set($e,$node.nodeValue ? $node.nodeValue : $node.innerHTML);
	};
	let $createOpt = ($p,$insertIndex)=>{
		let $opt = wfw.dom.create("div", {
			className:"select-opt",on:{mousedown:($e)=>{
				$e.stopPropagation(); $select($opt);
				if(!$params.multiSelect && !$params.disableCloseOnChange) setTimeout($close,60);
				else if($params.multiSelect && document.activeElement === $searchField){
					$disableClose = true; setTimeout(()=>$disableClose=false,70);
				}
			},mouseover:$highlight}
		});
		wfw.dom.appendTo($opt,
			$displayers.opt($p,($node)=>$setSearchNode($opt,$node))
		);
		if(!$txtMap.get($opt)){
			let $txtNode = $getTxtNode($opt);
			$txtMap.set($opt,$txtNode.nodeValue ? $txtNode.nodeValue : $txtNode.innerHTML);
		}
		if(Number.isInteger($insertIndex) && $insertIndex>=0 && $insertIndex < $allE.length)
			$allE.splice($insertIndex,0,$opt);
		else $allE.push($opt);
		$pMap.set($opt,$p);
		if($p.value) $setDataMaps($p.value,$opt);
		return $opt;
	};
	let $createGroup = ($p,$insertIndex)=>{
		let $groups = Array.isArray($p.groups) ? $p.groups : [];
		let $opts = Array.isArray($p.opts) ? $p.opts : [], $arr, $passIndex=null, $saveElemPos;
		let $group = wfw.dom.appendTo(wfw.dom.create("div",{className:"select-group",on:{
			mousedown:($e)=>{$e.stopPropagation();if($params.allowGroupSelection)$select($group);},
			mouseover:($e)=>{ if($params.allowGroupSelection) $highlight($e); }}}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"select-group-visual"}),
				$displayers.group($p,($node)=>$setSearchNode($p,$node))
			)
		);
		if(Number.isInteger($insertIndex) && $insertIndex>=0 && $insertIndex < $allE.length){
			$saveElemPos = $allE[$insertIndex];
			$allE.splice($insertIndex,0,$group); $passIndex = $insertIndex;
		} else $allE.push($group);
		$arr = $groups.map($g=>$createGroup($g,Number.isInteger($passIndex)
			? $passIndex++ : null
		)).concat(...$opts.map( $o=>$createOpt($o,Number.isInteger($passIndex)
			? $passIndex = $allE.indexOf($saveElemPos) : null
		)));
		if($arr.length > 0) wfw.dom.appendTo($group,...$arr);
		if(!$txtMap.get($group)){
			let $txtNode = $getTxtNode($group);
			$txtMap.set($group,$txtNode.nodeValue ? $txtNode.nodeValue : $txtNode.innerHTML);
		}
		$pMap.set($group,$p);
		$gMap.set($group,$arr);
		if($p.value) $setDataMaps($p.value,$group);
		return $group;
	};
	let $setDataMaps =($data,$elem)=>{
		if($dMap.has($data)){
			let $tmpD = $txtMap.get($elem);
			throw new Error( "Value duplication : the value of '"
				+$tmpD+"' is already owned by '"+$tmpD+"'"
			);
		}
		$eMap.set($elem,$data);
		if((typeof $data !== 'object') && !Array.isArray($data)){
			if(!($data in $primitiveIndexer)) $primitiveIndexer[$data] = new $primitiveObject($data);
			$data = $primitiveIndexer[$data];
		}
		$dMap.set($data,$elem);
	};

	let $select = (...$e)=>{
		if($e.length === 0) $selected = [];
		else $e.forEach($n=>{
			if(!$params.multiSelect) $selected = [];
			if($n.classList.contains("select-group") && $params.multiSelect && $params.selectGroupItems){
				$n.querySelectorAll(".select-opt").forEach($o=>{
					if($selected.indexOf($o<0)) $selected.push($o);
				});
			}else if($selected.indexOf($n)<0) $selected.push($n);
		});
		$updateDisplayedValue();
	};
	let $unselect = (...$e)=>{
		$e.forEach($n=>{ if($selected.indexOf($n)>=0) $selected.splice($selected.indexOf($n),1); });
		$updateDisplayedValue();
	};
	let $updateDisplayedValue = ()=>{
		$on.change.forEach($fn=>$fn($inst));
		$sDisplay.innerHTML = '';
		if($params.multiSelect && $selected.length>0) wfw.dom.appendTo($sDisplay,
			wfw.dom.appendTo(wfw.dom.create("div",{className:"select-multi-values"}),
				...$selected.map($s=>wfw.dom.appendTo(
					wfw.dom.create("div",{className:"select-multi-value"}),
					$displayers.value($txtMap.get($s),$eMap.get($s)),
					wfw.dom.appendTo(wfw.dom.create("div",
						{className:"unselect-value",on:{
							mousedown:($e)=>{$e.stopPropagation();$unselect($s);}
						}}),
						wfw.dom.create('span',{innerHTML:"+"})
					)
				))
			)
		);
		else if($selected.length === 0) wfw.dom.appendTo($sDisplay,
			wfw.dom.appendTo(wfw.dom.create("div",{className:"select-value select-placeholder"}),
				wfw.dom.create("span",{innerHTML : $placeholder})
			)
		);
		else wfw.dom.appendTo($sDisplay,
			wfw.dom.appendTo(wfw.dom.create("div",{className:"select-value"}),
				$displayers.value($txtMap.get($selected[0]),$eMap.get($selected[0]))
			)
		);
	};

	let $highlight = ($e,$elem,$up)=>{
		if($e) $e.stopPropagation();
		if($e && $highlighted === $e.currentTarget) return;
		if($highlighted) $highlighted.classList.remove('highlighted');
		$highlighted = $e ? $e.currentTarget : $elem;
		if($highlighted){
			$highlighted.classList.add('highlighted');
			if($e) return;
			if($up){
				if($list.scrollTop > $highlighted.offsetTop
					|| $list.offsetHeight + $list.scrollTop < $highlighted.offsetTop){
					$list.scrollTop=$highlighted.offsetTop-$list.offsetTop;
				}
			}else{
				if($highlighted.offsetTop+$highlighted.offsetHeight > $list.offsetTop+$list.offsetHeight){
					$list.scrollTop = (
						($highlighted.offsetTop+$highlighted.offsetHeight)-($list.offsetTop+$list.offsetHeight)
					);
				}else $list.scrollTop = 0;
			}
		}
	};
	let $highlightUp = ($elem)=>{
		let $displayed = Array.from($list.querySelectorAll(".select-group,.select-opt"));
		let $all = $allE.filter(($e)=>$displayed.indexOf($e)>=0);
		if(!$elem) $elem = $all[0];
		let $index = $all.indexOf($elem);
		if($index < 0) $index=0;
		else if($index === 0 ) $index = $all.length-1;
		else $index--;
		while($index >= 0){
			$elem = $all[$index];
			if($elem.classList.contains("select-group")){
				if($params.allowGroupSelection){$highlight(null,$elem,true); break;}
			} else { $highlight(null,$elem,true); break }
			$index--;
		}
	};
	let $highlightDown = ($elem)=>{
		let $displayed = Array.from($list.querySelectorAll(".select-group,.select-opt"));
		let $all = $allE.filter(($e)=>$displayed.indexOf($e)>=0);
		if(!$elem) $elem = $all[$all.length-1];
		let $index = $all.indexOf($elem);
		if($index < 0) $index = $all.length-1;
		else if($index === $all.length-1) $index = 0;
		else $index++;
		while($index<$all.length){
			$elem = $all[$index];
			if($elem.classList.contains("select-group")){
				if($params.allowGroupSelection){$highlight(null,$elem); break;}
			} else { $highlight(null,$elem); break }
			$index++;
		}
	};

	let $searchKeyUp = ($e)=>{
		if($e.key === 'Enter'){
			$highlighted.dispatchEvent(new MouseEvent("mousedown"));
			if(!$params.disableCloseOnChange && !$params.multiSelect) $close();
		} else if($e.key === 'ArrowUp') $highlightUp($highlighted);
		else if($e.key === 'ArrowDown') $highlightDown($highlighted);
		else $search($searchField.value);
	};
	let $getTxtNode = ($e) => $tnMap.has($e) ? $tnMap.get($e)
		: document.createTreeWalker($e,NodeFilter.SHOW_TEXT,null,false).nextNode();
	let $search = ($value)=>{
		$rootElems.forEach($e=>$list.appendChild($e));
		let $all = $allE.slice();
		$all.forEach($e =>{
			$getTxtNode($e).innerHTML = $txtMap.get($e);
			if($gMap.has($e)) $gMap.get($e).forEach($n=>$e.appendChild($n));
		});
		if($value.length > 0){
			let $found = [], $foundNodes = [];
			$all.forEach($e=>{
				let $txtNode = $getTxtNode($e), $str = $txtMap.get($e);
				if(!$str){ $e.parentNode.removeChild($e); return ; }
				let $i = $str.toLowerCase().stripDiatrics().indexOf(
					$value.toLowerCase().stripDiatrics()
				);
				if($i >= 0){
					$found.push({
						value : $str.substrDelimit($i,$value.length,{
							start:"<span class=\"select-search-highlight\">",end:"</span>"
						}),
						pos : $i, node : $e, txtNode : $txtNode
					});
					$foundNodes.push($e);
				}else if($e.parentNode && !$gMap.has($e)) $e.parentNode.removeChild($e);
			});
			$found.sort($sortFn);
			$found.forEach($f=>{
				$getTxtNode($f.node).innerHTML = $f.value;
				if($f.node.parentNode) $f.node.parentNode.appendChild($f.node);
			});
			Array.from($list.querySelectorAll(".select-group")).reverse().forEach($g=>{
				if($g.querySelectorAll(".select-group,.select-opt").length===0)
					$g.parentNode.removeChild($g);
			});
		}
	};

	window.addEventListener("resize",()=>{ if($isOpen) $close(); });
	window.addEventListener("mousedown",($e)=>{
		if($html !== $e.target && !$html.contains($e.target) && $isOpen){ $close(); }
	});

	let $getValue = () => $selected.map(($e)=>$eMap.get($e));
	let $setValue = (...$e)=>{
		let $toSelect = []; $selected = [];
		$e.forEach($n=>{
			let $d;
			if(typeof $n !== 'object' && !Array.isArray($n)) $d = $dMap.get($primitiveIndexer[$n]);
			else if($eMap.has($n)) $d = $n;
			else $d = $dMap.get($n);
			if(typeof $d === 'object') $toSelect.push($d);
		});
		$select(...$toSelect);
	};

	if($params.allowReset){
		let $reset = wfw.dom.create("div",{className:"select-opt select-reset",innerHTML:'&nbsp;',
			on:{mousedown:()=>$select(),mouseover:$highlight}}
		);
		$allE.push($reset); $rootElems = [$reset];
	}else $rootElems = [];
	$rootElems = $rootElems.concat(
		$groups.map($g=>$createGroup($g)).concat($opts.map($o=>$createOpt($o)))
	);

	$html = wfw.dom.appendTo(wfw.dom.create("div",{className:"advanced-select close",on:{
			mousedown:$click,focus:()=>{
				if(!$isOpen){ $open(); setTimeout(()=>$searchField.focus(),50); }
			}
		},tabIndex:0}),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"select-head"}),
			$sDisplay = wfw.dom.create("div",{className:"select-display"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"select-icon"}),
				wfw.dom.import.svg(
					$params.icon ? $params.icon : wfw.webroot+"Image/svg/icons/caret-down.svg"
				)
			)
		),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"select-body"}),
			$searchField = wfw.dom.create("input",{tabIndex:-1,className:"select-search",on:{
				keyup:($e)=>$searchKeyUp($e),mousedown:($e)=>$e.stopPropagation(),focus:()=>{
					if(!$isOpen) $open();
				},blur:()=>{if($isOpen) setTimeout($close,60);}
			}}),
			$list = wfw.dom.appendTo(wfw.dom.create("div",{className:"select-list"}),...$rootElems)
		)
	);
	$updateDisplayedValue();
	let $add = ($fn,$p,$to,$before)=>{
		if($to){
			let $toIndex;
			if($toIndex = $allE.indexOf($to) >= 0){
				if(typeof $before === 'boolean'){
					let $parent = $to.parentNode, $o;
					if($before){
						$parent.insertBefore($o = $fn($p,$toIndex),$to);
						if($parent === $list) $rootElems.splice($rootElems.indexOf($to),0,$o);
						else{ let $g = $gMap.get($parent); $g.splice($g.indexOf($to),0,$o); }
					}else{
						wfw.dom.insertAfter($o = $fn($p,$toIndex+1),$to);
						if($parent === $list) $rootElems.splice($rootElems.indexOf($to)+1,0,$o);
						else{ let $g = $gMap.get($parent); $g.splice($g.indexOf($to)+1,0,$o); }
					}
				}else{
					if($to.classList.contains('select-group')){
						let $g = $gMap.get($to); let $i = $allE.indexOf($before);
						if($i >= 0 && $before.parentNode === $to){
							let $o = $fn($p,$i);
							$g.splice($g.indexOf($before),0,$o); $to.insertBefore($o,$before);
						}else{
							let $o = $fn($p,$allE.indexOf($g[$g.length-1]));
							$g.push($o); $to.appendChild($o);
						}
					}else{
						let $i = $rootElems.indexOf($to), $o = $fn($p,$i);
						$list.insertBefore($o,$to); $rootElems.splice($i,0,$o);
					}
				}
				$updateDisplayedValue();
				$search($searchField.value);
			}else throw new Error("ref node not found !");
		}else{ let $o = $createOpt($p); $list.appendChild($o); $rootElems.push($o) }
	};
	let $remove = (...$e)=>{
		$e.forEach($n=>{
			let $tmp = $allE.indexOf($n),$tmpD = $eMap.get($n);
			if($tmp >= 0) $allE.splice($tmp,1);
			if($eMap.has($n)) $eMap.delete($n);
			if($tmpD) $dMap.delete($tmpD);
			if($tmpD instanceof $primitiveObject) delete $primitiveIndexer[$tmpD.data];
			if($txtMap.has($n)) $txtMap.delete($n);
			$tmp = $rootElems.indexOf($n);
			if($tmp >= 0) $rootElems.splice($tmp,1);
			if($pMap.has($n)) $pMap.delete($n);
			if($gMap.has($n)){
				$remove(...$gMap.get($n));
				$gMap.delete($n);
			}
			if($tnMap.has($n)) $tnMap.delete($n);
			$tmp = $selected.indexOf($n);
			if($tmp >= 0) $selected.splice($tmp,1);
			if($highlighted === $n) $highlighted = null;
			if($n.parentNode && $gMap.has($n.parentNode)){
				$tmp = $gMap.get($n.parentNode);
				$tmp.splice($tmp.indexOf($n),1);
			}
			if($n.parentNode) $n.parentNode.removeChild($n);
		});
		$updateDisplayedValue();
	};
	let $redefineError = ()=>{ throw new Error("Cann't redefine select's properties !"); };
	Object.defineProperties($html,{
		wfw : { get : ()=>$inst, set : ()=>$redefineError },
		value : { get : () => $getValue(), set : ($d) => $setValue(...$d) }
	});
	Object.defineProperties(this,{
		html : { get : () => $html, set : () => $redefineError },
		open : { get : () => $open, set : () => $redefineError },
		close : { get : () => $close, set : () => $redefineError },
		value : { get : () => $getValue(), set : ($d) => $setValue(...$d) },
		reset : { get : () => ()=> $select(), set : $redefineError },
		focus : { get : () => ()=>$searchField.focus(), set : ()=>$redefineError },
		remove : { get : () => $remove, set : $redefineError },
		removeAll : { get : () => () => $remove(...$rootElems), set : $redefineError },
		isOpen : { get : () => $isOpen, set : $redefineError },
		valueOf : { get : () => (...$e) => $e.map($n=>$eMap.get($n)) , set : $redefineError },
		onChange : { get : () => ($fn)=>$on.change.push($fn), set : $redefineError },
		addOpt : {get:()=>($p,$to,$before)=>$add($createOpt,$p,$to,$before),set:$redefineError},
		addGroup : {get:()=>($p,$to,$before)=>$add($createGroup,$p,$to,$before),set:$redefineError}
	});
});
})();