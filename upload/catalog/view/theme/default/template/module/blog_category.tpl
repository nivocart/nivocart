<?php if ($categories) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
    <div class="box-category">
    <ul>
    <?php foreach ($categories as $category) { ?>
      <li>
      <?php if ($category['blog_category_id'] == $category_id) { ?>
        <a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
      <?php } else { ?>
        <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
      <?php } ?>
      <?php if ($category['children']) { ?>
        <ul>
        <?php foreach ($category['children'] as $child) { ?>
          <li>
          <?php if ($child['category_id'] == $child_id) { ?>
            <a href="<?php echo $child['href']; ?>" class="active"> - <?php echo $child['name']; ?></a>
          <?php } else { ?>
            <a href="<?php echo $child['href']; ?>"> - <?php echo $child['name']; ?></a>
          <?php } ?>
          </li>
        <?php } ?>
        </ul>
      <?php } ?>
      </li>
    <?php } ?>
    </ul>
    <?php if ($this->config->get('blog_search_article')) { ?>
    <div id="blog-search" style="margin:5px 0;">
      <div>
        <input type="text" name="article_search" value="<?php echo $blog_search; ?>" placeholder="<?php echo $text_search_article; ?>" />
        <br /><br />
        <a id="button-search" class="button"><?php echo $button_search; ?></a>
      </div>
    </div>
    <?php } ?>
    </div>
  </div>
</div>
<?php } else { ?>
<div style="margin-bottom:20px;">
  <div class="box-category">
  <ul>
  <?php foreach ($categories as $category) { ?>
    <li>
    <?php if ($category['blog_category_id'] == $category_id) { ?>
      <a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
    <?php } else { ?>
      <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
    <?php } ?>
    <?php if ($category['children']) { ?>
      <ul>
      <?php foreach ($category['children'] as $child) { ?>
        <li>
        <?php if ($child['category_id'] == $child_id) { ?>
          <a href="<?php echo $child['href']; ?>" class="active"> - <?php echo $child['name']; ?></a>
        <?php } else { ?>
          <a href="<?php echo $child['href']; ?>"> - <?php echo $child['name']; ?></a>
        <?php } ?>
        </li>
      <?php } ?>
      </ul>
    <?php } ?>
    </li>
  <?php } ?>
  </ul>
  <?php if ($this->config->get('blog_search_article')) { ?>
  <div id="blog-search" style="margin:5px 0;">
    <div>
      <input type="text" name="article_search" value="<?php echo $blog_search; ?>" placeholder="<?php echo $text_search_article; ?>" />
      <br /><br />
      <a id="button-search" class="button"><?php echo $button_search; ?></a>
    </div>
  </div>
  <?php } ?>
  </div>
</div>
<?php } ?>
<?php } ?>

<script type="text/javascript"><!--
$('#blog-search input[name=\'article_search\']').keydown(function(e) {
	if (e.keyCode == 13) {
		$('#button-search').trigger('click');
	}
});

$('#button-search').bind('click', function() {
	url = 'index.php?route=simple_blog/search';

	var article_search = $('#blog-search input[name=\'article_search\']').val();

	if (article_search) {
		url += '&blog_search=' + encodeURIComponent(article_search);
	}

	location = url;
});
//--></script>