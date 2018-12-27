window.addEventListener("load",()=>{
	let $timer; let $time=250; let $final; let $complete=true; let $steps=30;
	let $smoothScrolling = ($e,$scrollOffset)=>{
		if(!$complete){
			clearInterval($timer);
			$e.scrollLeft = $final;
			$complete=true;
			setTimeout(()=>$smoothScrolling($e,$scrollOffset),10);
		}else{
			$complete = false;
			$final = $e.scrollLeft+$scrollOffset;
			let $current = 0; let $currentPos=$e.scrollLeft;
			$timer = setInterval(()=>{
				$current++;
				if($e.scrollLeft!==$final && $current<$steps) $e.scrollLeft=$currentPos+=$scrollOffset/$steps;
				else{
					clearInterval($timer);
					$e.scrollLeft = $final;
					$complete = true;
				}
			},$time/$steps);
		}
	};
	let $updateScroll = ($picture)=>{
		let $trail = $picture.parentNode.parentNode;
		let $trailMiddle = $trail.offsetWidth/2;
		let $pictureMiddle = $picture.offsetWidth/2 + $picture.offsetLeft;
		if($trailMiddle + $trail.scrollLeft !== $pictureMiddle)
			$smoothScrolling($trail,$pictureMiddle - ($trailMiddle + $trail.scrollLeft));
	};
	document.querySelectorAll(".css-slider.trail-enabled").forEach($slider=>{
		$slider.querySelectorAll(".css-slider-input-chooser").forEach($i=>{
			$i.addEventListener("change",()=>{
				if($i.checked) $updateScroll(
					$slider.querySelector(".trail label[for=\""+$i.id+"\"]")
				);
			});
		});
	});
});