wfw.require(
	"api/network/wfwAPI",
	"api/dom/nodeHelper",
	"api/ui/notifications",
	"api/ui/lang",
	"api/ui/loaders/eclipse",
	"api/ui/fileExplorer"
);
wfw.init(()=>wfw.ui.lang.load("plugins/miel/medias",wfw.next));
wfw.define("plugins/miel/medias",function($params){
	let $lstr = ($key,...$replaces)=>wfw.ui.lang.get('plugins/miel/medias/'+$key,...$replaces);
	let $loadedSvg=0, $nodes = new WeakMap(), $fe, $explorerWindow, $active;
	let $docsCss = new WeakMap(), $doc = $params.doc || document, $div;
	let $alert = ($message)=>setTimeout(()=>alert($message),0);
	let $svg = {
		audio: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/audio.svg"),
		video: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/video.svg")
	};
	Object.keys($svg).forEach($k=>{$svg[$k].addEventListener("svgLoaded",($e)=>{
		if($e.detail.tagName!=="SVG") $svg[$k]=$e.detail;
	})});
	$doc.head.appendChild($div = wfw.dom.appendTo(wfw.dom.create('div',
		{style:{visibility:'hidden',position:"fixed",top:"-100%",left:"-100%"},on:{svgLoaded:()=>{
			$loadedSvg++; if($loadedSvg===2) $div.parentNode.removeChild($div);
		}}}),
		$svg.audio, $svg.video
	));
	let $getSvg =($key)=>{
		if($svg[$key].tagName==="OBJECT") return $svg[$key]; else return $svg[$key].cloneNode(true);
	};
	let $register = ($node,$params)=>{
		let $o = {params:$params,list:null}; $nodes.set($node,$o);
		let $doc = $o.doc = $node.ownerDocument, $panel;
		let $window = $o.window = wfw.dom.create('div',{className:"panel-window medias-window"});
		let $css = $params.css ? $params.css : wfw.url("Css/miel/medias.css");
		if(!$docsCss.has($doc)) $docsCss.set($doc,[]);
		if($docsCss.get($doc).indexOf($css) < 0) $docsCss.get($doc).push($css);
		$active = $node;
		$doc.head.appendChild(wfw.dom.create("link", {href:$css,rel:"stylesheet"}));
		$params.medias = $params.medias || {};
		$params.editable = $params.editable || {};
		['image','audio','video'].forEach($t=>{
			if(!($t in $params.medias)){
				if($t === 'image') $params.medias.image = true;
				else $params.medias[$t] = false;
			}
		});
		['title','description'].forEach($e=>{
			if(!($e in $params.editable)) $params.editable[$e] = false;
		});

		wfw.dom.appendTo($window,
			wfw.dom.create("div",{className:"dark-bg"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:$lstr("MANAGE")}),
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{
							click:()=>$window.parentNode.removeChild($window)}
						}),
						wfw.dom.create("span",{innerHTML:'+'})
					)
				)
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"panel"}),
					wfw.dom.appendTo(wfw.dom.create("div"), $createButtons($params.buttons||{},$window)),
					$panel = wfw.dom.appendTo(wfw.dom.create("div"), $o.list = $createList($node))
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.create("button",{innerHTML:$lstr('SAVE'),on:{click:()=>{
						$update(
							$node.getAttribute('data-miel_key'),
							Array.from($o.list.querySelectorAll(".item")).map($n=>$compileListNode($n)),
							$window
						);
						$window.parentNode.removeChild($window)
					}}}),
					wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{
						click:()=>{
							$window.parentNode.removeChild($window);
							$panel.removeChild($o.list);
							$panel.appendChild($o.list = $createList($node));
						}
					}})
				)
			)
		);
		$active = $node;
		$node.addEventListener("click",()=>$doc.body.appendChild($window));
	};
	let $displayLoader = ($message,$node)=>{
		let $loader = new wfw.ui.loaders.eclipse($message);
		let $shadowLoader = wfw.dom.appendTo(wfw.dom.create("div",{className:"medias-loader"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"container"}),$loader.html)
		);
		$node.appendChild($shadowLoader);
		return {
			loader : $loader,
			remove : ()=>{ $loader.delete(); $shadowLoader.parentNode.removeChild($shadowLoader); }
		};
	};
	let $compileListNode = ($item)=>{
		let $file = $item.querySelector(".file").innerHTML;
		let $title = $item.querySelector(".title");
		let $description = $item.querySelector(".description");
		let $cover = $item.querySelector(".cover");
		let $mime = $item.querySelector(".mime").innerHTML;
		return {
			file : $file,
			title : $title ? $title.innerHTML : null,
			description : $description ? $description.innerHTML : null,
			mime : $mime,
			cover : ($mime.match(/image/)) ? $file : ($cover ? $cover.src : ($mime.match(/audio/) ? "@audio" : "@video"))
		};
	};
	let $createList = ($node)=>{
		let $data = JSON.parse($node.getAttribute('data-miel-medias_data'));
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"items"}),...($data.map(($d,$i)=>{
			return $createItem($d,$i);
		})));
	};
	let $changePositions = ($el,$pos,$prev)=>{
		let $list = $el.parentNode; $prev--; $pos--;
		if($pos >= $list.childNodes.length){ $list.removeChild($el); $list.appendChild($el); }
		else{
			if($pos < 0) $pos = 0;
			if($prev < $pos) $pos++;
			let $node = $list.childNodes[$pos];
			$list.removeChild($el);
			$list.insertBefore($el,$node);
		}
		$updatePositions();
	};
	let $updatePositions = ()=>{
		Array.from($nodes.get($active).list.childNodes).forEach(
			($n,$i)=>$n.querySelector(".position>span").textContent=($i+1).toString()
		);
	};
	let $createItem = ($d,$i)=>{
		if(Number.isInteger($i)) $i++; else $i = $nodes.get($active).list.childNodes.length + 1;
		let $chk, $content, $cov, $ct, $item = wfw.dom.appendTo(wfw.dom.create("div",{className:"item"}),
			$chk = wfw.dom.create('input',{type:"checkbox",className:"hidden-input"}),
			wfw.dom.create("div",{className:"selected"}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:"cover-contener"}),
				$cov = $d.cover.match(/^@/)
					? $getSvg($d.cover.replace("@",""))
					: wfw.dom.create("img",{ src: wfw.webroot+$d.cover, className : "cover"})
			),
			$ct = wfw.dom.appendTo(wfw.dom.create("div",{className:"content"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"infos"}),
					wfw.dom.create("span",{className:"file",innerHTML:$d.file}),
					wfw.dom.create("span",{className:"file-name",innerHTML:$d.file.split('/').pop()}),
					wfw.dom.create("span",{className:"mime",innerHTML : $d.mime})
				)
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"position"}),
				wfw.dom.create("span",{textContent:$i,contentEditable:true,on:{
					paste : $e => $e.preventDefault(), keypress: $e=>{
						let $code = ($e.which) ? $e.which : $e.keyCode;
						if($e.key === "Enter"){
							$e.preventDefault(); let $el = $e.currentTarget;
							$changePositions( $item, Number.parseInt($el.textContent),
								Number.parseInt($el.getAttribute("data-value"))
							);
						}
						if($code > 31 && ($code < 48 || $code > 57))
							$e.preventDefault()
					},click : $e => $e.stopPropagation() ,blur:$e=>{
						$e.currentTarget.textContent = $e.currentTarget.getAttribute("data-value");
					},focus : $e => {
						$e.currentTarget.setAttribute("data-value",$e.currentTarget.textContent);
						window.requestAnimationFrame(()=>$doc.execCommand("selectAll",false,null));
					}
				}})
			)
		);
		$cov.className="cover";
		let $params = $nodes.get($active).params;
		if($params.editable.title || $params.editable.description){
			let $clickEvent = $e => $e.stopPropagation();
			let $pasteEvent = $e=> {
				$e.stopPropagation(); $e.preventDefault();
				$e.currentTarget.innerHTML =($e.clipboardData || window.clipboardData).getData('text/plain');
			};
			$content = wfw.dom.create("div");
			if($params.editable.title)
				$content.appendChild(wfw.dom.create("h1",{
					className:"title",data:{text:"(Titre)"},textContent:$d.title,contentEditable:true,
					on : {
						click:$clickEvent,paste:$pasteEvent,
						keydown:$e=>{if($e.key === "Enter") $e.preventDefault();
					}
				}}));
			if($params.editable.description)
				$content.appendChild(wfw.dom.create("div",{
					className:"description", data:{text:"(Description)"},
					textContent:$d.description, contentEditable:true,
					on : { click:$clickEvent,paste:$pasteEvent }
				}));
			else $content.classList.add("title-only");
			$ct.appendChild($content);
		}
		$chk.addEventListener('click',($e)=>{ $e.preventDefault(); $e.stopPropagation(); });
		$item.addEventListener('click',()=>{ $chk.checked = !$chk.checked;});
		return $item;
	};
	let $add = ($body)=>{ $fe.load(); $body.appendChild($explorerWindow); };
	let $delete = ($body)=>{
		let $rows = $body.querySelectorAll("input[type=\"checkbox\"]:checked"); let $box;
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		$body.appendChild($box=wfw.dom.appendTo(wfw.dom.create('div',{className:'media-remove'}),
			wfw.dom.create('p',{innerHTML:$lstr("CONFIRM_REMOVE")}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
				wfw.dom.create('button',{innerHTML:$lstr("BTN_REMOVE"),on:{click:()=>{
					$body.removeChild($box);
					$rows.forEach($r=>$r.parentNode.parentNode.removeChild($r.parentNode));
					$updatePositions();
				}}}),
				wfw.dom.create('button',{innerHTML:$lstr('CANCEL'),on:{click:()=>$body.removeChild($box)}})
			))
		);
	};
	let $createButtons = ($params,$body)=>{
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
			('add' in $params) ? $params.archive :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:()=>$add($body)}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/media.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr("BTN_ADD")})
				),
			('remove' in $params) ? $params.archive :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:()=>$delete($body)}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/trash.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr("BTN_REMOVE")})
				)
		);
	};
	let $update = ($k,$arr,$window)=>{
		let $loader = $displayLoader($lstr("WAIT_UPDATE"),$window.querySelector(".body"));
		wfw.network.wfwAPI(wfw.webroot+"miel/update",{
			type : "POST",
			postData : { miel_key : $k, miel_data : JSON.stringify($arr) },
			"000" : ()=>{ $window.ownerDocument.location.reload(); },
			error : ($res)=>{ $loader.remove(); $alert($lstr("ERROR")+"\n"+$res); }
		})
	};

	$fe = new wfw.ui.fileExplorer({
		upload : {url:wfw.webroot+"uploader/uploadFile", paramName:{ file:'file',name:'name' }},
		delete : {url:wfw.webroot+"uploader/delete", paramName:{ paths:"paths" }},
		rename : {url:wfw.webroot+"uploader/rename", paramName:{ oldPaths:"oldPaths",newPaths:"newPaths" }},
		create : {url:wfw.webroot+"uploader/createPath", paramName:{ paths:"paths" }},
		list : {url:wfw.webroot+"uploader/list"},
		doc : $doc
	});
	$explorerWindow = wfw.dom.appendTo(wfw.dom.create("div",{className:"panel-window medias-window"}),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
				wfw.dom.create("span",{innerHTML:$lstr('CHOOSE_MEDIAS')}),
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{click:()=>
						$explorerWindow.parentNode.removeChild($explorerWindow)
					}}),
					wfw.dom.create("span",{innerHTML:'+'})
				)
			)
		),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
			$fe.html,
			wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
				wfw.dom.create("button",{innerHTML:$lstr('CHOOSE'),on:{click:()=>{
					let $selected = Array.from($fe.selected);
					let $ds = $selected.map($d=>$fe.getData($d)), $files = [];
					$ds.forEach($d=>$files.push(...$extractFiles($d)));
					$files = $files.filter($d=>$filterFiles($d,$nodes.get($active).params));
					if($files.length>0){
						$files.forEach(($f)=>$nodes.get($active).list.appendChild($createItem({
							file : $f.path, mime : $f.mime, title : null, description : null,
							cover : $f.mime.match(/^image/) ? $f.path : "@"+$f.mime.split('/')[0]
						})));
						$explorerWindow.parentNode.removeChild($explorerWindow);
					}else alert($lstr(
						'WARN_MUST_SELECT_MEDIA',
						$acceptedMedias($nodes.get($active).params).join(', ')
					));
				}}}),
				wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{click:()=>{
					$explorerWindow.parentNode.removeChild($explorerWindow);
				}}})
			)
		)
	);
	let $extractFiles = ($data)=>{
		let $res = [];
		if($data.type==="dir") Object.keys($data.items).forEach($d=>$res.push(...$extractFiles($data.items[$d])));
		else $res.push($data);
		return $res;
	};
	let $filterFiles = ($d,$params)=>{
		let $res = true;
		['image','audio','video'].some($t=>{
			if(!$params.medias[$t] && $d.mime.match(new RegExp($t))) return $res = false;
		});
		return $res;
	};
	let $acceptedMedias = ($params)=>{
		let $res = [];
		['image','audio','video'].forEach($t=>{
			if($params.medias[$t]) $res.push($lstr($t.toUpperCase()))
		});
		return $res;
	};

	let $res = {};
	let $redefineError = ()=>{throw new Error("Can't redefine miel default plugin properties !");};
	Object.defineProperties($res,{
		register : { get : () => $register, set : $redefineError}
	});
	return $res;
});