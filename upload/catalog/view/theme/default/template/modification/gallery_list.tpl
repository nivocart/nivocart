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
  <?php if ($description) { ?>
    <div class="gallery-description"><?php echo $description; ?></div>
  <?php } ?>
  <div class="gallery-filter">
    <div class="gallery-types">
    <?php foreach ($types as $type) { ?>
      <a href="<?php echo $type['href']; ?>" class="gallery-type<?php echo $type['active'] ? ' active' : ''; ?>"><?php echo $type['text']; ?></a>
    <?php } ?>
    </div>
    <div class="gallery-tools">
      <span class="gallery-display">
        <a onclick="galleryDisplay('grid');" id="gallery-view-grid" title="<?php echo $text_grid; ?>"><i class="fa fa-th-large"></i></a>
        <a onclick="galleryDisplay('list');" id="gallery-view-list" title="<?php echo $text_list; ?>"><i class="fa fa-list"></i></a>
      </span>
      <span class="gallery-limit"><span class="hide-phone"><?php echo $text_limit; ?></span>
        <select onchange="location = this.value;">
        <?php foreach ($limits as $limit) { ?>
          <option value="<?php echo $limit['href']; ?>"<?php echo $limit['selected'] ? ' selected="selected"' : ''; ?>><?php echo $limit['text']; ?></option>
        <?php } ?>
        </select>
      </span>
      <a href="<?php echo $submit; ?>" class="button"><i class="fa fa-camera"></i> &nbsp;<?php echo $text_submit; ?></a>
    </div>
  </div>
  <?php if ($images) { ?>
  <div class="gallery-items gallery-grid" id="gallery-items">
  <?php foreach ($images as $image) { ?>
    <div class="gallery-item">
      <a href="<?php echo $image['href']; ?>" class="gallery-image"><img src="<?php echo $image['thumb']; ?>"<?php if ($image['thumb_2x']) { ?> srcset="<?php echo $image['thumb']; ?> 300w, <?php echo $image['thumb_2x']; ?> 600w" sizes="(max-width: 640px) 45vw, 300px"<?php } ?> width="300" height="300" alt="<?php echo $image['alt']; ?>" title="<?php echo $image['name']; ?>" loading="lazy" /></a>
      <div class="gallery-caption">
        <div class="gallery-name"><a href="<?php echo $image['href']; ?>"><?php echo $image['name']; ?></a></div>
        <div class="gallery-kind"><?php echo $image['type']; ?></div>
        <?php if ($image['description']) { ?>
          <div class="gallery-text"><?php echo $image['description']; ?></div>
        <?php } ?>
      </div>
    </div>
  <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
    <div class="content"><?php echo $text_empty; ?></div>
  <?php } ?>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
function galleryDisplay(view) {
	if (view !== 'list') {
		view = 'grid';
	}

	$('#gallery-items').removeClass('gallery-grid gallery-list').addClass('gallery-' + view);
	$('.gallery-display a').removeClass('active');
	$('#gallery-view-' + view).addClass('active');

	try {
		localStorage.setItem('gallery_display', view);
	} catch (e) {}
}

var galleryView = 'grid';

try {
	galleryView = localStorage.getItem('gallery_display') || 'grid';
} catch (e) {}

galleryDisplay(galleryView);
//--></script>

<?php echo $footer; ?>