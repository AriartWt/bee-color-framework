wfw.require(
	"api/network/wfwAPI",
	"api/dom/nodeHelper",
	"api/ui/notifications",
	"api/ui/lang"
);
wfw.define("packages/miel",function($modules,$params){
	let $doc, $selector = '*[data-miel_modifiable="true"]', $paramAttr = "data-miel_params";
	$params = $params || {};
	$modules = (typeof $modules === "object") ? $modules : {};
	$doc = $params.doc || document;
	let $css = $params.css ? $params.css : wfw.webroot+"Css/miel/default.css";
	if(!$doc.head.querySelector("link[href=\""+$css+"\"]")){
		$doc.head.appendChild(wfw.dom.create("link",{href:$css,rel:"stylesheet"}));
	}
	Object.keys($modules).forEach($k=>{
		if(!('register' in $modules[$k]) || typeof $modules[$k].register !== 'function')
			throw new Error("Miel module "+$k+" must implements a register function !");
	});
	if(!('default' in $modules)) $modules.default = new wfw.plugins.miel.default();
	$doc.querySelectorAll($selector).forEach($node=>{
		$node.classList.add("miel-enabled");$node.classList.add("miel-enabled-icon");
		let $params = $paramAttr.length > 0 ? JSON.parse($node.getAttribute($paramAttr)) : {};
		if(Array.isArray($params)) $params = {module:"default"};
		else if(typeof $params === "object" && !("module" in $params)) $params['module']='default';
		if($params.module in $modules) $modules[$params.module].register($node,$params);
		else throw new Error("Required module "+$params.module+" not found !");
	});
});

wfw.init(()=>wfw.ui.lang.load("plugins/miel/default",wfw.next));
wfw.define("plugins/miel/default",function(){
	let $register = ($node)=>{
		$node.addEventListener('click',function($e){
			$e.preventDefault();
			if($node.getAttribute('data-miel_editing') !== 'true'){
				$node.classList.remove('miel-enabled-icon');
				$node.setAttribute('data-miel_editing','true');
				let $webroot = wfw.webroot+'Image/Icons/'; let $text = $node.innerHTML;
				let $width = $node.offsetWidth;let $height = $node.offsetHeight; $node.innerHTML='';
				wfw.dom.appendTo( $node,
					wfw.dom.appendTo(
						wfw.dom.create('div',{className : 'miel-module-default'}),
						wfw.dom.create('input',{
							type:'text', value:$text.trim(),
							style:{'max-width':$width+'px',height:$height+'px'}}),
						wfw.dom.create('img',{
							src:$webroot+"accept.png",className:'icon',title:"Enregistrer",
							style:{'height':$height+'px'}, onclick : () => $update($node)
						}),
						wfw.dom.create('img',{
							src:$webroot+"delete.png",className:'icon',title:"Annuler",
							style:{'height':$height+'px'},
							onclick : ($e) => {
								$e.stopPropagation(); $e.preventDefault(); $cancel($node,$text);
							}
						})
					)
				);
			}
		});
	};
	let $cancel = ($node,$text) => {
		$node.innerHTML = $text;
		$node.classList.add('miel-enabled-icon'); $node.removeAttribute('data-miel_editing');
	};
	let $update = function($node){
		let $k = $node.getAttribute('data-miel_key');
		let $value = $node.querySelector('input').value;
		wfw.network.wfwAPI(wfw.webroot+'miel/update',{
			type : 'post', postData : { miel_key : $k, miel_data : $value },
			'000' : () => {
				$node.ownerDocument
					.querySelectorAll('*[data-miel_modifiable="true"][data-miel_key="'+$k+'"]')
					.forEach(($node)=>$cancel($node,$value));
				wfw.ui.notifications.display(
					{ message : wfw.ui.lang.get("packages/miel/modules/default/SAVE_DONE") }
				);
			},
			error : ($res,$code) =>{
				wfw.ui.notifications.display({
					message : wfw.ui.lang.get("packages/miel/modules/default/ERROR")
					+' (code : '+$code+')',
					icon : 'prohibit'
				});
			}
		})
	};
	let $res = {};
	let $redefineError = ()=>{throw new Error("Can't redefine miel default plugin properties !");};
	Object.defineProperties($res,{
		register : { get : () => $register, set : $redefineError}
	});
	return $res;
});