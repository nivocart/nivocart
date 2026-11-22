<?php if ($images || $month) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content gallery-module">
<?php } else { ?>
  <div class="gallery-module">
<?php } ?>
    <?php if ($month) { ?>
      <div class="gallery-month">
        <a href="<?php echo $month['href']; ?>"><img src="<?php echo $month['thumb']; ?>" alt="<?php echo $month['name']; ?>" title="<?php echo $month['name']; ?>" /></a>
        <div class="gallery-month-label"><?php echo $month['label']; ?></div>
        <div class="gallery-month-name"><a href="<?php echo $month['href']; ?>"><?php echo $month['name']; ?></a></div>
      </div>
    <?php } ?>
    <?php if ($images) { ?>
      <div id="gallery-module<?php echo $module; ?>" class="flexslider loading">
        <ul class="slides">
        <?php foreach ($images as $image) { ?>
          <li><a href="<?php echo $image['href']; ?>"><img src="<?php echo $image['thumb']; ?>" alt="<?php echo $image['name']; ?>" title="<?php echo $image['name']; ?>" /></a></li>
        <?php } ?>
        </ul>
      </div>
    <?php } ?>
    <?php if ($links) { ?>
      <div class="gallery-module-links">
        <a href="<?php echo $view; ?>" class="button"><i class="fa fa-photo"></i> &nbsp; <?php echo $text_view; ?></a>
        <a href="<?php echo $submit; ?>" class="button"><i class="fa fa-camera"></i> &nbsp; <?php echo $text_submit; ?></a>
      </div>
    <?php } ?>
<?php if ($theme) { ?>
  </div>
</div>
<?php } else { ?>
  </div>
<?php } ?>
<?php if ($images) { ?>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#gallery-module<?php echo $module; ?>').flexslider({
		animation: '<?php echo $animation; ?>',
		direction: '<?php echo $direction; ?>',
		animationSpeed: <?php echo $speed; ?>,
		slideshowSpeed: <?php echo $duration; ?>,
		animationLoop: true,
		controlNav: false,
		directionNav: false,
		pauseOnHover: true,
		pauseOnAction: false,
		smoothHeight: false,
		useCSS: true,
		touch: true,
		start: function(slider) {
			slider.removeClass('loading');
		}
	});
});
//--></script>
<?php } ?>
<?php } ?>