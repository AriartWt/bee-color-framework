wfw.require(
	"api/network/wfwAPI",
	"api/dom/nodeHelper",
	"api/ui/lang",
	"api/ui/table",
	"api/ui/loaders/eclipse"
);
wfw.init(()=>wfw.ui.lang.load('packages/contact',wfw.next));
wfw.define("packages/contact",function($params){
	let $lstr = ($key,...$replaces)=>wfw.ui.lang.get('packages/contact/'+$key,...$replaces);
	let $body; let $contacts; let $activeWindow;
	let $alert = ($message)=>setTimeout(()=>alert($message),0);
	let $loaded = false; $params = $params || {};
	let $displayWindow = ($row)=>{
		if($activeWindow) return undefined;
		let $d = $row ? $contacts.getRowData($row) : null;
		$activeWindow = wfw.dom.appendTo(wfw.dom.create('div',{className:"panel-window contact-window"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:$lstr("READ")}),
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{click:()=>$closeWindow()}}),
						wfw.dom.create("span",{innerHTML:'+'})
					)
				)
			),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"body"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"content"}),
					wfw.dom.appendTo(wfw.dom.create("div"),
						wfw.dom.create("span",{innerHTML:$lstr("LABEL")+" :"}),
						wfw.dom.create("span",{innerHTML:$d[0]})
					),
					wfw.dom.appendTo(wfw.dom.create("div"),
						wfw.dom.create("span",{innerHTML:$lstr("CDATE_DESC")+" :"}),
						wfw.dom.create("span",{innerHTML:(new Date($d[2]*1000)).toLocaleString()})
					),
					wfw.dom.appendTo(wfw.dom.create("div"),
						wfw.dom.create("span",{innerHTML:$lstr("STATE_DESC")+" :"}),
						wfw.dom.create("span",{innerHTML:$d[4]?$lstr('STATE'):$lstr('NOT_READ')})
					),
					wfw.dom.create("pre",{innerHTML:$d[1]})
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.create("button",{innerHTML:$lstr('CLOSE'),on:{click:()=>{$closeWindow();}}})
				)
			)
		);
		$body.appendChild($activeWindow);
	};
	let $closeWindow=()=>{if($activeWindow){$body.removeChild($activeWindow);$activeWindow=null;}};
	let $edit = ($e)=>{ $displayWindow($e.currentTarget); };
	let $archive = ()=>{
		let $rows = $contacts.getSelectedRows(); let $box;
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		$body.appendChild($box=wfw.dom.appendTo(wfw.dom.create('div',{className:'contact-remove'}),
			wfw.dom.create('p',{innerHTML:$lstr("CONFIRM_ARCHIVING")}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
				wfw.dom.create('button',{innerHTML:$lstr("ARCHIVE"),on:{click:()=>{
					$body.removeChild($box);
					let $ids = $rows.map($r=>$contacts.getRowData($r)).map($d=>$d[5]);
					let $loader = $displayLoader($lstr('WAIT_ARCHIVING'));
					wfw.network.wfwAPI(wfw.webroot+"contact/archive",{
						type : "POST", postData : {'ids[]':$ids},
						"001" : ($data)=>{
							$loader.remove();
							/** @var Array $data */
							$data = JSON.parse($data);
							$rows.forEach($r=>{
								if($data.indexOf($contacts.getRowData($r)[5]) >= 0){
									let $oldData = $contacts.getRowData($r);
									$contacts.removeRow($r);
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
	let $markAsRead = ()=>{
		let $rows = $contacts.getSelectedRows();
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		let $ids = $rows.map($r=>$contacts.getRowData($r)).map($d=>$d[5]);
		let $loader = $displayLoader($lstr("WAIT_MARK_AS_READ"));
		wfw.network.wfwAPI(wfw.webroot+"contact/markAsRead",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($contacts.getRowData($r)[5]) >= 0){
						$contacts.editRow($r,
							{[$lstr('STATE')]:true,[$lstr('RDATE')]:Date.now()/1000}
						);
						$dispatch('markAsRead',$contacts.getRowData($r));
					}
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr('ERR_MARK_AS_READ')+"\n"+$res) }
		});
	};
	let $markAsUnread = ()=>{
		let $rows = $contacts.getSelectedRows();
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		let $ids = $rows.map($r=>$contacts.getRowData($r)).map($d=>$d[5]);
		let $loader = $displayLoader($lstr('WAIT_MARK_AS_UNREAD'));
		wfw.network.wfwAPI(wfw.webroot+"contact/markAsUnread",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($contacts.getRowData($r)[5]) >= 0){
						$contacts.editRow($r,
							{[$lstr('RDATE')]:null}
						);
						$dispatch("markAsUnread",$contacts.getRowData($r));
					}
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr('ERR_MARK_AS_UNREAD')+"\n"+$res); }
		});
	};
	let $displayLoader = ($message)=>{
		let $loader = new wfw.ui.loaders.eclipse($message);
		let $shadowLoader = wfw.dom.appendTo(wfw.dom.create("div",{className:"contact-loader"}),
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
			('archive' in $params) ? $params.archive :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$archive}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/trash.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr("BTN_ARCHIVE")})
				),
			('markAsRead' in $params) ? $params.putOnline :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$markAsRead}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/eye.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_MARK_AS_READ')})
				),
			('markAsUnread' in $params) ? $params.putOffline :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$markAsUnread}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/eye.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_MARK_AS_UNREAD')})
				)
		);
	};
	let $parseServerContact = ($d)=>{
		return [
			$d["_label"],
			$d["_infos"],
			$d["_creationDate"],
			$d["_readDate"],
			$d["_readed"],
			$d["_id"]
		];
	};
	let $load = ()=>{
		if(!$loaded) $loaded = true;
		else $dispatch('reload');
		let $loader = $displayLoader($lstr('WAIT_LOADING_CONTACTS'));
		$contacts.rows.forEach($r=>$contacts.removeRow($r));
		wfw.network.wfwAPI(wfw.webroot+"contact/list",{
			"001" : ($data)=>{
				$loader.remove();
				$data = JSON.parse($data);
				$data.map($d=>$parseServerContact($d)).forEach($d=>{
					$contacts.addRow(...$d);
					$dispatch("load",$d);
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr("ERR_LOADING_CONTACTS")+"\n"+$res); }
		});
	};
	let $redefineError = () => {throw new Error("Can't redefine news properties !")};
	let $on = {edit:[],write:[],archive:[],markAsUnread:[],markAsRead:[],load:[],reload:[]};
	let $dispatch = ($event,$data)=>$on[$event].forEach($fn=>$fn($data));

	document.head.appendChild(wfw.dom.create("link",
		{href:$params.css ? $params.css : wfw.webroot+"Css/contact/default.css",rel:"stylesheet"})
	);
	$contacts = new wfw.ui.table([
		{name:$lstr('LABEL'),comparator:($a,$b)=>{return $a.toString().toLowerCase().localeCompare($b.toString().toLowerCase());}},
		{name:$lstr('CONTENT'),comparator:($a,$b)=>{return $a.toString().toLowerCase().localeCompare($b.toString().toLowerCase());}},
		{name:$lstr('CDATE'),sort:{default:"desc",first:"desc"}, displayer:($val)=>{return (new Date($val*1000)).toLocaleString()}},
		{name:$lstr('RDATE'),sort:{first:"desc"},displayer:($val)=>{return $val ? (new Date($val*1000)).toLocaleString() : "--"}}
	],{checkboxes:true,rowEvents : { click : $edit }});
	$body = wfw.dom.appendTo(wfw.dom.create('div',{className:"contact-module"}),
		wfw.dom.appendTo(wfw.dom.create('div'),
			$createButtons($params.buttons || {})
		),
		wfw.dom.appendTo(wfw.dom.create('div',{className:"table-contener"}),
			$contacts.html
		)
	);

	Object.defineProperties(this,{
		html : { get : () => $body, set : $redefineError },
		load : { get : () => $load, set : $redefineError },
		articles : { get : () => $contacts, set : $redefineError },
		onLoad : { get : () => ($fn)=>$on.load.push($fn), set : $redefineError },
		onEdit : { get : () => ($fn)=>$on.edit.push($fn), set : $redefineError },
		onReload : { get : () => ($fn)=>$on.reload.push($fn), set : $redefineError },
		onArchive : { get : () => ($fn)=>$on.archive.push($fn), set : $redefineError },
		onMarkAsRead : { get : () => ($fn)=>$on.markAsRead.push($fn), set : $redefineError },
		onMarkAsUnread : { get : () => ($fn)=>$on.markAsUnread.push($fn), set : $redefineError }
	});
});