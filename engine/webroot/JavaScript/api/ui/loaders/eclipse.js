wfw.require("api/dom/nodeHelper");
wfw.define("ui/loaders/eclipse",function($message){
	let $style; let $span;
	let $loader = wfw.dom.appendTo(wfw.dom.create("div",{className:"eclipse-loader"}),
		wfw.dom.create("div",{className:"loader"}),
		$span = wfw.dom.create("p",{className:"message",innerHTML:$message})
	);
	let $updateMessage = ($message)=>{ $span.innerHTML = $message; };
	document.head.appendChild($style = wfw.dom.create('style',{innerHTML: `
		 .eclipse-loader {position:relative;margin:auto;}
		 .eclipse-loader .loader{
			border-radius:100px;
			border-top:3px solid #FF9900;
			border-bottom:3px solid #FF9900;
			animation-name: eclipse-rotate;
			animation-iteration-count:infinite;
			animation-duration:1s;
			margin:auto;
			height:100px;
			width:100px;
			position:relative;
		 }
		 .eclispe-loader .message{margin:0;padding:0;text-align:center;}
		 @keyframes eclipse-rotate{
			from{transform:rotate(0deg);}
			to{transform:rotate(360deg);}
		 }`
	}));
	let $del=()=>{throw new Error("This loader have been deleted and can't be used anymore !");};
	let $remove = ()=>{
		if($style.parentNode) $style.parentNode.removeChild($style);
		if($loader.parentNode) $loader.parentNode.removeChild($loader);
		$loader = undefined; $updateMessage = $del; $remove = $del;
	};
	let $redefineError = ()=>{ throw new Error("Cann't redefine eclipse's properties !") };
	Object.defineProperties($loader,{
		wfw : { get : ()=>$inst, set : $redefineError}
	});
	Object.defineProperties(this,{
		html : { get : ()=>$loader, set : $redefineError },
		updateMessage : { get : ()=>$updateMessage, set : $redefineError },
		delete : { get : ()=>$remove, set : $redefineError }
	});
});