<?php
	/** @var \wfw\engine\lib\HTML\resources\lazyloader\Lazyloader $this */
	$class = $this->getClass();
?>
<script>
	window.addEventListener("DOMContentLoaded",()=>{
		let lazyImages = [].slice.call(document.querySelectorAll(".<?= $class ?>"));

		if ("IntersectionObserver" in window) {
			let lazyImageObserver = new IntersectionObserver(entries=>{
				entries.forEach(entry=>{
					if (entry.isIntersecting) {
						let lazyImage = entry.target;
						lazyImage.src = lazyImage.dataset.src;
						lazyImage.classList.remove("<?= $class ?>");
						lazyImageObserver.unobserve(lazyImage);
					}
				});
			});

			lazyImages.forEach(function(lazyImage) {
				lazyImageObserver.observe(lazyImage);
			});
		} else {
			let active = false;
			const lazyLoad = () => {
				if (!active) {
					active = true;
					setTimeout(() => {
						lazyImages.forEach(function (lazyImage) {
							if ((lazyImage.getBoundingClientRect().top <= window.innerHeight
								&& lazyImage.getBoundingClientRect().bottom >= 0)
								&& getComputedStyle(lazyImage).display !== "none"
							) {
								lazyImage.src = lazyImage.dataset.src;
								lazyImage.classList.remove("<?= $class ?>");

								lazyImages = lazyImages.filter(function (image) {
									return image !== lazyImage;
								});

								if (lazyImages.length === 0) {
									document.removeEventListener("scroll", lazyLoad);
									window.removeEventListener("resize", lazyLoad);
									window.removeEventListener("orientationchange", lazyLoad);
								}
							}
						});
						active = false;
					}, 200);
				}
			};
			document.addEventListener("scroll", lazyLoad);
			window.addEventListener("resize", lazyLoad);
			window.addEventListener("orientationchange", lazyLoad);
			window.addEventListener("load", lazyLoad);
		}
	});
</script>