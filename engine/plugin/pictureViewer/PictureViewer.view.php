<?php
/** @var \wfw\engine\plugin\pictureViewer\PictureViewer $this */
$id = $this->getId();
$opts = $this->getOptions();
$svgImporter = $this->getSvgImporter();
$pics = $this->getPictures();
?>
<div class="css-slider" id="css-slider_<?php echo $id;?>" data-autoplay="<?php echo $opts->autoplayEnabled()?1:0; ?>">
	<?php if($opts->autoplayEnabled()): ?>
		<input id="css-slider-autoplay_<?php echo $id;?>" type="checkbox" class="css-slider-input css-slider-input-autoplay" checked>
	<?php endif; ?>
	<?php if($opts->hasFullscreen()): ?>
		<input id="css-slider-fullscreen_<?php echo $id; ?>" type="checkbox" class="css-slider-input css-slider-fullscreen-button">
	<?php endif; ?>
	<?php for($i=0;$i<count($pics);$i++): ?>
		<input name="css-slider_<?php echo $id; ?>" type="radio" class="css-slider-input css-slider-input-chooser" id="btn_picture_<?php echo $id;?>_<?php echo $i ?>" <?php echo ($i===0)?"checked":"" ?>>
	<?php endfor; ?>
	<div class="slider">
		<?php if($opts->hasFullscreen()): ?>
			<label for="css-slider-fullscreen_<?php echo $id;?>" class="full-screen-background"></label>
		<?php endif; ?>
		<ul class="picture-list">
			<?php foreach($pics as $k=>$v): ?>
				<li class="picture picture_<?php echo $k; ?>">
					<?php if($opts->hasArrows()): ?>
						<label for="btn_picture_<?php echo $id; ?>_<?php echo ($k>0)?$k-1:count($pics)-1; ?>" class="arrow arrow-prev">
							<?php echo $svgImporter->import($opts->arrowLeftIcon()); ?>
						</label>
					<?php endif; ?>
					<?php if($opts->hasFullscreen()): ?>
						<div class="full-screen-buttons">
							<label for="css-slider-fullscreen_<?php echo $id;?>" class="full-screen-on">
								<?php echo $svgImporter->import($opts->fullscreenOnIcon()); ?>
							</label>
							<label for="css-slider-fullscreen_<?php echo $id;?>" class="full-screen-off">
								<?php echo $svgImporter->import($opts->fullscreenOffIcon()); ?>
							</label>
						</div>
					<?php endif; ?>
					<?php if($opts->autoplayEnabled()): ?>
						<label for="css-slider-autoplay_<?php echo $id;?>" class="play-button">
							<?php echo $svgImporter->import($opts->autoplayIcon()); ?>
						</label>
					<?php endif; ?>
					<?php if($v->title() && $v->title()!== ""): ?>
						<p class="picture-title"><?php echo $v->title(); ?></p>
					<?php endif;?>
					<img src="<?php echo $v->path(); ?>" alt="<?php echo $v->alt(); ?>">
					<?php if($v->description() && $v->description() !== ""): ?>
						<p class="picture-description"><?php echo $v->description(); ?></p>
					<?php endif; ?>
					<?php if($opts->hasArrows()): ?>
						<label for="btn_picture_<?php echo $id; ?>_<?php echo ($k<count($pics)-1)?$k+1:0; ?>" class="arrow arrow-next">
							<?php echo $svgImporter->import($opts->arrowRightIcon()); ?>
						</label>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if($opts->hasBullets()): ?>
			<div class="bullets">
				<?php foreach($pics as $k=>$v): ?>
					<label class="bullets_<?php echo $k; ?>" for="btn_picture_<?php echo $id; ?>_<?php echo $k; ?>">
						<?php if($opts->hasBulletsPreview()): ?>
							<span class="tooltip">
								<img src="<?php echo $v->path(); ?>" alt="">
							</span>
						<?php endif; ?>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>