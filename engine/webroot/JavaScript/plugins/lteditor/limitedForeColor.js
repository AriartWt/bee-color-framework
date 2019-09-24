wfw.require(
	"api/dom/nodeHelper"
);
wfw.init(()=>wfw.ui.lang.load("plugins/lteditor/limitedForeColor",wfw.next));
wfw.ready(()=>{
	let $lstr = ($key,...$rep)=>wfw.ui.lang.get("plugins/lteditor/limitedForeColor/"+$key,...$rep);
	wfw.define("ui/lteditor/plugins/limitedForeColor",function($colors,$default){
		const $exec = ($cmd,$value = null)=>document.execCommand($cmd,false,$value);
		let $lastClicked; const $queryCmdValue = ($cmd) => document.queryCommandValue($cmd);
		let $isEmptyChar = ($char)=>wfw.dom.create('div',{innerHTML:"&#x200B;"}).innerText===$char;
		let $click = ($e)=>{ $lastClicked = $e.currentTarget; };
		let $action = ($e,$btn)=>{
			if(!$lastClicked) $lastClicked = $btn.querySelector(".color-box:first-child");
			let $color = $lastClicked.style.backgroundColor;
			if($color.match(/^rgb\(.*\)$/))
				$color = "#"+$color.replace("rgb(",'').replace(')','').split(',').map(($e)=>{
					let $r=Number.parseInt($e).toString(16);
					return ($r.length===1)?`0${$r}`:$r
				}).join('');
			if($lastClicked.getAttribute("data-default")){
				if(window.getSelection().isCollapsed){
					const $sel = window.getSelection();
					const $last = $sel.getRangeAt(0).cloneRange();
					if($last.startOffset>=0){
						let $r = $last.cloneRange();
						$r.setStart($last.startContainer,$last.startOffset-1);
						$sel.removeAllRanges(); $sel.addRange($r);
						if(!$isEmptyChar($sel.toString())){
							$sel.removeAllRanges();
							if(/Edge/.test(navigator.userAgent)){
								$last.setStartAfter($last.startContainer.parentNode);
								$last.collapse(true);
							}
							$sel.addRange($last);
							$exec('insertHTML',"&#x200B;");
						}
					}
				}
				$exec('removeFormat','foreColor');
			}
			else $exec('foreColor',$color);
			return true;
		};
		let $state = ($e,$btn,$editor)=>{
			if(document.activeElement === $editor.html.querySelector('.lteditor-content')){
				$btn.querySelector('.colors > span').style.backgroundColor=$queryCmdValue("foreColor");
			}
		};
		let $init = ($btn,$editor)=>{
			$editor.content.addEventListener("keydown",($e)=>{
				if($e.keyCode === 8 && window.getSelection().isCollapsed){
					const $sel = window.getSelection();
					const $last = $sel.getRangeAt(0).cloneRange();
					if($last.startOffset>=0){
						let $r = $last.cloneRange();
						$r.setStart($last.startContainer,$last.startOffset-1);
						$sel.removeAllRanges(); $sel.addRange($r);
						if($isEmptyChar($sel.toString())){
							$exec('insertHTML','');
						}
					}
				}
			});
		};
		let $icon = wfw.dom.appendTo(
			wfw.dom.create("div",{className:"colors",title:$lstr("WRITING_COLOR")}),
			wfw.dom.create("span",{style:{backgroundColor:$default}}),
			wfw.dom.appendTo(wfw.dom.create("div"),
				wfw.dom.create("span", {
					className:"color-box",title:$lstr("DEFAULT_COLOR"), on:{click:$click},
					style:{backgroundColor:$default}, data:{default:true}
				}),
				...$colors.map(($c)=>wfw.dom.create("span",{className:"color-box",
					style:{backgroundColor:$c},on:{click:$click}}
				))
			)
		);
		let $redefineError=()=>{throw new Error("Can't redefine limitedForeColor's properties !")};
		Object.defineProperties(this,{
			action : { get : () => $action, set : $redefineError },
			state : { get : () => $state, set : $redefineError },
			icon : { get : () => $icon, set : $redefineError },
			init : { get : () => $init, set : $redefineError }
		});
	});
},true);