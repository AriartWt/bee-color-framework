wfw.require("api/dom/events/appears");
wfw.define("plugins/pictureViewer/autoplay",function($params){
	$params = $params || {}; let $doc = $params.doc || document;
	let $autoplayTime = 20000; let $playSpeed = 6000;
	let $at = wfw.settings.get('ui/pictureViewer/autoplayTime');
	let $ps = wfw.settings.get('ui/pictureViewer/playSpeed');
	$autoplayTime = $at ? $at : $autoplayTime; $playSpeed = $ps ? $ps : $playSpeed;
	let $autoplay = ($slider)=>{
		let $active=$slider.querySelector(".css-slider-input-autoplay");
		if(!$active.checked){
			let $attr=parseFloat($slider.getAttribute("data-lasttime"));
			if($attr>0 && Date.now()-$attr > $autoplayTime){
				$slider.setAttribute("data-lasttime","-1"); $active.checked=true;
			}
		}else{
			let $checked=$slider.querySelector(".css-slider-input-chooser:checked");
			let $id_split=$checked.id.split("_");
			$slider.querySelectorAll(".picture")[$id_split[$id_split.length-1]]
				.querySelector(".arrow-next").click();
		}
		setTimeout(function(){$autoplay($slider);},$playSpeed);
	};
	let $sliders = $doc.querySelectorAll(".css-slider[data-autoplay=\"1\"]");
	$sliders.forEach(($slider)=>{
		$slider.querySelector(".css-slider-input-autoplay").addEventListener("click",function($e){
			$e.stopPropagation();
		});
		$slider.setAttribute("data-lasttime","-1");
		$slider.addEventListener("click",function(e){
			let $aplay=this.querySelector(".css-slider-input-autoplay");
			if(!(e.screenX===0 && e.screenY===0)){
				this.setAttribute("data-lasttime",Date.now());
				if($aplay.checked) $aplay.checked=false;
			}
		});
		wfw.dom.events.appears($slider,()=>setTimeout(()=>$autoplay($slider),$playSpeed),null,$doc);
	});
});