<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<script type="application/ld+json"><?php echo $schema; ?></script>
<div id="content">
  <h1><?php echo $heading_title; ?></h1>
  <div class="watch-player <?php echo $video_shape; ?>">
    <video id="watch-video" controls playsinline preload="metadata" poster="<?php echo $video_poster; ?>"<?php if ($video_width && $video_height) { ?> width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>"<?php } ?>>
      <source src="<?php echo $video_file; ?>" type="<?php echo $video_type; ?>" />
    </video>
  </div>
  <div class="watch-details">
    <div class="watch-buy">
      <?php if ($price) { ?>
        <span class="watch-price">
          <?php if ($price_from) { ?>
            <small><?php echo $text_from; ?></small>
          <?php } ?>
          <?php if ($price_old) { ?>
            <span class="watch-price-old"><?php echo $price_old; ?></span>
          <?php } ?>
          <?php echo $price; ?>
        </span>
      <?php } ?>
      <a href="<?php echo $product; ?>" class="button"><?php echo $button_view_product; ?></a>
    </div>
    <?php if ($options) { ?>
      <div class="watch-options">
        <h2><?php echo $text_options; ?></h2>
        <ul>
        <?php foreach ($options as $option) { ?>
          <li><a href="<?php echo $option['href']; ?>" title="<?php echo $option['title']; ?>"><?php echo $option['name']; ?></a></li>
        <?php } ?>
        </ul>
      </div>
    <?php } ?>
    <?php if ($description) { ?>
      <p class="watch-description"><?php echo $description; ?></p>
    <?php } ?>
    <p class="watch-more">
      <a href="<?php echo $videos_page; ?>" class="button"><?php echo $text_all_videos; ?></a>
    </p>
  </div>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {
	if (typeof Plyr !== 'undefined') {
		new Plyr('#watch-video', {
			controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
			iconUrl: '<?php echo $video_icons; ?>',
			clickToPlay: true
		});
	}
});
//--></script>

<?php echo $footer; ?>