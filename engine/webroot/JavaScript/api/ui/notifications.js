wfw.require("api/dom/nodeHelper");
wfw.define("ui/notifications",(function($styles){
	let $xStart = "right"; let $xShift = "20px"; let $yStart = "bottom"; let $yShift = "10px";
	let $desktopNotif = true; let $displayTime = 10000; let $userAuth = false; let $title=null;
	let $trail = null; let $webroot = wfw.webroot;
	wfw.ready(()=>{
		document.head.appendChild(wfw.dom.create('style',{innerHTML:$styles}));
		let $s = wfw.defined("settings") ? wfw.settings.get("ui/notifications") : null;
		if($s){
			if("xStart" in $s) $xStart = $s["xStart"]; if("xShift" in $s) $xShift = $s["xShift"];
			if("yStart" in $s) $yStart = $s["yStart"]; if("yShift" in $s) $yShift = $s["yShift"];
			if("desktopNotifications" in $s) $desktopNotif = $s["desktopNotifications"];
			if("displayTime" in $s) $displayTime = $s["displayTime"];
			$title = wfw.settings.get("app/name");
		}
		$checkDesktopPermission();
		$trail = wfw.dom.create("div",{
			className : ["notifications-trail"],
			style : { [$xStart] : $xShift, [$yStart] : $yShift }
		});
		document.body.appendChild($trail);
	},true);
	let $checkDesktopPermission = function(){
		if($desktopNotif && window.Notification){
			Notification.requestPermission(()=> {
				if(Notification.permission === "granted") $userAuth = false;
			});
		}
	};
	let $create = function($params){
		return wfw.dom.appendTo(
			wfw.dom.create("div",{className : "notification"}),
			wfw.dom.appendTo(
				wfw.dom.create("div",{className:"notification-panel"}),
				wfw.dom.appendTo(
					wfw.dom.create("div",{className:"notification-left"}),
					wfw.dom.appendTo(
						wfw.dom.create("div",{className:"vertical-centror"}),
						wfw.dom.create("img",{
							src : $webroot+'Image/Icons/'
								  +(($params.icon)?$params.icon:'accept')+'.png',
							title : (($params.title) ? $params.title : undefined),
							className : 'icon'
						})
					)
				),
				wfw.dom.appendTo(
					wfw.dom.create("div",{className:"notification-right"}),
					wfw.dom.create("span",{innerHTML : $params.message})
				),
				wfw.dom.create("img",{
					src: $webroot+'Image/Icons/delete-disable.png', title: 'fermer',
					className : 'little-icon',
					onclick : ($e) =>  $e.target.parentNode.parentNode.parentNode.removeChild(
						$e.target.parentNode.parentNode
					),
					onmouseover : ($e) => $e.target.src = $webroot+'Image/Icons/delete.png',
					onmouseout : ($e) => $e.target.src = $webroot+'Image/Icons/delete-disable.png'
				})
			)
		);
	};
	let $dispatchDesktopNotification = function($params){
		if($userAuth) new Notification(
			$params.title ? $params.title : $title ? $title : document.title,
			{ icon : $params.icon, body : $params.message }
		);
	};
	let $display = function($params){
		let $notif = $create($params); wfw.dom.appendTo($trail,$notif);
		setTimeout(()=>{ try{$trail.removeChild($notif);}catch($e){}},$displayTime);
		$dispatchDesktopNotification($params);
	};
	let $redefineError = () => {throw new Error("Cann't redefine notification's properties !")};
	Object.defineProperties(this,{ display : { get : () => $display, set : $redefineError } });
	return this;
})(window.notificationsStyle ||
	".notifications-trail{ position:fixed; z-index:9999999999; }\n" +
	".notification{ border-radius:10px; color:white; max-width:300px; background-color:rgba(50,50,50,1); margin : 10px;}\n" +
	".notification-panel{ display:flex; position:relative; padding: 5px 25px 5px 5px; }\n" +
	".notification-right{ margin-left:5px; margin-right:5px; }\n" +
	".notification-left{ display:flex; min-width:25px; }\n" +
	".notification .notification-panel>img{ position:absolute; right:5px; top:5px; }"
));