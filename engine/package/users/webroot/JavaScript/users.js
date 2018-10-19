wfw.require(
	"api/network/wfwAPI",
	"api/dom/nodeHelper",
	"api/ui/table",
	"api/ui/lang",
	"api/ui/loaders/eclipse"
);
wfw.init(()=>wfw.ui.lang.load('packages/users',wfw.next));
wfw.define("packages/users",function($params){
	let $lstr = ($key,...$replaces) => wfw.ui.lang.get('packages/users/'+$key,...$replaces);
	let $body = null; let $users = null; let $loaded = false;
	let $disableClientLogin = true; let $disableClientRole = false ;let $activeWindow;
	let $alert = ($message) => setTimeout(()=>alert($message),0);
	let $displayWindow = ($row) => {
		if($activeWindow) return undefined;
		let $d = $row ? $users.getRowData($row) : null; let $windowBody;
		$activeWindow = wfw.dom.appendTo(wfw.dom.create('div',{className:"panel-window users-window"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"head"}),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"title"}),
					wfw.dom.create("span",{innerHTML:$lstr(($d ? "EDIT" : "CREATE") + "_USER") }),
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"close",on:{click:()=>$closeWindow()}}),
						wfw.dom.create("span",{innerHTML:'+'})
					)
				)
			),
			$windowBody = wfw.dom.create("div",{className:"body"})
		);
		let $inputs = {
			progressBar:null, progressLabel:null, password:null, passwordConfirm:null,
			select:null, login:null, email:null
		};
		let $pwdStrength = ()=>{
			let $score = 0; let $pwd = $inputs.password.value;
			if($pwd.match(/[a-z]/)) $score++; if($pwd.match(/[A-Z]/)) $score++;
			if($pwd.match(/[0-9]/)) $score++; if($pwd.match(/[^a-zA-Z0-9]/)) $score++;
			if($pwd.length>7) $score++;

			$score = ($score/5)*100; $inputs.progressBar.style.width = $score+"%";
			if($score === 100) $inputs.progressLabel.innerHTML = $lstr("SAFE_PWD");
			else $inputs.progressLabel.innerHTML = $score+"%";

			let $color = "#8F0000";
			if($score>40 && $score<=60) $color = "#b66b00";
			else if($score>60 && $score<=80) $color = "#9aa900";
			else if($score>80 && $score === 100) $color = "#4e9c00";
			$inputs.progressBar.style.backgroundColor = $color;
			if($score < 100) $inputs.password.title=$lstr("PWD_HINT");
			else $inputs.password.title = '';
			$pwdConfirm();
		};
		let $pwdConfirm = ()=>{
			let $color = "#8F0000"; let $title=$lstr("WARN_PWD_NOT_MATCH");
			if($inputs.passwordConfirm.value === $inputs.password.value){
				$color = "#4e9c00"; $title='';
			}
			if($inputs.passwordConfirm.value.length === 0) $color = null;
			if($color){
				$inputs.passwordConfirm.style.outline = `3px solid ${$color}`;
				$inputs.passwordConfirm.title = $title;
			}else{
				$inputs.passwordConfirm.style.outline="none";
				$inputs.passwordConfirm.title = '';
			}
		};
		if($d){
			let $email; let $state; let $box;
			let $editMail = ()=>{
				let $div = $email.querySelector("div"); $div.contentEditable = true;
				$div.focus();
				$div.addEventListener("keydown",($e)=>{
					if($e.key === "Enter"){
						let $mail = $div.innerHTML;
						$e.preventDefault();
						$e.stopPropagation();
						$e.stopImmediatePropagation();
						$div.contentEditable = false;
						let $loader = $displayLoader($lstr("WAIT_EDIT_MAIL"), $windowBody);
						wfw.network.wfwAPI(wfw.webroot+"users/admin/changeMail",{
							type : "POST",
							postData : { id : $d[4], email : $mail },
							"000" : ()=>{
								$loader.remove();
								$users.editRow($row,{"E-mail":$mail}); $div.innerHTML = $mail;
								$dispatch("mailChange",$users.getRowData($row));
								if($disableClientLogin && $d[3] ===  "Client"){
									$users.editRow($row,{"Login" : $mail});
									$dispatch("loginChange",$users.getRowData($row));
								}
							},
							error : ($err)=>{
								$loader.remove(); $div.innerHTML = $d[1];
								$alert($lstr("ERR_EDIT_MAIL")+"\n"+$err);
							}
						});
					}
				});
				$div.addEventListener("blur",()=>{
					$div.contentEditable = false; $div.innerHTML = $d[1];
				});
			};
			let $chooseStateIcon = ($state) => {
				switch($state){
					case "EnabledUser" : return wfw.webroot+"Image/users/svg/icons/disable-user.svg";
					case "DisabledUser" : return wfw.webroot+"Image/users/svg/icons/new-user.svg";
					case "UserWaitingForMailConfirmation" :
					case "UserWaitingForRegisteringConfirmation" :
					case "UserWaitingForPasswordReset" :
						return wfw.webroot+"Image/svg/icons/cancel.svg"
				}
			};
			let $chooseStateTitle = ($state) => {
				switch($state){
					case "EnabledUser" : return $lstr('CLICK_TO_DISABLE');
					case "DisabledUser" : return $lstr('CLICK_TO_ENABLE');
					case "UserWaitingForMailConfirmation" : return $lstr('CLICK_TO_CANCEL_MAIL');
					case "UserWaitingForRegisteringConfirmation" :
						return $lstr('CLICK_TO_CANCEL_REGISTRATION');
					case "UserWaitingForPasswordReset" : return $lstr('CLICK_TO_CANCEL_PWD');
				}
			};
			let $editState = ()=>{
				let $action=wfw.webroot+"users/admin/"; let $postData = {}; let $nstate = null;
				let $event;
				switch($d[2]){
					case "EnabledUser" :
						$action +="disable";
						$postData = {"ids[]" : [$d[4]]};
						$nstate = "DisabledUser";
						$event = "disable";
						break;
					case "DisabledUser" :
						$action +="enable";
						$postData = {"ids[]" : [$d[4]]};
						$nstate = "EnabledUser";
						$event = "enable";
						break;
					case "UserWaitingForMailConfirmation" :
						$action +="cancelChangeMail";
						$postData = {"id" : $d[4]};
						$nstate = "EnabledUser";
						$event = "cancelChangeMail";
						break;
					case "UserWaitingForRegisteringConfirmation" :
						$action +="cancelUserRegistration";
						$postData = {"id" : $d[4]};
						$nstate = "EnabledUser";
						$event = "cancelRegistration";
						break;
					case "UserWaitingForPasswordReset" :
						$action +="cancelResetPassword";
						$postData = {"id" : $d[4]};
						$nstate = "EnabledUser";
						$event = "cancelPasswordReset";
						break;
					default : throw new Error($d[2]+" is not a supported state !");
				}
				let $loader = $displayLoader($lstr('WAIT_EDIT_USER_STATE'),$windowBody);
				wfw.network.wfwAPI($action,{
					type : "POST",
					postData : $postData,
					"001" : ()=>{
						$loader.remove();
						let $svg = $state.querySelector("svg");
						if($svg) $svg.parentNode.removeChild($svg);
						$state.appendChild(wfw.dom.import.svg($chooseStateIcon($nstate)));
						$users.editRow($row,{[$lstr('STATE')] : $nstate});
						$state.querySelector("div").innerHTML = $getState($nstate);
						$state.title=$chooseStateTitle($nstate);
						$dispatch($event,$users.getRowData($row));
					},
					error : ($err)=>{
						$loader.remove();
						$alert($lstr('ERR_EDIT_USER_STATE')+"\n"+$err);
					}
				})
			};
			let $changePwd = ()=>{
				let $loader = $displayLoader($lstr('WAIT_PWD_RESET'), $box);
				wfw.network.wfwAPI(wfw.webroot+"users/admin/resetPassword",{
					type : "POST",
					postData : {
						id : $d[4],
						password : $inputs.password.value,
						password_confirm : $inputs.passwordConfirm.value
					},
					"000" : ()=>{
						$loader.remove();
						$box.parentNode.removeChild($box);
						$dispatch("passwordReset",$d);
					},
					"201" : ($data)=>{
						$loader.remove(); $data = JSON.parse($data);
						if("password" in $data){
							$inputs.password.classList.add("input-error");
							$inputs.password.title = $data.password;
						}
						if("password_confirm" in $data){
							$inputs.passwordConfirm.classList.add("input-error");
							$inputs.passwordConfirm.title = $data.password_confirm;
						}
					},
					error : ($err)=>{ $loader.remove(); $alert($lstr('ERR_PWD_RESET')+"\n"+$err); }
				})
			};
			wfw.dom.appendTo($windowBody,
				wfw.dom.appendTo(wfw.dom.create("div",{className:"inputs"}),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"info"+($disableClientLogin && $d[3]==="Client" ? " hidden" : "")}),
						wfw.dom.create("span",{innerHTML:$lstr('USER_NAME')+" : "}),
						wfw.dom.create("div",{innerHTML : $d[0]})
					),
					$email = wfw.dom.appendTo(wfw.dom.create("div",{className:"info editable",on:{click:$editMail}}),
						wfw.dom.create("span",{innerHTML:$lstr('EMAIL')+" : "}),
						wfw.dom.create("div",{innerHTML : $d[1]}),
						wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/settings.svg")
					),
					wfw.dom.appendTo(wfw.dom.create("div",{className:"info"}),
						wfw.dom.create("span",{innerHTML:$lstr('ROLE')+" : "}),
						wfw.dom.create("div",{innerHTML : $getRole($d[3])})
					),
					$state = wfw.dom.appendTo(wfw.dom.create("div",{className:"info editable",title:$chooseStateTitle($d[2]),on:{click:$editState}}),
						wfw.dom.create("span",{innerHTML:$lstr('STATE')+" : "}),
						wfw.dom.create("div",{innerHTML : $getState($d[2])}),
						wfw.dom.import.svg($chooseStateIcon($d[2]))
					)
				),
				wfw.dom.create("button",{innerHTML:$lstr('BTN_RESET_PWD'),on:{click:()=>{
					$windowBody.appendChild(
						$box=wfw.dom.appendTo(wfw.dom.create('div',{className:'users-modal'}),
							wfw.dom.appendTo(wfw.dom.create('div',{className:'inputs'}),
								$inputs.password = wfw.dom.create("input",{type:"password",placeholder:$lstr('PWD'),className:"password-input",
									on : { keydown : $pwdStrength, change : $pwdStrength, keyup : $pwdStrength }
								}),
								wfw.dom.appendTo(wfw.dom.create("div",{className:"progress-bar"}),
									$inputs.progressBar = wfw.dom.create("div"),
									$inputs.progressLabel = wfw.dom.create("span",{innerHTML:"0%"})
								),
								$inputs.passwordConfirm = wfw.dom.create("input",{type:"password",className:"passwordConfirm-input",placeholder:$lstr('PWD_CONFIRM'),
									on : { keydown : $pwdConfirm, change : $pwdConfirm, keyup : $pwdConfirm }
								})
							),
							wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
								wfw.dom.create('button',{innerHTML:$lstr('RESET'),on:{click:$changePwd}}),
								wfw.dom.create('button',{innerHTML:$lstr('CANCEL'),on:{click:()=>{
									$box.parentNode.removeChild($box);
								}}})
							)
						)
					);
				}}})
			);
		}else{
			let $roles = { "admin" : $lstr('ADMIN'), "client" : $lstr('CLIENT') };
			if($disableClientRole) delete $roles.client;
			wfw.dom.appendTo($windowBody,
				wfw.dom.appendTo(wfw.dom.create("div",{className:"inputs"}),
					wfw.dom.appendTo(wfw.dom.create("div"),
						$inputs.select = wfw.dom.create("select",{ options:$roles,
							on:{ change : ()=>{
								if($inputs.select.value === "client" && $disableClientLogin)
									$inputs.login.classList.add("hidden-input");
								else $inputs.login.classList.remove("hidden-input");
						}}}),
						$inputs.login = wfw.dom.create("input",{type:"text",placeholder:$lstr('USER_NAME')}),
						$inputs.email = wfw.dom.create("input",{type:"email",placeholder:$lstr('EMAIL')}),
						$inputs.password = wfw.dom.create("input",{type:"password",placeholder:$lstr('PWD'),className:"password-input",
							on : { keydown : $pwdStrength, change : $pwdStrength, keyup : $pwdStrength }
						}),
						wfw.dom.appendTo(wfw.dom.create("div",{className:"progress-bar"}),
							$inputs.progressBar = wfw.dom.create("div"),
							$inputs.progressLabel = wfw.dom.create("span",{innerHTML:"0%"})
						),
						$inputs.passwordConfirm = wfw.dom.create("input",{type:"password",className:"passwordConfirm-input",placeholder:$lstr('PWD_CONFIRM'),
							on : { keydown : $pwdConfirm, change : $pwdConfirm, keyup : $pwdConfirm }
						})
					)
				),
				wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
					wfw.dom.create("button",{innerHTML:$lstr('CREATE'),on:{click:()=>{
						let $loader = $displayLoader($lstr('WAIT_CREATE_USER'));
						wfw.network.wfwAPI(wfw.webroot+"users/admin/register",{
							type : "POST",
							postData : {
								type:$inputs.select.value,
								login:($inputs.select.value==="client" && $disableClientLogin)
										? $inputs.email.value : $inputs.login.value,
								email:$inputs.email.value,
								password:$inputs.password.value,
								password_confirm:$inputs.passwordConfirm.value
							},
							"001" : ($data)=>{
								$loader.remove(); $data = $parseServerUser(JSON.parse($data));
								$users.addRow(...$data); $closeWindow();
								$dispatch("register",$data);
							},
							"201" : ($data)=>{
								$loader.remove(); $data = JSON.parse($data);
								Object.keys($data).forEach($k=>{
									$inputs[$k].classList.add("input-error");
									$inputs[$k].title=$data[$k];
								});
							},
							error : ($res)=>{
								$loader.remove();
								$alert($lstr('ERR_CREATE_USER')+"\n"+$res);
							}
						})
					}}}),
					wfw.dom.create("button",{innerHTML:$lstr('CANCEL'),on:{click:()=>{$closeWindow();}}})
				)
			);
		}
		$body.appendChild($activeWindow);
	};
	let $closeWindow=()=>{if($activeWindow) $body.removeChild($activeWindow);$activeWindow = null;};
	let $register = ()=>{ $displayWindow();};
	let $enable = ()=>{
		let $rows = $users.getSelectedRows();
		if($rows.length===0){ alert($lstr("WARN_MUST_SELECT_ONE")); return; }
		let $ids = $rows.map($r=>$users.getRowData($r)).map($d=>$d[4]);
		let $loader = $displayLoader($lstr('WAIT_ENABLE_USERS'));
		wfw.network.wfwAPI(wfw.webroot+"users/admin/enable",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($users.getRowData($r)[4]) >= 0){
						$users.editRow($r,{[$lstr('STATE')]:"EnabledUser"});
						$dispatch("enable",$users.getRowData($r));
					}
				});
			},
			error : ($res)=>{
				$loader.remove();
				$alert($lstr('ERR_ENABLE_USERS')+"\n"+$res)
			}
		});
	};
	let $disable = ()=>{
		let $rows = $users.getSelectedRows();
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		let $ids = $rows.map($r=>$users.getRowData($r)).map($d=>$d[4]);
		let $loader = $displayLoader($lstr('WAIT_DISABLE_USERS'));
		wfw.network.wfwAPI(wfw.webroot+"users/admin/disable",{
			type : "POST", postData : {'ids[]':$ids},
			"001" : ($data)=>{
				$loader.remove();
				/** @var Array $data */
				$data = JSON.parse($data);
				$rows.forEach($r=>{
					$r.querySelector('input[type="checkbox"]').checked=false;
					if($data.indexOf($users.getRowData($r)[4]) >= 0){
						$users.editRow($r,{[$lstr('STATE')]:"DisabledUser"});
						$dispatch("disable",$users.getRowData($r));
					}
				});
			},
			error : ($res)=>{ $loader.remove(); $alert($lstr('ERR_DISABLE_USERS')+"\n"+$res) }
		});
	};
	let $remove = ()=>{
		let $rows = $users.getSelectedRows(); let $box;
		if($rows.length===0){ alert($lstr('WARN_MUST_SELECT_ONE')); return; }
		$body.appendChild($box=wfw.dom.appendTo(wfw.dom.create('div',{className:'users-modal'}),
			wfw.dom.create('p',{innerHTML:$lstr('CONFIRM_REMOVE_USERS')}),
			wfw.dom.appendTo(wfw.dom.create('div',{className:'buttons'}),
				wfw.dom.create('button',{innerHTML:$lstr('REMOVE'),on:{click:()=>{
					$body.removeChild($box);
					let $ids = $rows.map($r=>$users.getRowData($r)).map($d=>$d[4]);
					let $loader = $displayLoader($lstr('WAIT_REMOVE_USERS'));
					wfw.network.wfwAPI(wfw.webroot+"users/admin/remove",{
						type : "POST", postData : {'ids[]':$ids},
						"001" : ($data)=>{
							$loader.remove();
							/** @var Array $data */
							$data = JSON.parse($data);
							$rows.forEach($r=>{
								if($data.indexOf($users.getRowData($r)[4]) >= 0){
									let $oldData = $users.getRowData($r);
									$users.removeRow($r);
									$dispatch("remove",$oldData);
								}
							});
						},
						error : ($res)=>{
							$loader.remove();
							$alert($lstr('ERR_REMOVE_USERS')+"\n"+$res)
						}
					});
				}}}),
				wfw.dom.create('button',{innerHTML:$lstr('CANCEL'),on:{click:()=>$body.removeChild($box)}})
			))
		);
	};
	let $edit = ($e)=>{$displayWindow($e.currentTarget)};
	let $displayLoader = ($message,$container)=>{
		let $loader = new wfw.ui.loaders.eclipse($message);
		let $shadowLoader = wfw.dom.appendTo(wfw.dom.create("div",{className:"users-loader"}),
			wfw.dom.appendTo(wfw.dom.create("div",{className:"container"}),$loader.html)
		);
		if($container) $container.appendChild($shadowLoader);
		else $body.appendChild($shadowLoader);
		return {
			loader : $loader,
			remove : ()=>{ $loader.delete(); $shadowLoader.parentNode.removeChild($shadowLoader); }
		};
	};
	let $createButtons = ($params)=>{
		return wfw.dom.appendTo(wfw.dom.create("div",{className:"buttons"}),
			('register' in $params) ? $params.register :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$register}}),
					wfw.dom.import.svg(wfw.webroot+"Image/users/svg/icons/new-user.svg"),
					wfw.dom.create("span",{className:"title",innerHTML: $lstr("BTN_CREATE")})
				),
			('remove' in $params) ? $params.remove :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$remove}}),
					wfw.dom.import.svg(wfw.webroot+"Image/svg/icons/trash.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_REMOVE')})
				),
			('enable' in $params) ? $params.enable :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$enable}}),
					wfw.dom.import.svg(wfw.webroot+"Image/users/svg/icons/new-user.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_ENABLE')})
				),
			('disable' in $params) ? $params.disable :
				wfw.dom.appendTo(wfw.dom.create("div",{className:"button",on:{click:$disable}}),
					wfw.dom.import.svg(wfw.webroot+"Image/users/svg/icons/disable-user.svg"),
					wfw.dom.create("span",{className:"title",innerHTML : $lstr('BTN_DISABLE')})
				)
		);
	};
	let $parseServerUser = ($d)=>{
		return [
			$d["_login"],
			$d["_email"],
			$d["_state"],
			$d["_type"],
			$d["_id"],
			$d["_settings"]
		];
	};
	let $load = ()=>{
		let $loader = $displayLoader($lstr('WAIT_LOADING_USERS'));
		$users.rows.forEach($r=>$users.removeRow($r));
		if(!$loaded) $loaded = true;
		else $dispatch('reload');
		wfw.network.wfwAPI(wfw.webroot+"users/admin/list",{
			"001" : ($data)=>{
				$loader.remove();
				$data = JSON.parse($data);
				$data.map($d=>$parseServerUser($d)).forEach($d=>{
					$users.addRow(...$d);
					$dispatch("load",$d);
				});
			},
			error : ($res)=>{
				$loader.remove();
				$alert($lstr('ERR_LOADING_USERS')+"\n"+$res);
			}
		});
	};
	let $getState = ($state)=>{
		switch($state){
			case "EnabledUser" : return $lstr('ENABLED');
			case "DisabledUser" : return $lstr('DISABLED');
			case "UserWaitingForMailConfirmation" : return $lstr('WAITING_MAIL');
			case "UserWaitingForPasswordReset" : return $lstr('PWD_RESET');
			case "UserWaitingForRegisteringConfirmation" : return $lstr('WAITING_REGISTRATION');
			default : return $state;
		}
	};
	let $getRole= ($role)=>{
		switch($role){
			case "Admin" : return $lstr("ADMIN");
			case "Client" : return $lstr('CLIENT');
			case "Basic" : return $lstr('EMPLOYEE');
			default : return $role;
		}
	};
	let $redefineError = () => { throw new Error("Can't redefine users properties !"); };
	let $on = {load:[],reload:[],register:[],remove:[],enable:[],disable:[],loginChange:[],
		mailChange:[],passwordReset:[],cancelPasswordReset:[],cancelChangeMail:[],
		cancelRegistration:[]
	};
	let $dispatch = ($event,$data)=>$on[$event].forEach($fn=>$fn($data));

	$params = $params || {};
	if('disableClientRole' in $params) $disableClientRole = $params.disableClientRole;
	if('disableClientLogin' in $params) $disableClientLogin = $params.disableClientLogin;
	else $disableClientLogin = true;
	document.head.appendChild(wfw.dom.create("link",
		{href:$params.css ? $params.css : wfw.webroot+"/Css/users/default.css",rel:"stylesheet"})
	);
	$users = new wfw.ui.table([
		{name:$lstr('USER_NAME'),sort:{default:"asc",first:"asc"}},
		{name:$lstr('EMAIL')},
		{name:$lstr('STATE'),displayer : $getState},
		{name:$lstr('ROLE'),displayer : $getRole}
	],{checkboxes:true,rowEvents : { click : $edit }});
	$body = wfw.dom.appendTo(wfw.dom.create("div",{className:"users-module"}),
		wfw.dom.appendTo(wfw.dom.create('div'),
			$createButtons($params.buttons || {})
		),
		wfw.dom.appendTo(wfw.dom.create('div',{className:"table-contener"}),
			$users.html
		)
	);
	Object.defineProperties(this,{
		html : { get : () => $body, set : $redefineError },
		load : { get : () => $load, set : $redefineError },
		users : { get : () => $users, set : $redefineError },
		onLoad : { get : () => ($fn) => $on.load.push($fn), set : $redefineError },
		onReload : { get : () => ($fn) => $on.reload.push($fn), set : $redefineError },
		onRemove : { get : () => ($fn) => $on.remove.push($fn), set : $redefineError },
		onEnable : { get : () => ($fn) => $on.enable.push($fn), set : $redefineError },
		onDisable : { get : () => ($fn) => $on.disable.push($fn), set : $redefineError },
		onRegister : { get : () => ($fn) => $on.register.push($fn), set : $redefineError },
		onMailChange : { get : () => ($fn) => $on.mailChange.push($fn), set : $redefineError },
		onLoginChange : { get : () => ($fn) => $on.loginChange.push($fn), set : $redefineError },
		onPasswordReset : { get : () => ($fn) => $on.passwordReset.push($fn), set : $redefineError },
		onCancelChangeMail : { get : () => ($fn) => $on.cancelChangeMail.push($fn), set : $redefineError },
		onCancelRegistration : { get : () => ($fn) => $on.cancelRegistration.push($fn), set : $redefineError },
		onCancelPasswordReset : { get : () => ($fn) => $on.cancelPasswordReset.push($fn), set : $redefineError }
	});
});