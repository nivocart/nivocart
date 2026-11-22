<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <div class="gallery-info">
    <div class="gallery-photo">
      <img src="<?php echo $image; ?>" srcset="<?php echo $image; ?> 600w, <?php echo $image_2x; ?> 900w" sizes="(max-width: 640px) 100vw, 600px" width="600" height="600" alt="<?php echo $alt; ?>" />
    </div>
    <?php if ($description) { ?>
      <p class="gallery-about"><?php echo $description; ?></p>
    <?php } ?>
    <p class="gallery-credit"><?php echo $text_credit; ?> &middot; <a href="<?php echo $agreement; ?>"><?php echo $text_agreement; ?></a></p>
    <div class="gallery-nav">
      <?php if ($previous) { ?>
        <a href="<?php echo $previous; ?>" class="button-grey" rel="prev"><i class="fa fa-chevron-left"></i> &nbsp;<?php echo $text_previous; ?></a>
      <?php } ?>
      <a href="<?php echo $back; ?>" class="button"><?php echo $text_back; ?></a>
      <a href="<?php echo $submit; ?>" class="button"><i class="fa fa-camera"></i> &nbsp;<?php echo $text_submit; ?></a>
      <?php if ($next) { ?>
        <a href="<?php echo $next; ?>" class="button-grey" rel="next"><?php echo $text_next; ?> &nbsp;<i class="fa fa-chevron-right"></i></a>
      <?php } ?>
    </div>
  </div>
  <script type="application/ld+json"><?php echo $json_ld; ?></script>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>