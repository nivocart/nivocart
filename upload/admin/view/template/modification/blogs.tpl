<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" name="blogs">
        <table class="form">
          <tr>
            <td><?php echo $entry_blog_heading; ?></td>
            <td><input type="text" name="blog_heading" value="<?php echo $blog_heading; ?>" size="50" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_related_heading; ?></td>
            <td><input type="text" name="product_related_heading" value="<?php echo $product_related_heading; ?>" size="50" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_comment_heading; ?></td>
            <td><input type="text" name="comment_related_heading" value="<?php echo $comment_related_heading; ?>" size="50" /></td>
          </tr>
        </table>
      <h2><?php echo $header_set_option; ?></h2>
        <table class="form">
          <tr>
            <td><?php echo $entry_comment_approval; ?></td>
            <td><?php if ($blog_comment_auto_approval) { ?>
              <input type="radio" name="blog_comment_auto_approval" value="1" id="approval-on" class="radio" checked />
              <label for="approval-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_comment_auto_approval" value="0" id="approval-off" class="radio" />
              <label for="approval-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="blog_comment_auto_approval" value="1" id="approval-on" class="radio" />
              <label for="approval-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_comment_auto_approval" value="0" id="approval-off" class="radio" checked />
              <label for="approval-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_show_author; ?></td>
            <td><?php if ($blog_show_authors) { ?>
              <input type="radio" name="blog_show_authors" value="1" id="authors-on" class="radio" checked />
              <label for="authors-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_show_authors" value="0" id="authors-off" class="radio" />
              <label for="authors-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="blog_show_authors" value="1" id="authors-on" class="radio" />
              <label for="authors-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_show_authors" value="0" id="authors-off" class="radio" checked />
              <label for="authors-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_author_information; ?></td>
            <td><?php if ($blog_author_information) { ?>
              <input type="radio" name="blog_author_information" value="1" id="information-on" class="radio" checked />
              <label for="information-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_author_information" value="0" id="information-off" class="radio" />
              <label for="information-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="blog_author_information" value="1" id="information-on" class="radio" />
              <label for="information-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_author_information" value="0" id="information-off" class="radio" checked />
              <label for="information-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_related_article; ?></td>
            <td><?php if ($blog_related_articles) { ?>
              <input type="radio" name="blog_related_articles" value="1" id="related-on" class="radio" checked />
              <label for="related-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_related_articles" value="0" id="related-off" class="radio" />
              <label for="related-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="blog_related_articles" value="1" id="related-on" class="radio" />
              <label for="related-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_related_articles" value="0" id="related-off" class="radio" checked />
              <label for="related-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_social_network; ?></td>
            <td><?php if ($blog_share_social_site) { ?>
              <input type="radio" name="blog_share_social_site" value="1" id="social-on" class="radio" checked />
              <label for="social-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_share_social_site" value="0" id="social-off" class="radio" />
              <label for="social-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="blog_share_social_site" value="1" id="social-on" class="radio" />
              <label for="social-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="blog_share_social_site" value="0" id="social-off" class="radio" checked />
              <label for="social-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();

	$('#content').prepend('<div id="dialog" style="padding:3px 0 0 0;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin:0; display: block; width:100%; height:100%;" frameborder="no" scrolling="auto"></iframe></div>');

	$('#dialog').dialog({
		title: '<?php echo $text_image_manager; ?>',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).val()),
					dataType: 'text',
					success: function(data) {
						$('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
					}
				});
			}
		},
		bgiframe: false,
		width: <?php echo ($this->browser->checkMobile()) ? 580 : 760; ?>,
		height: 400,
		resizable: false,
		modal: false
	});
};
//--></script>

<?php echo $footer; ?>