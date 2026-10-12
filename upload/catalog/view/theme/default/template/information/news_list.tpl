<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
  <?php foreach ($breadcrumbs as $key => $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
      <a href="<?php echo $breadcrumb['href']; ?>" itemprop="item"><span itemprop="name"><?php echo $breadcrumb['text']; ?></span></a>
      <meta itemprop="position" content="<?php echo $key + 1; ?>" />
    </span>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($news_data) { ?>
    <div class="content">
    <?php foreach ($news_data as $news) { ?>
      <div class="panel-collapsed">
        <h3><?php echo $news['title']; ?><i class="fa"></i></h3>
        <div class="panel-content">
          <?php echo $news['description']; ?><br /><br />
          <i class="fa fa-calendar"></i> &nbsp; <?php echo $news['date_added']; ?> - <?php echo $news['viewed']; ?> <?php echo $text_views; ?><br /><br />
          <a href="<?php echo $news['href']; ?>" class="button"><i class="fa fa-mail-forward"></i> &nbsp; <?php echo $button_read; ?></a>
        </div>
      </div>
    <?php } ?>
    </div>
  <?php } else { ?>
    <div class="content"><?php echo $text_no_results; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>