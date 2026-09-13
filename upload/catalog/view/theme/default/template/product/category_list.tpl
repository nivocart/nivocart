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
  <?php if ($categories) { ?>
  <h1><?php echo $heading_title; ?></h1>
  <div class="category-page">
    <?php foreach ($categories as $category) { ?>
    <div>
      <?php if ($category['thumb']) { ?>
      <div class="image">
        <a href="<?php echo $category['href']; ?>">
          <img src="<?php echo $category['thumb']; ?>" alt="<?php echo $category['name']; ?>" />
        </a>
      </div>
      <?php } ?>
      <div class="info">
        <div class="name"><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></div>
        <?php if ($category['description']) { ?>
        <div class="description"><?php echo $category['description']; ?></div>
        <?php } ?>
        <?php if ($category['children']) { ?>
        <div class="category-pills">
          <?php foreach ($category['children'] as $child) { ?>
          <a href="<?php echo $child['href']; ?>" class="category-pill"><?php echo $child['name']; ?></a>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } else { ?>
    <div class="content"><?php echo $text_empty; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>