<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="carousel-wrap">
    <div class="slick_skin" id="carousel<?php echo $module; ?>">
      <?php foreach ($banners as $banner) { ?>
        <?php if ($banner['link']) { ?>
          <div class="carousel-swipe"><a onclick="addClick('<?php echo $banner['banner_image_id']; ?>');" href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" /></a></div>
        <?php } else { ?>
          <div class="carousel-swipe"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" id="<?php echo $banner['banner_image_id']; ?>" /></div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } else { ?>
  <div class="carousel-wrap">
    <div class="slick_skin" id="carousel<?php echo $module; ?>">
      <?php foreach ($banners as $banner) { ?>
        <?php if ($banner['link']) { ?>
          <div class="carousel-swipe"><a onclick="addClick('<?php echo $banner['banner_image_id']; ?>');" href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" /></a></div>
        <?php } else { ?>
          <div class="carousel-swipe"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" id="<?php echo $banner['banner_image_id']; ?>" /></div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
<?php } ?>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#carousel<?php echo $module; ?>').slick({
		arrows: true,
		autoplay: <?php echo $auto; ?>,
		autoplaySpeed: <?php echo $duration; ?>,
		pauseOnHover: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		infinite: true,
		speed: <?php echo $speed; ?>,
		cssEase: 'cubic-bezier(0.87, 0, 0.13, 1)',
		respondTo: 'slider',
		mobileFirst: true,
		swipe: true,
		rtl: false,
		dots: false,
		responsive: <?php echo $responsive; ?>
	});
});
--></script>
