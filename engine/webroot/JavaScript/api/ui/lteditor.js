wfw.require(
	"api/dom/nodeHelper",
	"api/ui/lang"
);
(()=>{
wfw.init(()=> wfw.ui.lang.load("ui/lteditor",wfw.next));
let $lstr = ($key,...$replaces)=>wfw.ui.lang.get("ui/lteditor/"+$key,...$replaces);

let $requestedCss = {};
const $exec = ($cmd,$value = null)=> {document.execCommand($cmd,false,$value)};
const $queryCommandValue = ($cmd) => document.queryCommandValue($cmd);
const $queryCommandState = ($cmd) => document.queryCommandState($cmd);
const formatBlock = 'formatBlock';

wfw.define("ui/lteditor",function($name,$actions,$params){
	$params = $params || {}; let $content, $textarea, $editor = null, $head, $inst = this;
	let $fnOnChange = []; let $escaped = false; let $body; let $res = this; let $lastSel;
	const defParagraphSeparator = $params['defaultParagraphSeparator'] || 'div';
	let $css = $params.css || wfw.url("Css/api/ui/lteditor.css");
	if(!$params.disableAutoCss && !$requestedCss[$css]){
		$requestedCss[$css]=true;
		document.head.appendChild(wfw.dom.create('link',{rel:"stylesheet",href:$css}));
	}
	let $opts = {
		main : {className:"lteditor"},
		textarea : {className:"lteditor-hidden-input"},
		actions : {className:"lteditor-actions"}, action : {className:"lteditor-action"},
		selectedAction : "lteditor-action-selected",
		body : {className:"lteditor-body"}, content : {className:"lteditor-content"}
	};
	if("opts" in $params && typeof $params.opts === "object")
		Array.from($params.keys).forEach(($k)=>Object.assign($opts[$k],$params[$k]));
	$opts.content.contentEditable = true;

	let $change = ($e) => $fnOnChange.forEach($fn=>$fn($e));
	let $onChange = ($fn) => $fnOnChange.push($fn);
	$editor = wfw.dom.appendTo(wfw.dom.create("div",$opts.main),
		wfw.dom.create('style',{innerHTML:`.${$opts.actions.className}`
			+`{user-select:none;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;}`}),
		$textarea = wfw.dom.create("textarea",$opts.textarea),
		$head = wfw.dom.create("div",$opts.actions),
		$body = wfw.dom.appendTo(wfw.dom.create("div",$opts.body),
			$content = wfw.dom.create("div",$opts.content)
		)
	);
	$editor.addEventListener('mousedown',($e)=>{
		let $current = $e.target; let $disableAutoRefocus = false;
		while($current && $current !== $editor && !$disableAutoRefocus){
			if($current.classList.contains("lteditor-window")
				|| $current.classList.contains("lteditor-actions")
				|| $current.classList.contains("lteditor-content")
			) $disableAutoRefocus = true;
			$current = $current.parentNode;
		}
		if(!$disableAutoRefocus) $content.focus()
	});
	$body.addEventListener('click',($e)=>{$e.target===$body ? $focus() : undefined});
	$textarea.name = $name; $textarea.addEventListener("change",$e=>$change($e));
	$content.addEventListener("input",({target:{firstChild}})=>{
		if(firstChild && firstChild.nodeType === 3) $exec(formatBlock,`<${defParagraphSeparator}>`);
		else if ($content.innerHTML === '<br>') $content.innerHTML = '';
		$textarea.value = $content.innerHTML;
	});
	$content.addEventListener("paste",($e)=>{
		$e.preventDefault(); let text = '';
		if ($e.clipboardData || $e.originalEvent.clipboardData)
			text = ($e.originalEvent || $e).clipboardData.getData('text/plain');
		else if (window.clipboardData) text = window.clipboardData.getData('Text');
		if (document.queryCommandSupported('insertText')) $exec('insertText', text);
		else $exec('paste', text);
	});
	$content.addEventListener("blur",()=>{
		$escaped=false; let $tmp = window.getSelection().getRangeAt(0).cloneRange();
		let $parent = window.getSelection().anchorNode;
		if($parent===$content) $lastSel = $tmp;
		else while($parent=$parent.parentNode){if($parent===$content){ $lastSel = $tmp; break; }}
	});
	$content.addEventListener("keydown",$e=>{
		if($e.key === 'Tab' && !$escaped){
			$e.preventDefault();$exec('insertHTML', '<span style="white-space: pre;">&#009</span>');
		}else if($e.key === 'Escape'){
			$escaped = true; setTimeout(()=>$escaped = false, 1000);
		}else if ($e.key === 'Enter' && $queryCommandValue(formatBlock) === 'blockquote')
			setTimeout(() => $exec(formatBlock, `<${defParagraphSeparator}>`), 0);
	});
	let $mutations = new MutationObserver(($records,$observer)=>{
		for(let $i = 0; $i<$records.length ; $i++){
			for(let $j=0; $j<$records[$i].addedNodes.length; $j++){
				if($records[$i].addedNodes[$j] === $editor){
					const $focus = document.activeElement;
					$content.focus();
					$exec('defaultParagraphSeparator',defParagraphSeparator);
					$exec('styleWithCss',true);
					$exec('enableObjectResizing',false);
					$exec('enableInlineTableEditing',false);
					$focus.focus();
					$observer.disconnect();
					return undefined;
				}
			}
		}
	});
	$mutations.observe(document,{childList:true,subtree:true});

	let $createBtn = ($action,$name)=>{
		if(!('title' in $action)) $action.title=null;
		if('state' in $action)
			if(typeof $action.state !== "function")
				throw new Error(`Action.${$name}.state must be a function`);
		if(!('action' in $action)) throw new Error(`Action.${$name}.action must be a function !`);
		if(!('icon' in $action)) throw new Error(`Action.${$name}.icon must be defined !`);
		if(!('params' in $action)) $action.params={};
		if(!('init' in $action)) $action.init=()=>undefined;
		else if(typeof $action.init !== 'function')
			throw new Error(`Action.${$name}.init must be a function !`);
		let $btn = (typeof $action.icon === "string")
			? wfw.dom.create("span",Object.assign({},$opts.action,$action.params,{innerHTML:$action.icon}))
			: wfw.dom.appendTo(wfw.dom.create("span",Object.assign({},$opts.action,$action.params)),
				$action.icon);
		$btn.classList.add($name);
		if($action.state){
			let $handler=($e)=>{setTimeout(()=>
				$btn.classList[$action.state($e,$btn,$res)?'add':'remove']($opts.selectedAction)
			,0);};
			$content.addEventListener('keyup',$handler);
			$content.addEventListener('mouseup',$handler);
			$btn.addEventListener('click',$handler);
			$editor.addEventListener('mousemove',$handler);
		}
		$btn.addEventListener("click",($e)=>{$action.action($e,$btn,$res);$content.focus();});
		return $btn;
	};
	let $focus = (start=false)=>{
		$content.focus(); let range = document.createRange(); range.selectNodeContents($content);
		range.collapse(start);
		let sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
	};
	$actions = Object.assign({
		bold : {
			action : ()=>$exec('Bold'),
			state : ()=>$queryCommandState('Bold'),
			icon : '<b>G</b>',
			params:{title:$lstr("BOLD")}
		},
		italic : {
			action : ()=>$exec('italic'),
			state : ()=>$queryCommandState('italic'),
			icon : '<em>I</em>',
			params:{title:$lstr("ITALIC")}
		},
		underline : {
			action : ()=>$exec('underline'),
			state : ()=>$queryCommandState('underline'),
			icon : '<u>s</u>',
			params:{title:$lstr("UNDERLINE")}
		},
		strikeThrough : {
			action : ()=>$exec('strikeThrough'),
			state : ()=>$queryCommandState('strikeThrough'),
			icon : '<s>b</s>',
			params:{title:$lstr("STRIKE_THROUGH")}
		},
		olist : {
			action : ()=>$exec('insertOrderedList'),
			state : ()=>$queryCommandState('insertOrderedList'),
			icon : '<div><div>1 ---</div><div>2 ---</div><div>3 ---</div></div>',
			params:{title:$lstr("O_LIST")}
		},
		ulist : {
			action : ()=>$exec('insertUnorderedList'),
			state : ()=>$queryCommandState('insertUnorderedList'),
			icon : '<div><div>&#9679; ---</div><div>&#9679; ---</div><div>&#9679; ---</div></div>',
			params:{title:$lstr("U_LIST")}
		},
		justifyFull : {
			action : ()=>$exec('justifyFull'),
			state : ()=>$queryCommandState('justifyFull'),
			icon : '<div><div>------</div><div>------</div><div>------</div><div>------</div><div>------</div></div>',
			params:{title:$lstr("JUSTIFY_FULL")}
		},
		justifyLeft : {
			action : ()=>$exec('justifyLeft'),
			state : ()=>$queryCommandState('justifyLeft'),
			icon : '<div><div>------</div><div>----</div><div>------</div><div>----</div><div>------</div></div>',
			params:{title:$lstr("JUSTIFY_LEFT")}
		},
		justifyCenter : {
			action : ()=>$exec('justifyCenter'),
			state : ()=>$queryCommandState('justifyCenter'),
			icon : '<div><div>&nbsp;------&nbsp;</div><div>&nbsp;----&nbsp;</div><div>&nbsp;------&nbsp;</div><div>&nbsp;----&nbsp;</div><div>&nbsp;------&nbsp;</div></div>',
			params:{title:$lstr("JUSTIFY_CENTER")}
		},
		justifyRight : {
			action : ()=>$exec('justifyRight'),
			state : ()=>$queryCommandState('justifyRight'),
			icon : '<div><div>------</div><div>&nbsp;----</div><div>------</div><div>&nbsp;----</div><div>------</div></div>',
			params:{title:$lstr("JUSTIFY_RIGHT")}
		},
		indent : {
			action : ()=>$exec('indent'),
			icon : '<div><div>--------</div><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---</div><div>&nbsp;&#8680;&nbsp;&nbsp;---</div><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---</div><div>--------</div></div>',
			params:{title:$lstr("INDENT")}
		},
		outdent : {
			action : ()=>$exec('outdent'),
			icon : '<div><div>--------</div><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---</div><div>&nbsp;&#8678;&nbsp;&nbsp;---</div><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---</div><div>--------</div></div>',
			params:{title:$lstr("OUTDENT")}
		},
		titles : {
			action : ($e)=>{},
			icon : wfw.dom.appendTo(wfw.dom.create("div",{className:"heading",title:$lstr("INSERT_TITLE")}),
				wfw.dom.create("span",{innerHTML:"T"}),
				wfw.dom.appendTo(wfw.dom.create("div"),
					wfw.dom.create("h1",{innerHTML:$lstr("TITLE_N",'1'),on:{click:()=>$exec(formatBlock,'<h1>')}}),
					wfw.dom.create("h2",{innerHTML:$lstr("TITLE_N",'2'),on:{click:()=>$exec(formatBlock,'<h2>')}}),
					wfw.dom.create("h3",{innerHTML:$lstr("TITLE_N",'3'),on:{click:()=>$exec(formatBlock,'<h3>')}})
				)
			)
		},
		clean : {
			action : ()=>{ $exec("removeFormat"); $exec(formatBlock,'<div>'); },
			icon : wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/clean.svg"),
			params : {title : $lstr("CLEAN")}
		},
		link : {
			action : ()=>{const $url = window.prompt(); if($url)$exec('createLink',$url)},
			state : ($e,$btn,$editor)=>{
				let $content = $editor.editor.querySelector(".lteditor-content");
				let $anchorNode = document.getSelection().anchorNode;
				if($anchorNode){
					let $parent = $anchorNode.parentElement;
					if($parent.tagName === 'A') return $parent;
					while(($parent = $parent.parentNode) && $parent!==$content){
						if($parent.tagName==="A") return $parent;
					}
					return null;
				} else return null;
			},
			icon : wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/link.svg"),
			params : {title:$lstr("LINK")}
		}
	},$actions);
	Object.keys($actions).forEach(($key)=>{$head.appendChild($createBtn($actions[$key],$key))});

	let $addButton = ($name,$action,$before)=>{
		if($before)$head.insertBefore($createBtn($action,$name),$head.querySelector(`.${$before}`));
		else $head.appendChild($createBtn($action,$name));
	};
	let $getValue = ()=>{
		$textarea.value = $content.innerHTML;
		return $textarea.value;
	};
	let $setValue = ($v) => {$content.innerHTML=$v;$textarea.value=$v;};
	let $redefineError = () => {throw new Error("Can't redefine news properties !")};
	Object.defineProperties($editor,{
		wfw : { get : ()=> $inst, set : $redefineError }
	});
	Object.defineProperties($res,{
		value : { get : $getValue, set : ($v) => $setValue($v) },
		html : { get : () => $editor, set : $redefineError },
		onChange : { get : () => $onChange, set : $redefineError },
		content : { get : () => $content, set : $redefineError },
		options : { get : () => $opts, set : $redefineError },
		addButton : {get : () => $addButton, set : $redefineError },
		lastSelection : {get : ()=>$lastSel, set : $redefineError }
	});
	Object.keys($actions).forEach(($key)=>$actions[$key].init($res));
});
})();