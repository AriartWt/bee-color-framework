wfw.require(
	"api/dom/nodeHelper",
	"api/ui/fileExplorer",
	"api/dom/resizer"
);
wfw.init(()=>wfw.ui.lang.load("plugins/lteditor/medias",wfw.next));
wfw.ready(()=>{
	let $lstr = ($key,...$replaces)=>wfw.ui.lang.get("plugins/lteditor/medias/"+$key,...$replaces);
	wfw.define("ui/lteditor/plugins/medias",function(){
		let $exec = ($cmd,$val) => document.execCommand($cmd,false,$val);
		let $explorerWindow; let $inputFile; let $mediaWindow; let $activeMedia = null;
		let $close = ($explorer)=>{
			if($explorer) $explorer.parentNode.removeChild($explorer);
			else $mediaWindow.parentNode.removeChild($mediaWindow);
		};
		let $fe = new wfw.ui.fileExplorer({
			upload : {url:wfw.webroot+"uploader/uploadFile", paramName:{ file:'file',name:'name' }},
			delete : {url:wfw.webroot+"uploader/delete", paramName:{ paths:"paths" }},
			rename : {url:wfw.webroot+"uploader/rename", paramName:{ oldPaths:"oldPaths",newPaths:"newPaths" }},
			create : {url:wfw.webroot+"uploader/createPath", paramName:{ paths:"paths" }},
			list : {url:wfw.webroot+"uploader/list"}
		});
		$explorerWindow = wfw.dom.appendTo(wfw.dom.create("div",{className:"lteditor-window file-explorer"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:$lstr("CHOOSE_MEDIA")}),
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(wfw.dom.create("div",{on:{click:()=>$close($explorerWindow)}}),
						wfw.dom.create("span",{className:"close",innerHTML:'+'})
					)
				)
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
				$fe.html,
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.create("button",{innerHTML:$lstr("CHOOSE"),on:{click:()=>{
						let $selected = $fe.selected;
						if($selected.length>1)
							alert($lstr("WARN_ONLY_ONE_ALLOWED"));
						else if($selected.length===1){
							let $d = $fe.getData($selected[0]);
							if($d.type!=="file")
								alert($lstr("WARN_MUST_CHOOSE_ONE"));
							else{
								$inputFile.value = wfw.webroot+$d.path;
								$inputFile.setAttribute("data-media_type",
									$d.mime.split('/').shift()
								);
								let $mediaType = $inputFile.getAttribute("data-media_type");
								$mediaType = $mediaType==="image" ? 'img' :
									$mediaType==="video" ? 'vid' : 'aud';
								$mediaWindow.querySelector("#"+$mediaType).checked=true;
								$close($explorerWindow);
							}
						}else alert($lstr("WARN_MUST_SELECT_MEDIA"));
					}}}),
					wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{click:()=>{
						$close($explorerWindow);
					}}})
				)
			)
		);
		let $insertMedia = ($url,$media,$params)=>{
			let $baseAtt = ` src="${$url}" style="float:${$params.float}" `;
			if($media === "image"){
				$exec("insertHTML", `<img ${$baseAtt} alt="${$params.alt}">`);
			}else if($media === "video"){
				let $poster = $params.poster ? $params.poster : null;
				let $controls='controls '; let $autoplay=''; let $loop='';let $muted='';
				if($poster) $poster = `poster="${$poster}" `; else $poster = '';
				if('controls' in $params && !$params.controls) $controls='';
				if($params.autoplay) $autoplay = 'autoplay ';
				if($params.loop) $loop = 'loop ';
				if($params.muted) $muted = 'muted ';
				$baseAtt += [$poster,$controls,$autoplay,$loop,$muted].join('');
				$exec("insertHTML", `&#x200B;<video ${$baseAtt}></video>&#x200B;`);
			}else if($media === "audio"){
				let $controls='controls '; let $autoplay=''; let $loop='';
				if('controls' in $params && !$params.controls) $controls='';
				if($params.autoplay) $autoplay = 'autoplay ';
				if($params.loop) $loop = 'loop ';
				$baseAtt += [$controls,$autoplay,$loop].join('');
				$exec("insertHTML", `&#x200B;<audio ${$baseAtt} controls></audio>&#x200B;`);
			}else throw new Error(`Unsupported media type : ${$media}`);
		};
		let $displayMediaEditor = ($e,$btn,$editor)=>{
			let $input; let $alt;let $left; let $right; let $icon; let $type; let $active;
			if($activeMedia){
				$active = $activeMedia;
				$type = $activeMedia.tagName==="IMG"?'img':$activeMedia.tagName==="AUDIO"?'aud':"vid";
			}
			wfw.dom.appendTo($editor.html,
				$mediaWindow = wfw.dom.appendTo(wfw.dom.create("div",{className:"lteditor-window media-window"+($activeMedia?" media-edition-mode":"")}),
					wfw.dom.create("input",{type:"radio",name:"media",id:"img",className:"hidden",checked:$type==='img'}),
					wfw.dom.create("input",{type:"radio",name:"media",id:"vid",className:"hidden",checked:$type==='vid'}),
					wfw.dom.create("input",{type:"radio",name:"media",id:"aud",className:"hidden",checked:$type==='aud'}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"media-input"}),
						$input = $inputFile = wfw.dom.create("input", {
							placeholder:$lstr("CHOOSE_MEDIA_TO_IMPORT"),
							disable:true,
							data : { media_type : "image" },
							value : $activeMedia ? $activeMedia.src : ''
						}),
						$icon = wfw.dom.appendTo(
							wfw.dom.create("span",{className:"media-icon",on:{click:()=>{
								$fe.load(); $editor.html.appendChild($explorerWindow);
							}}}),
							wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/folder.svg")
						),
						wfw.dom.create("div",{
							className:"input-disabled",
							on:{click:($e)=>{$e.preventDefault();$icon.click();$input.classList.remove("lteditor-error");}}
						})
					),
					$alt = wfw.dom.create("input",
						{placeholder:"Alt",className:"alt",value:$activeMedia?$activeMedia.alt:''}
					),
					wfw.dom.create('h2',{innerHTML:$lstr('LAYOUT')}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"media-dispositions"}),
						wfw.dom.appendTo(wfw.dom.create("label",{className:"media-disposition",htmlFor:"none"}),
							wfw.dom.create('input',{id:"none",name:"disp",type:"radio",checked:$activeMedia ? $activeMedia.style.cssFloat!=="left" && $activeMedia.style.cssFloat!=="right" : true}),
							wfw.dom.create("p",{innerHTML:"------------"}),
							wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/media.svg"),
							wfw.dom.create("p",{innerHTML:"------------"})
						),
						wfw.dom.appendTo(wfw.dom.create("label",{className:"media-disposition",htmlFor:"floatleft"}),
							$left = wfw.dom.create('input',{id:"floatleft",name:"disp",type:"radio",checked:$activeMedia ? $activeMedia.style.cssFloat==="left" : false}),
							wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/media.svg"),
							wfw.dom.create("p",{innerHTML:"------------------------"})
						),
						wfw.dom.appendTo(wfw.dom.create("label",{className:"media-disposition",htmlFor:"floatright"}),
							$right = wfw.dom.create('input',{id:"floatright",name:"disp",type:"radio",checked:$activeMedia ? $activeMedia.style.cssFloat==="right" : false}),
							wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/media.svg"),
							wfw.dom.create("p",{innerHTML:"------------------------"})
						)
					),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
						wfw.dom.create("button",{innerHTML:$lstr($activeMedia ? "EDIT" : "CREATE"),
							on:{click:()=>{
							if($input.value.length===0) $input.classList.add("lteditor-error");
							else{
								if($active){
									$active.src = $input.value;
									$active.alt = $alt.value;
									if($left.checked) $active.style.float="left";
									else if($right.checked) $active.style.float="right";
									else $active.style.float="initial";
								}else{
									if($editor.lastSelection){
										window.getSelection().removeAllRanges();
										window.getSelection().addRange($editor.lastSelection.cloneRange());
									}else $editor.content.focus();
									$insertMedia($input.value,
										$input.getAttribute("data-media_type"),{
											alt : $alt.value,
											float : ($left.checked) ? "left" : ($right.checked) ? "right" : "initial"
									});
								}
								$close();
								if($active){
									window.getSelection().removeAllRanges();
									let $range = document.createRange();
									$range.setStartAfter($active);
									$range.collapse(true);
									window.getSelection().addRange($range);
								}
							}
						}}}),
						wfw.dom.create("button",{innerHTML:$lstr("CANCEL"),on:{click:()=>$close()}})
					)
				)
			)
		};
		let $initEventsOnMedia = ($media,$editor,$refNode)=>{
			if($media instanceof Node){
				let $resizer = wfw.dom.resizer($media,$editor.content,undefined,$refNode);
				$editor.html.addEventListener('click',$resizer.update);
				$editor.html.addEventListener('keyup',$resizer.hide);
				$resizer.onDisplay(()=>$activeMedia=$media);
				$resizer.onHide(()=>$activeMedia=null);
				$media.addEventListener('click',()=>{
					window.getSelection().removeAllRanges();
					let $range = document.createRange();
					$range.setStartAfter($media);
					$range.collapse(true);
					window.getSelection().addRange($range);
				});
			}
		};
		let $icon = wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/media.svg");
		let $init =($editor)=>{
			let $mutations = new MutationObserver(($records)=>{
				$records.forEach(($record)=>{
					let $mediaList = [];
					Array.from($record.addedNodes).forEach($n=>{
						if($n.tagName==="IMG" || $n.tagName==="VIDEO") $mediaList.push($n);
						else if(typeof $n.querySelectorAll === "function")
							Array.from($n.querySelectorAll("img,video")).forEach(
								$m=>$mediaList.push($m)
							);
					});
					$mediaList.forEach($node=>{
						$node.contentEditable=false;
						$initEventsOnMedia(
							$node, $editor,
							$node.parentNode ? $node.parentNode //Sorry
								: $record.nextSibling ? $record.nextSibling : $editor.content
						);
					});
				});
			});
			$mutations.observe($editor.content,{childList:true,subtree:true});
		};
		let $redefineError=()=>{throw new Error("Can't redefine medias' properties !")};
		Object.defineProperties(this,{
			action : { get : () => $displayMediaEditor, set : $redefineError },
			state : { get : () => () => !!$activeMedia, set : $redefineError },
			icon : { get : () => $icon, set : $redefineError },
			init : { get : () => $init, set : $redefineError },
			window : { get : () => $mediaWindow, set : $redefineError },
			params : { get : () => { return {title:$lstr('DESCRIPTION')} }, set : $redefineError }
		});
	});
},true);