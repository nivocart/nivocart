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
      <h1><img src="view/image/review.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-general"><?php echo $tab_general; ?></a>
        <a href="#tab-comment"><?php echo $tab_comment; ?></a>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <div id="tab-general">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_author; ?></td>
            <td><input type="text" name="author_name" value="<?php echo $author_name; ?>" size="40" />
            <?php if ($error_author) { ?>
              <span class="error"><?php echo $error_author; ?></span>
            <?php } ?>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_article; ?></td>
            <td><input type="text" name="article_title" value="<?php echo $article_title; ?>" size="40" />
            <?php if ($error_article_title) { ?>
              <span class="error"><?php echo $error_article_title; ?></span>
            <?php } ?>
            </td>
          </tr>
          <tr class="highlighted">
            <td><?php echo $entry_status; ?></td>
            <td><select name="status">
              <option value="1" <?php if ($status == 1) { echo "selected='selected'"; } ?>><?php echo $text_enabled; ?></option>
              <option value="0" <?php if ($status == 0) { echo "selected='selected'"; } ?>><?php echo $text_disabled; ?></option>
            </select></td>
          </tr>
        </table>
      </div>
      <div id="tab-comment">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_comment; ?></td>
            <td><textarea name="comment" cols="40" rows="5"><?php echo $comment; ?></textarea>
            <?php if ($error_comment) { ?>
              <span class="error"><?php echo $error_comment; ?></span>
            <?php } ?>
            </td>
          </tr>
        </table>
        <table id="comment-reply" class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $entry_author; ?></td>
            <td class="left"><?php echo $entry_reply_comment; ?></td>
            <td class="left"><?php echo $entry_status; ?></td>
            <td></td>
          </tr>
        </thead>
      <?php $module_row = 0; ?>
      <?php foreach ($comment_reply as $reply) { ?>
        <tbody id="reply-<?php echo $module_row; ?>">
          <tr>
            <td class="left">
              <input type="text" name="comment_reply[<?php echo $module_row; ?>][author]" value="<?php echo $reply['author']; ?>" />
              <?php if (isset($error_reply_author[$module_row])) { ?>
                <span class="error"><?php echo $error_reply_author[$module_row]; ?></span>
              <?php } ?>
            </td>
            <td class="left">
              <textarea name="comment_reply[<?php echo $module_row; ?>][comment]" cols="20" rows="5"><?php echo $reply['comment']; ?></textarea>
              <?php if (isset($error_reply_comment[$module_row])) { ?>
                <span class="error"><?php echo $error_reply_comment[$module_row]; ?></span>
              <?php } ?>
            </td>
            <td class="left"><select name="comment_reply[<?php echo $module_row; ?>][status]">
              <option value="1" <?php if ($reply['status'] == 1) { echo "selected='selected'"; } ?>><?php echo $text_enabled; ?></option>
              <option value="0" <?php if ($reply['status'] == 0) { echo "selected='selected'"; } ?>><?php echo $text_disabled; ?></option>
            </select></td>
            <td class="center"><a onclick="$('#reply-<?php echo $module_row; ?>').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>
          </tr>
        </tbody>
      <?php $module_row++; ?>
      <?php } ?>
        <tfoot>
          <tr>
            <td colspan="3"></td>
            <td class="center"><a onclick="addReply();" class="button ripple"><?php echo $button_add_reply; ?></a></td>
          </tr>
        </tfoot>
        </table>
      </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addReply() {
	html = '<tbody id="reply-' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left">';
	html += '      <input type="text" name="comment_reply[' + module_row + '][author]" value="" />';
	html += '    </td>';
	html += '    <td class="left">';
	html += '      <textarea name="comment_reply[' + module_row + '][comment]" cols="20" rows="5"></textarea>';
	html += '    </td>';
	html += '    <td class="left"><select name="comment_reply[' + module_row + '][status]">';
	html += '      <option value="1"><?php echo $text_enabled; ?></option>';
	html += '      <option value="0"><?php echo $text_disabled; ?></option>';
	html += '    </select></td>';
	html += '    <td class="center"><a onclick="$(\'#reply-' + module_row + '\').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';

	$('#comment-reply tfoot').before(html);

	module_row++;
}
//--></script>

<script type="text/javascript"><!--
$('input[name=\'article_title\']').autocomplete({
	delay: 10,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=blog/article/autocomplete&token=<?php echo $token; ?>&article_name=' + encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.blog_article_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('input[name=\'article_title\']').val(ui.item.label);

		return false;
	},
	focus: function(event, ui) {
		return false;
	}
});
//--></script>

<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>

<?php echo $footer; ?>