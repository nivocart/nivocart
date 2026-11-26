<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<div id="content">
  <h1><?php echo $heading_title; ?></h1>
  <p class="video-intro"><?php echo $text_intro; ?></p>
  <div class="video-grid">
  <?php foreach ($videos as $video) { ?>
    <div class="video-card">
      <a href="<?php echo $video['href']; ?>" class="video-card-image" title="<?php echo $text_play; ?>">
        <img src="<?php echo $video['thumb']; ?>" width="300" height="300" alt="<?php echo $video['name']; ?>" loading="lazy" />
        <i class="fa fa-play-circle" aria-hidden="true"></i>
        <?php if ($video['duration']) { ?>
          <span class="video-card-time"><?php echo $video['duration']; ?></span>
        <?php } ?>
      </a>
      <div class="video-card-name">
        <a href="<?php echo $video['href']; ?>"><?php echo $video['name']; ?></a>
      </div>
    </div>
  <?php } ?>
  </div>
  <?php if ($pagination) { ?>
    <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
</div>
<?php echo $footer; ?>