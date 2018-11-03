wfw.require(
	"api/dom/nodeHelper",
	"api/network/wfwAPI",
	"api/ui/lang",
	"api/ui/loaders/eclipse",
	"api/dom/events/clickndbl"
);

(function(){
wfw.init(()=>wfw.ui.lang.load("ui/fileExplorer",wfw.next));
let $lstr = ($key,...$replaces)=>wfw.ui.lang.get("ui/fileExplorer/"+$key,...$replaces);

wfw.define("ui/fileExplorer",function($params){
	let $upload, $delete, $rename, $list, $create, $explorer, $displayed, $inst = this;
	let $data, $loadedSvg=0, $div, $body, $pathHelp, $files, $filesForm, $qSize, $qTxt;
	let $svg = {
		dir: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/folder.svg"),
		back: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/come-back.svg"),
		audio: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/audio.svg"),
		video: wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/video.svg")
	};
	Object.keys($svg).forEach($k=>{$svg[$k].addEventListener("svgLoaded",($e)=>{
		if($e.detail.tagName!=="SVG") $svg[$k]=$e.detail;
	})});
	document.body.appendChild($div = wfw.dom.appendTo(wfw.dom.create('div',
		{style:{visibility:'hidden',position:"fixed",top:"-100%",left:"-100%"},on:{svgLoaded:()=>{
			$loadedSvg++; if($loadedSvg===4) $div.parentNode.removeChild($div);
		}}}),
		$svg.dir, $svg.back, $svg.audio, $svg.video
	));
	let $getSvg =($key)=>{
		if($svg[$key].tagName==="OBJECT") return $svg[$key]; else return $svg[$key].cloneNode(true);
	};

	let $css = $params.css ? $params.css : wfw.webroot+"Css/api/ui/fileExplorer.css";
	let $doc = $params.doc ? $params.doc : document;
	if(!$doc.head.querySelector("link[href=\""+$css+"\"]")){
		$doc.head.appendChild(wfw.dom.create("link",{href:$css,rel:"stylesheet"}));
	}

	if('list' in $params) $list = { url : $params.list.url };
	else throw new Error("$params.list have to be defined !");
	if("upload" in $params){
		$upload = { url : $params.upload.url, paramName : {file:'file',name:'name'} };
		if(typeof $params.upload.paramName === "object")
			['file','name'].forEach(($k)=> $upload.paramName[$k]=$params.upload.paramName[$k] );
	}
	if("delete" in $params){
		$delete = { url : $params.delete.url, paramName : {paths:'paths'} };
		if(typeof $params.delete.paramName === "object")
			$delete.paramName.paths = $params.delete.paramName.paths;
	}
	if("rename" in $params){
		$rename = { url : $params.rename.url, paramName:{oldPaths:'oldPaths',newPaths:'newPaths'}};
		if(typeof $params.upload.paramName === "object")
			['newPaths','oldPaths'].forEach(($k)=>$rename.paramName[$k]=$params.rename.paramName[$k]);
	}
	if("create" in $params){
		$create = { url : $params.create.url, paramName : {paths:'paths'} };
		if(typeof $params.create.paramName === "object")
			$create.paramName.paths = $params.create.paramName.paths;
	}
	let $getData = ($path) => {
		let $o = $data; let $p = $path.replace(/^\//,'').split('/'); let $len = $p.length;
		while(!($p[0] in $o) && $p.length>0){ $p.shift(); }
		$p.filter(($f)=>$f.length>0).forEach($f=>{
			if(typeof $o[$f] === "object") $o = $o[$f];
			else if(typeof $o.items[$f] === "object") $o=$o.items[$f];
			else throw new Error(`Invalid path : '${$path}' doesn't leads to a valide directory`);
		});
		if($len>1 && $p.length===0) throw new Error("Invalid path "+$path);
		return $o;
	};
	let $getDataByNode = ($node) => { return $getData($node.getAttribute("data-fs-path")); };
	let $purge = ()=>{[$pathHelp,$body].forEach($e=>
		Array.from($e.childNodes).forEach($node=>{$node.parentNode.removeChild($node);})
	);};
	let $selector = ($e,$node)=>{
		$node = $e.currentTarget ? $e.currentTarget : $node;
		if(!$e.ctrlKey) Array.from($node.parentNode.querySelectorAll(".selected"))
			.filter($n=>$n!==$node).forEach($n=>$n.classList.remove("selected"));
		if($node.classList.contains("selected")) $node.classList.remove("selected");
		else $node.classList.add("selected");
	};
	let $createEditableNameParams = ($d)=>{
		let $lastVal;
		return {className:"fs-name",innerHTML:$d.name,on:{click:$e=>{
			if($e.currentTarget.parentNode.parentNode.classList.contains("selected")){
				$e.stopPropagation(); $e.stopImmediatePropagation();
				$lastVal = $e.currentTarget.innerHTML;
				$e.currentTarget.contentEditable=true;
			}
		},keydown:($e)=>{
			if($e.key==="Enter"){
				$e.preventDefault();let $t= $e.currentTarget; $t.contentEditable=false;
				if($t.innerHTML.length>0){
					let $newName = $d.path.split('/'); $newName.pop(); $newName.push($t.innerHTML);
					$move([$d.path],[$newName.join('/')],true,()=> $t.innerHTML=$lastVal );
				}else $t.innerHTML=$lastVal;
			}
		},blur:$e=>{
			let $t = $e.currentTarget;
			setTimeout(()=>{ $t.contentEditable=false; $t.innerHTML=$lastVal; },0);
		}}}
	};
	let $createFolder = ($d,$path,$back)=>{
		$path = $d && $d.name ? $path+'/'+$d.name : $path.split('/').slice(0,-1).join('/');
		let $res =  wfw.dom.appendTo(
			wfw.dom.create("div",
				{className:"fs-element directory",draggable:true,data:{"fs-path":$d.path},on:{
					drop:($e)=>{
						$e.preventDefault(); let $dropData=$e.dataTransfer.getData('text');
						if($res.getAttribute("data-fs-path")!==$dropData){
							let $sels=Array.from($explorer.querySelectorAll(".selected")).map(($s)=>
								$getDataByNode($s).path
							);
							if($sels.indexOf($dropData)<0) $sels.push($dropData);
							$move($sels,$path);
						}
					},
					dragstart : ($e)=>{ $e.dataTransfer.setData('text',$d.path); },
					dragover : ($e)=>{$e.preventDefault();}
				}}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"icon"}),$getSvg($back?"back":'dir'))
		);
		if(!$back){
			wfw.dom.events.clickndbl($res,$selector,()=>$display($path));
			$res.appendChild(wfw.dom.appendTo(wfw.dom.create("div",{className:"infos"}),
				wfw.dom.create("p",$createEditableNameParams($d)),
			));
		}else $res.addEventListener("click",()=>$display($path));
		return $res;
	};
	let $createFile = ($f)=>{
		let $ic;
		if($f.mime.match(/^image.*/)) $ic=wfw.dom.create("img",{src:wfw.webroot+$f.path});
		else if($f.mime.match(/^video.*/))
			$ic = wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/video.svg");
		else if($f.mime.match(/^audio.*/))
			$ic = wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/audio.svg");
		else $ic = wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/image.svg");
		return wfw.dom.appendTo(wfw.dom.create("div",
			{className:"fs-element file",draggable:true,on:{
				click:$selector, dragstart:($e)=>{$e.dataTransfer.setData("text",$f.path)}
			},data:{"fs-path":$f.path}}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"icon"}), $ic),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"infos"}),
				wfw.dom.create("p",$createEditableNameParams($f))
			)
		);
	};
	let $updateButtons = ()=>{};
	let $display = ($path) => {
		$purge(); let $d = $getData($path); let $arr; $updateButtons(); $displayed = $path;
		let $partialPath='';
		$pathHelp.appendChild(wfw.dom.create("span",
			{className:"path-part",data:{path:''},innerHTML:"/",on:{click:($e)=>{
				$display($e.currentTarget.getAttribute("data-path"));}}
			}
		));
		$path.split('/').forEach($p=>{
			if($p.length>0){
				$partialPath += $partialPath.length===0 ? $p : `/${$p}`;
				$pathHelp.appendChild(wfw.dom.create("span",
					{className:"path-part",data:{path:$partialPath},innerHTML:$p,on:{click:($e)=>{
						$display($e.currentTarget.getAttribute("data-path"));
					}}}
				));
			}
		});
		if(!('items' in $d) || typeof $d['items'] !== "object") $arr = Array.from(Object.keys($d));
		else{ $arr = Object.keys($d["items"]); $d = $d["items"]}
		if($path.length>0) $body.appendChild($createFolder($d,$path,true));
		$arr.sort(($a,$b)=>{
			$a = $d[$a]; $b=$d[$b];
			if($a.type===$b.type) return $a.name.toLowerCase().localeCompare($b.name.toLowerCase());
			else return ($a.type==="dir" ? -1:1)
		}).forEach(($k)=>{
			if($d[$k].type==='file') $body.appendChild($createFile($d[$k],$path));
			else $body.appendChild($createFolder($d[$k],$path));
		});
	};
	let $createDir = ()=>{
		let $win; let $input; let $createBtn;
		$explorer.appendChild($win = wfw.dom.appendTo(wfw.dom.create('div',{className:"fs-create"}),
			$input = wfw.dom.create("input",{placeholder:$lstr("FOLDER_NAME"),autofocus:"autofocus",on:{
				keydown : ($e)=>{ if($e.key === "Enter") $createBtn.click();}
			}}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:"buttons"}),
				$createBtn = wfw.dom.create('button',{innerHTML:$lstr("CREATE_BTN"),on:{click:()=>{
					if($input.value.length>0){
						let $l = $displayLoader($lstr("WAITING_FOLDER_CREATION"));
						wfw.network.wfwAPI($create.url,{
							type : "POST",
							postData : {[$create.paramName.paths+'[]']:$displayed+'/'+$input.value},
							"000":()=>{$l.remove();$load($displayed);$explorer.removeChild($win);},
							error : ($res)=>{
								$l.remove();alert($lstr("ERR_FOLDER_CREATION")+"\n"+$res);}
						});
					}
				}}}),
				wfw.dom.create('button',{innerHTML:$lstr("CANCEL"),on:{click:()=>{
					$explorer.removeChild($win);
				}}})
			)
		));
	};
	let $removeSelected = ()=>{
		let $all = Array.from($explorer.querySelectorAll(".fs-element.selected")).map(($e)=>$getDataByNode($e));
		if($all.length===0) alert($lstr("WARN_MUST_CHOOSE_ONE"));
		else{
			let $win;
			$explorer.appendChild($win = wfw.dom.appendTo(wfw.dom.create('div',{className:"fs-remove"}),
				wfw.dom.create("p",{innerHTML:$lstr("CONFIRM_REMOVE_SELECTED")}),
				wfw.dom.appendTo(wfw.dom.create('div',{className:"buttons"}),
					wfw.dom.create('button',{innerHTML:$lstr("REMOVE_BTN"),on:{click:()=>{
						$explorer.removeChild($win);
						let $loader = $displayLoader($lstr("WAITING_REMOVE_SELECTED"));
						wfw.network.wfwAPI($delete.url,{
							type : "POST",
							postData : { [$delete.paramName.paths+'[]'] : $all.map(($a)=>$a.path) },
							"000" : ()=>{
								$loader.remove();
								$all.map($a=>$a.path).forEach(($p)=>{
									let $tmp = $p.split('/'); $tmp.shift(); let $name = $tmp.pop();
									let $d = $getData($tmp.join('/'));
									if('items' in $d) delete $d['items'][$name];
									else delete $d[$name];
									$display($displayed);
								});
								$updateQuotas();
							}, error : ($resp) => {
								$loader.remove();
								alert($lstr("ERR_REMOVE_SELECTED",$resp));
							}
						});
					}}}),
					wfw.dom.create('button',{innerHTML:$lstr("CANCEL"),on:{click:()=>{
						$explorer.removeChild($win);
					}}})
				)
			));
		}
	};
	let $move = ($elems, $dest, $isRename, $errorCallBack)=>{
		let $loader = $displayLoader($isRename ? $lstr("WAITING_RENAME") : $lstr("WAITING_MOVE")
		);
		wfw.network.wfwAPI($rename.url,{
			type : "POST",
			postData : {
				[$rename.paramName.oldPaths+'[]'] : $elems,
				[$rename.paramName.newPaths+'[]'] : $elems.map($e=>{
					return $dest+((!$isRename)?"/"+$e.split('/').pop():'');
				})
			},
			"000" : () => {$loader.remove(); $load($displayed);},
			error : ($res)=>{
				$loader.remove();
				if(typeof $errorCallBack === "function") $errorCallBack($res);
				alert($lstr("ERR_RENAME_MOVE")+"\n"+$res);
			}
		});
	};
	let $displayLoader = ($message,$displayProgress)=>{
		let $loader = new wfw.ui.loaders.eclipse($message); let $progress;
		if($displayProgress){
			$progress = wfw.dom.create("div",{className:"progress"});
			$loader.html.querySelector(".loader").appendChild($progress);
		}
		let $shadowLoader = wfw.dom.appendTo(wfw.dom.create("div",{className:"fs-loader"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"container"}),$loader.html)
		);
		$explorer.appendChild($shadowLoader);
		return {
			loader : $loader, progress : $progress,
			remove : ()=>{ $loader.delete(); $shadowLoader.parentNode.removeChild($shadowLoader); }
		};
	};
	$explorer = wfw.dom.appendTo(wfw.dom.create('div',{className:"file-explorer"}),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"fs-head"}),
			$pathHelp = wfw.dom.create("div",{className:"path"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$createDir}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/folder.svg"),
					wfw.dom.create("span",{className:"title",innerHTML: $lstr("CREATE_FOLDER")})
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:()=>{
						$files.click();
					}}}),
					$filesForm = wfw.dom.appendTo(wfw.dom.create("form"),
						$files = wfw.dom.create("input",{type:"file",className:"hidden-input",on:{
						change : ()=>{
							for(let $i =0; $i<$files.files.length; $i++){
								if(!$files.files[$i].type.match(/^(video\/mp4|audio\/(mpeg|mp3)|image\/.*)$/)){
									alert($lstr("WARN_MEDIA_TYPE_ACCEPTED"));
									$filesForm.reset(); return;
								}
							}
							let $loader = $displayLoader($lstr("WAITING_SEND_FILE"),true);
							wfw.network.wfwAPI($upload.url,{
								type : "POST",
								postData : {
									[$upload.paramName.file] : $files.files[0],
									[$upload.paramName.name] : $displayed+"/"+$files.files[0].name.split('/').pop()
								},
								beforeSend:($xhr)=>{$xhr.upload.addEventListener("progress",($e)=>{
									$loader.progress.innerHTML='<span>'+Math.round(($e.loaded*100)/$e.total)+'%</span>';
								})},
								"001" : ($name)=>{
									$loader.remove();
									let $d = $getData($displayed); let $f={
										name : $files.files[0].name.split('/').pop(),
										mime : $files.files[0].type,
										ctime : Date.now(), mtime : Date.now(),
										size : $files.files[0].size, type : "file",
										path : $name
									};
									if($d["items"]) $d["items"][$f.name] = $f;
									else $d[$f.name]=$f;
									$display($displayed);
									$filesForm.reset();
									$updateQuotas();
								},
								error : ()=>{ $loader.remove();alert($lstr("ERR_SERVER_REFUSES_FILE")); }
							});
						}
						},name:"file"})),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/uploading-file.svg"),
					wfw.dom.create("span",{className:"title",innerHTML: $lstr("ADD_FILE")})
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$removeSelected}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/filled-trash.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr("REMOVE_SELECTED")})
				)
			)
		),
		$body = wfw.dom.create("div",{className:"fs-body"}),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"fs-footer"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"fs-quotas"}),
				$qSize = wfw.dom.create("div",{className:"fs-quotas-size"}),
				$qTxt = wfw.dom.create("div",{className:"fs-quotas-txt"})
			)
		)
	);
	let $load = ($path)=>{
		$purge();
		let $loader = new wfw.ui.loaders.eclipse($lstr("LOADING"));
		$explorer.appendChild($loader.html);
		wfw.network.wfwAPI($list.url,{
			"001" : ($d)=> {
				$data = JSON.parse($d); $loader.delete();
				$display($path ? $path:''); $updateQuotas();
			}
		});
	};
	let $size = ($dir)=>{
		let $res = 0;
		if(!$dir) Object.keys($data).forEach($k=>$res+=$size($data[$k]));
		else if($dir.type === "file") $res+=$dir.size;
		else if($dir.type === 'dir'){
			Object.keys($dir.items).forEach($k=>{
				$res += $size($dir.items[$k]);
			});
		}
		return $res;
	};
	let $byteSizes = ["o\0", 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'], $byteK = 1024;
	let $displaySize = ($bytes,$decimalPoint)=>{
		if($bytes === 0) return '0o\0';
		let dm = $decimalPoint || 2,
			i = Math.floor(Math.log($bytes) / Math.log($byteK));
		return parseFloat(($bytes / Math.pow($byteK, i)).toFixed(dm)) + $byteSizes[i];
	};
	let $byteToInt = ($str)=>{
		let $nb = $str.substr(0,$str.length-2), $unit = $str.substr($str.length-2,2);
		return $nb * Math.pow($byteK,$byteSizes.indexOf($unit));
	};
	let $updateQuotas = ()=>{
		let $mSizeDisplay = wfw.settings.get("uploader/quotas");
		let $maxSize = $byteToInt($mSizeDisplay);
		let $currentSize = $size();
		$qTxt.innerHTML = $displaySize($currentSize,2)+" / "+$mSizeDisplay;
		$qSize.style.width = ($currentSize/$maxSize)*100 + "%";
	};

	let $redefineError = ()=>{throw new Error("Cann't redefine fileExplorer's properties !")};
	Object.defineProperties($explorer,{
		wfw : { get : ()=>$inst, set : ()=> $redefineError }
	});
	Object.defineProperties(this,{
		html : { get : ()=>$explorer, set : $redefineError },
		selected : { get : ()=>$body.querySelectorAll(".selected"), set : $redefineError },
		getData : { get : ()=>$getDataByNode, set : $redefineError },
		load : { get : ()=>$load, set : $redefineError }
	});
})
})();