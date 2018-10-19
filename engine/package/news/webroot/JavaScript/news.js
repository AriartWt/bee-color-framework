wfw.require(
	"api/network/wfwAPI",
	"api/dom/nodeHelper",
	"api/ui/lang",
	"api/ui/table",
	"api/ui/lteditor",
	"api/ui/fileExplorer",
	"api/ui/loaders/eclipse",
	"plugins/lteditor/limitedForeColor",
	"plugins/lteditor/advancedLink",
	"plugins/lteditor/medias"
);
wfw.init(()=>wfw.ui.lang.load('packages/news',wfw.next));
wfw.define("packages/news",function($params){
	let $lstr = ($key,...$replaces)=>wfw.ui.lang.get('packages/news/'+$key,...$replaces);
	let $body; let $articles; let $activeWindow; let $explorerWindow; let $preview;
	let $alert = ($message)=>setTimeout(()=>alert($message),0); let $inputFile; let $fe;
	let $loaded = false; $params = $params || {};
	let $displayWindow = ($row)=>{
		if($activeWindow) return undefined;
		let $d = $row ? $articles.getRowData($row) : null; let $icon; let $title;
		let $editor = new wfw.ui.lteditor("edit",{
			link : new wfw.ui.lteditor.plugins.advancedLink(),
			media : new wfw.ui.lteditor.plugins.medias()
		});
		$editor.addButton("foreColor",new wfw.ui.lteditor.plugins.limitedForeColor(
			Array.isArray($params.allowedColors) ? $params.allowedColors : ["#FF9900","#FF0000"],
			$params.defaultColor ? $params.defaultColor : "#000"),'olist');
		if($d) $editor.value = $d[5];
		$activeWindow = wfw.dom.appendTo(wfw.dom.create('div',{className:"panel-window news-window"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:($d?$lstr("CREATE_NEW"):$lstr("EDIT_NEW"))}),
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{click:()=>$closeWindow()}}),
						wfw.dom.create("span",{innerHTML:'+'})
					)
				)
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"inputs"}),
					$title = wfw.dom.create("input",{
						className:"title",
						placeholder:$lstr('TITLE_NEW'),
						value : $d ? $d[0] : ''
					}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"media-input"}),
						$inputFile = wfw.dom.create("input", {
							placeholder:$lstr('VISUAL'),
							disable:true,
							data : { media_type : "image" },
							value : $d ? $d[7] : ''
						}),
						$icon = wfw.dom.appendTo(
							wfw.dom.create("span",{className:"media-icon",on:{click:()=>{
								$fe.load(); $body.appendChild($explorerWindow);
							}}}),
							wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/folder.svg")
						),
						$preview = wfw.dom.create("img",{
							className:"preview",
							src : $d ? $d[7] : undefined,
							alt : ''
						}),
						wfw.dom.create("div",{
							className:"input-disabled",
							on:{click:($e)=>{$e.preventDefault();$icon.click();}}
						})
					)
				),
				$editor.html,
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.create("button",{innerHTML:$lstr($d ? "EDIT" : "CREATE"),on:{click:()=>{
						let $loader = $displayLoader($lstr("WAITING_"+ $d ? "EDIT" : "CREATE" ));
						wfw.network.wfwAPI(wfw.webroot+"news/"+($d?"edit":"create"),{
							type : "POST",
							postData : {
								title : $title.value,
								content : $editor.value,
								visual : $inputFile.value,
								article_id : $d ? $d[4] : undefined
							},
							"001" : ($data)=>{
								$loader.remove();
								$data = $parseServerArticle(JSON.parse($data));
								if($d){
									$articles.editRow($row,$data);
									$dispatch("edit",$data);
								}else{
									$articles.addRow(...$data);
									$dispatch("write",$data);
								}
								$closeWindow();
							},
							error : ($res)=>{
								$loader.remove();
								$alert($lstr("ERR_"+$d ? "EDIT" : "CREATE")+"\n"+$res);
							}
						})
					}}}),
					wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{click:()=>{$closeWindow();}}})
				)
			)
		);
		$body.appendChild($activeWindow);
	};
	let $closeWindow=()=>{if($activeWindow){$body.removeChild($activeWindow);$activeWindow=null;}};
	let $edit = ($e)=>{ $displayWindow($e.currentTarget); };
	let $write = ()=>{ $displayWindow(); };
	let $archive = ()=>{
		let $rows = $articles.getSelectedRows(); let $box;
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		$body.appendChild($box=wfw.dom.appendTo(wfw.dom.create('div',{className:'news-remove'}),
			wfw.dom.create('p',{innerHTML:$lstr("CONFIRM_ARCHIVING")}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
				wfw.dom.create('button',{innerHTML:$lstr("ARCHIVE"),on:{click:()=>{
					$body.removeChild($box);
					let $ids = $rows.map($r=>$articles.getRowData($r)).map($d=>$d[4]);
					let $loader = $displayLoader($lstr('WAIT_ARCHIVING'));
					wfw.network.wfwAPI(wfw.webroot+"news/archive",{
						type : "POST", postData : {'ids[]':$ids},
						"001" : ($data)=>{
							$loader.remove();
							/** @var Array $data */
							$data = JSON.parse($data);
							$rows.forEach($r=>{
								if($data.indexOf($articles.getRowData($r)[4]) >= 0){
									let $oldData = $articles.getRowData($r);
									$articles.removeRow($r);
									$dispatch('archive',$oldData);
								}
							});
						},
						error : ($res)=>{$loader.remove(); $alert($lstr('ERR_ARCHIVING')+"\n"+$res)}
					});
				}}}),
				wfw.dom.create('button',{innerHTML:$lstr('CANCEL'),on:{click:()=>$body.removeChild($box)}})
			))
		);
	};
	let $putOnline = ()=>{
		let $rows = $articles.getSelectedRows();
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		let $ids = $rows.map($r=>$articles.getRowData($r)).map($d=>$d[4]);
		let $loader = $displayLoader($lstr("WAIT_PUT_ONLINE"));
		wfw.network.wfwAPI(wfw.webroot+"news/putOnline",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($articles.getRowData($r)[4]) >= 0){
						$articles.editRow($r,
							{[$lstr('ONLINE')]:true,[$lstr('EDATE')]:Date.now()/1000}
						);
						$dispatch('putOnline',$articles.getRowData($r));
					}
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr('ERR_PUT_ONLINE')+"\n"+$res) }
		});
	};
	let $putOffline = ()=>{
		let $rows = $articles.getSelectedRows();
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		let $ids = $rows.map($r=>$articles.getRowData($r)).map($d=>$d[4]);
		let $loader = $displayLoader($lstr('WAIT_PUT_OFFLINE'));
		wfw.network.wfwAPI(wfw.webroot+"news/putOffline",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($articles.getRowData($r)[4]) >= 0){
						$articles.editRow($r,
							{[$lstr('ONLINE')]:false,[$lstr('EDATE')]:Date.now()/1000}
						);
						$dispatch("putOffline",$articles.getRowData($r));
					}
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr('ERR_PUT_OFFLINE')+"\n"+$res); }
		});
	};
	let $displayLoader = ($message)=>{
		let $loader = new wfw.ui.loaders.eclipse($message);
		let $shadowLoader = wfw.dom.appendTo(wfw.dom.create("div",{className:"news-loader"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"container"}),$loader.html)
		);
		$body.appendChild($shadowLoader);
		return {
			loader : $loader,
			remove : ()=>{ $loader.delete(); $shadowLoader.parentNode.removeChild($shadowLoader); }
		};
	};
	let $createButtons = ($params)=>{
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
			('write' in $params) ? $params.write :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$write}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/edit2.svg"),
					wfw.dom.create("span",{className:"title",innerHTML: $lstr('BTN_WRITE')})
				),
			('archive' in $params) ? $params.archive :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$archive}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/trash.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr("BTN_ARCHIVE")})
				),
			('putOnline' in $params) ? $params.putOnline :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$putOnline}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/eye.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_PUT_ONLINE')})
				),
			('putOffline' in $params) ? $params.putOffline :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$putOffline}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/eye.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_PUT_OFFLINE')})
				)
		);
	};
	let $parseServerArticle = ($d)=>{
		let $lastEdit = $d["_editions"].map($e=>$e).pop();
		return [
			$d["_title"],
			$d["_creationDate"],
			$lastEdit ? $lastEdit["date"] : undefined,
			$d["_online"],
			$d["_id"],
			$d["_content"],
			$d["_editions"],
			$d["_link"]
		];
	};
	let $load = ()=>{
		if(!$loaded) $loaded = true;
		else $dispatch('reload');
		let $loader = $displayLoader($lstr('WAIT_LOADING_NEWS'));
		$articles.rows.forEach($r=>$articles.removeRow($r));
		wfw.network.wfwAPI(wfw.webroot+"news/list",{
			"001" : ($data)=>{
				$loader.remove();
				$data = JSON.parse($data);
				$data.map($d=>$parseServerArticle($d)).forEach($d=>{
					$articles.addRow(...$d);
					$dispatch("load",$d);
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr("ERR_LOADING_NEWS")+"\n"+$res); }
		});
	};
	let $redefineError = () => {throw new Error("Can't redefine news properties !")};
	let $on = {edit:[],write:[],archive:[],putOnline:[],putOffline:[],load:[],reload:[]};
	let $dispatch = ($event,$data)=>$on[$event].forEach($fn=>$fn($data));

	document.head.appendChild(wfw.dom.create("link",
		{href:$params.css ? $params.css : wfw.webroot+"Css/news/default.css",rel:"stylesheet"})
	);
	$articles = new wfw.ui.table([
		{name:$lstr('TITLE'),comparator:($a,$b)=>{return $a.toString().toLowerCase().localeCompare($b.toString().toLowerCase());}},
		{name:$lstr('CDATE'),sort:{default:"desc",first:"desc"}, displayer:($val)=>{return (new Date($val*1000)).toLocaleString()}},
		{name:$lstr('EDATE'),sort:{first:"desc"},displayer:($val)=>{return $val ? (new Date($val*1000)).toLocaleString() : "--"}},
		{name:$lstr('ONLINE'),sort:{first:"desc"},displayer:($val)=>{return $lstr($val ?'YES':'NO')}}
	],{checkboxes:true,rowEvents : { click : $edit }});
	$body = wfw.dom.appendTo(wfw.dom.create('div',{className:"news-module"}),
		wfw.dom.appendTo(wfw.dom.create('div'),
			$createButtons($params.buttons || {})
		),
		wfw.dom.appendTo(wfw.dom.create('div',{className:"table-contener"}),
			$articles.html
		)
	);
	$fe = new wfw.ui.fileExplorer({
		upload : {url:wfw.webroot+"uploader/uploadFile", paramName:{ file:'file',name:'name' }},
		delete : {url:wfw.webroot+"uploader/delete", paramName:{ paths:"paths" }},
		rename : {url:wfw.webroot+"uploader/rename", paramName:{ oldPaths:"oldPaths",newPaths:"newPaths" }},
		create : {url:wfw.webroot+"uploader/createPath", paramName:{ paths:"paths" }},
		list : {url:wfw.webroot+"uploader/list"}
	});
	$explorerWindow = wfw.dom.appendTo(wfw.dom.create("div",{className:"panel-window news-window"}),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
				wfw.dom.create("span",{innerHTML:$lstr('CHOOSE_PICTURE')}),
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{click:()=>$body.removeChild($explorerWindow)}}),
					wfw.dom.create("span",{innerHTML:'+'})
				)
			)
		),
		wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
			$fe.html,
			wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
				wfw.dom.create("button",{innerHTML:$lstr('CHOOSE'),on:{click:()=>{
					let $selected = $fe.selected;
					if($selected.length>1) alert($lstr('WARN_ONLY_CHOOSE_ONE'));
					else if($selected.length===1){
						let $d = $fe.getData($selected[0]);
						if($d.type!=="file" || $d.mime.indexOf("image") !== 0)
							alert($lstr('WARN_MUST_SELECT_PICTURE'));
						else{
							$inputFile.value = wfw.webroot+$d.path;
							$preview.src = $inputFile.value;
							$body.removeChild($explorerWindow);
						}
					}else alert($lstr('WARN_MUST_SELECT_PICTURE'));
				}}}),
				wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{click:()=>{
					$body.removeChild($explorerWindow);
				}}})
			)
		)
	);

	Object.defineProperties(this,{
		html : { get : () => $body, set : $redefineError },
		load : { get : () => $load, set : $redefineError },
		articles : { get : () => $articles, set : $redefineError },
		onLoad : { get : () => ($fn)=>$on.load.push($fn), set : $redefineError },
		onEdit : { get : () => ($fn)=>$on.edit.push($fn), set : $redefineError },
		onWrite : { get : () => ($fn)=>$on.write.push($fn), set : $redefineError },
		onReload : { get : () => ($fn)=>$on.reload.push($fn), set : $redefineError },
		onArchive : { get : () => ($fn)=>$on.archive.push($fn), set : $redefineError },
		onPutOnline : { get : () => ($fn)=>$on.putOnline.push($fn), set : $redefineError },
		onPutOffline : { get : () => ($fn)=>$on.putOffline.push($fn), set : $redefineError }
	});
});