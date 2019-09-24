wfw.require(
	"api/dom/nodeHelper",
	"api/ui/lang"
);
wfw.init(()=>wfw.ui.lang.load("plugins/lteditor/advancedLink",wfw.next));
wfw.ready(()=>{
	let $lstr = ($key,...$repl)=>wfw.ui.lang.get("plugins/lteditor/advancedLink/"+$key,...$repl);
	wfw.define("ui/lteditor/plugins/advancedLink",function(){
		const $exec = ($cmd,$value = null)=>document.execCommand($cmd,false,$value);
		let $getLink = ($content)=>{
			let $anchorNode = document.getSelection().anchorNode;
			if($anchorNode){
				let $parent = $anchorNode.parentElement;
				if($parent.tagName === 'A') return $parent;
				while(($parent = $parent.parentNode) && $parent!==$content){
					if($parent.tagName === "A") return $parent;
				}
				return null;
			} else return null;
		};
		let $action = ($e,$btn,$editor)=>{
			let $window; let $desc; let $link; let $target; let $selection=document.getSelection();
			let $range = $selection.getRangeAt(0); let $linkValue=''; let $targetValue=null;
			let $descValue=wfw.dom.appendTo(wfw.dom.create('div'),$range.cloneContents()).innerHTML;
			let $a = $getLink($editor.html.querySelector(".lteditor-content"));
			if($editor.html.querySelector(".lteditor-window")) return;
			if($btn.classList.contains("lteditor-action-selected")){
				$targetValue = $a.getAttribute('target') === "_blank";
				$descValue = $a.innerHTML; $linkValue = $a.href;
			}
			wfw.dom.appendTo($editor.html,
				$window = wfw.dom.appendTo(wfw.dom.create('div',{className:"lteditor-window"}),
					$desc = (!$descValue)
						?wfw.dom.create('input',{placeholder:$lstr("LINK_DESC")})
						:wfw.dom.create('span',{style:{display:'none'}}),
					$link = wfw.dom.create('input',{placeholder:$lstr('LINK_ADDR'),value:$linkValue}),
					wfw.dom.appendTo(
						wfw.dom.create('label',{innerHTML:$lstr("OPEN_NEW_TAB")}),
						$target = wfw.dom.create('input',{type:'checkbox',checked:$targetValue})
					),
					wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
						wfw.dom.create('button',{innerHTML:$lstr(($a)?'EDIT':'CREATE'),on:{click:()=>{
							let $error = false;
							if($link.value.length===0){
								$link.classList.add("lteditor-error");
								$link.title=$lstr("WARN_LINK_REQUIRED");
								$error = true;
							}else{$link.classList.remove("lteditor-error");$link.title='';}
							if(!$descValue && $desc.value.length===0){
								$desc.classList.add("lteditor-error");
								$desc.title=$lstr("WARN_DESC_REQUIRED");
								$error = true;
							}else{$desc.classList.remove("lteditor-error");$desc.title='';}
							if($error) return;
							if($a){
								let $select = document.createRange();$select.selectNode($a);
								window.getSelection().removeAllRanges();
								window.getSelection().addRange($select);
							}else{
								let $select = window.getSelection();
								$select.removeAllRanges(); $select.addRange($range);
							}
							$exec('insertHTML',
								`<a href="${$link.value}" ${($target.checked)?'target="_blank"':''}`
								+`>${$desc.value||$descValue}</a>`
							);
							$window.parentNode.removeChild($window);
						}}}),
						wfw.dom.create('button',{
							innerHTML:$lstr("CANCEL"),
							on:{click:()=>$window.parentNode.removeChild($window)}
						}),
						($a) ? wfw.dom.create('button',{innerHTML:$lstr("REMOVE"),on:{click:()=>{
								let $select = document.createRange(); $select.selectNode($a);
								window.getSelection().removeAllRanges();
								window.getSelection().addRange($select);
								$select.deleteContents();
								$exec('insertHTML',$a.innerHTML);
								$window.parentNode.removeChild($window);
							}}})
							: wfw.dom.create('span',{style:{display:'none'}})
					)
				)
			)};
		let $state = ($e,$btn,$editor)=>$getLink($editor.html.querySelector('.lteditor-content'));
		let $icon = wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/link.svg");
		let $params = {title:$lstr("DESCRIPTION")};
		let $redefineError = () => {throw new Error("Can't redefine advancedLink's properties !")};
		Object.defineProperties(this,{
			action : { get : () => $action, set : $redefineError },
			state : { get : () => $state, set : $redefineError },
			icon : { get : () => $icon, set : $redefineError },
			params : { get : () => $params, set : $redefineError }
		});
	});
},true);