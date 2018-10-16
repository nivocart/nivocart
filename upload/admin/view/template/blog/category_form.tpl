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
      <h1><img src="view/image/category.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-general"><?php echo $tab_general; ?></a>
        <a href="#tab-data"><?php echo $tab_data; ?></a>
        <a href="#tab-design"><?php echo $tab_design; ?></a>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <div id="languages" class="htabs">
          <?php foreach ($languages as $language) { ?>
            <a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
          <?php } ?>
          </div>
        <?php foreach ($languages as $language) { ?>
          <div id="language<?php echo $language['language_id']; ?>">
            <table class="form">
              <tr>
                <td><span class="required">*</span> <?php echo $entry_name; ?></td>
                <td><input type="text" name="category_description[<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($category_description[$language['language_id']]) ? $category_description[$language['language_id']]['name'] : ''; ?>" size="40" />
                <?php if (isset($error_name[$language['language_id']])) { ?>
                  <span class="error"><?php echo $error_name[$language['language_id']]; ?></span>
                <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_description; ?></td>
                <td><textarea name="category_description[<?php echo $language['language_id']; ?>][meta_description]" id="meta-description<?php echo $language['language_id']; ?>" data-limit="156" cols="40" rows="5"><?php echo isset($category_description[$language['language_id']]) ? $category_description[$language['language_id']]['meta_description'] : ''; ?></textarea>
                <span id="remaining<?php echo $language['language_id']; ?>"></span></td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_keyword; ?></td>
                <td><textarea name="category_description[<?php echo $language['language_id']; ?>][meta_keyword]" cols="40" rows="5"><?php echo isset($category_description[$language['language_id']]) ? $category_description[$language['language_id']]['meta_keyword'] : ''; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo $entry_description; ?></td>
                <td><textarea name="category_description[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($category_description[$language['language_id']]) ? $category_description[$language['language_id']]['description'] : ''; ?></textarea></td>
              </tr>
            </table>
          </div>
        <?php } ?>
        </div>
        <div id="tab-data">
          <table class="form">
            <tr>
              <td><?php echo $entry_parent; ?></td>
              <td><select name="parent_id">
                <option value="0"><?php echo $text_none; ?></option>
              <?php foreach ($categories as $category) { ?>
                <?php if ($category['blog_category_id'] == $parent_id) { ?>
                  <option value="<?php echo $category['blog_category_id']; ?>" selected="selected"><?php echo $category['name']; ?></option>
                <?php } else { ?>
                  <option value="<?php echo $category['blog_category_id']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
              <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td><?php echo $entry_store; ?></td>
              <td><div class="scrollbox-store">
                <?php $class = 'even'; ?>
                <div class="<?php echo $class; ?>">
                <?php if (in_array(0, $category_store)) { ?>
                  <input type="checkbox" name="category_store[]" value="0" checked="checked" /><?php echo $text_default; ?>
                <?php } else { ?>
                  <input type="checkbox" name="category_store[]" value="0" /><?php echo $text_default; ?>
                <?php } ?>
                </div>
              <?php foreach ($stores as $store) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <?php if (in_array($store['store_id'], $category_store)) { ?>
                    <input type="checkbox" name="category_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" /><?php echo $store['name']; ?>
                  <?php } else { ?>
                    <input type="checkbox" name="category_store[]" value="<?php echo $store['store_id']; ?>" /><?php echo $store['name']; ?>
                  <?php } ?>
                </div>
              <?php } ?>
                </div>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_keyword; ?></td>
              <td><input type="text" name="keyword" value="<?php echo $keyword; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_image; ?></td>
              <td><div class="image"><img src="<?php echo $thumb; ?>" alt="" id="thumb" /><br />
                <input type="hidden" name="image" value="<?php echo $image; ?>" id="image" />
                <a onclick="image_upload('image', 'thumb');" class="button-browse"></a><a onclick="$('#thumb').attr('src', '<?php echo $no_image; ?>'); $('#image').attr('value', '');" class="button-recycle"></a>	
              </div>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_article_limit; ?></td>
              <td><input type="text" name="blog_category_column" value="<?php echo $blog_category_column; ?>" size="3" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_sort_order; ?></td>
              <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="3" /></td>
            </tr>
            <tr class="highlighted">
              <td><?php echo $entry_status; ?></td>
              <td><select name="status">
              <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
              </select></td>
            </tr>
          </table>
        </div>
        <div id="tab-design">
          <table class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_store; ?></td>
              <td class="left"><?php echo $entry_layout; ?></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="left"><?php echo $text_default; ?></td>
              <td class="left"><select name="category_layout[0][layout_id]">
                <option value=""><?php echo $text_none; ?></option>
                <?php foreach ($layouts as $layout) { ?>
                  <?php if (isset($category_layout[0]) && $category_layout[0] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
          </tbody>
        <?php foreach ($stores as $store) { ?>
          <tbody>
            <tr>
              <td class="left"><?php echo $store['name']; ?></td>
              <td class="left"><select name="category_layout[<?php echo $store['store_id']; ?>][layout_id]">
                <option value=""><?php echo $text_none; ?></option>
                <?php foreach ($layouts as $layout) { ?>
                  <?php if (isset($category_layout[$store['store_id']]) && $category_layout[$store['store_id']] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
          </tbody>
        <?php } ?>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>

<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description<?php echo $language['language_id']; ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});

$(document).ready(function() {
	$('#meta-description<?php echo $language['language_id']; ?>').on('load propertychange keyup input paste', function() {
		var limit = $(this).data("limit");
		var remain = limit - $(this).val().length;
		if (remain <= 0) {
			$(this).val($(this).val().substring(0, limit));
		}
		$('#remaining<?php echo $language['language_id']; ?>').text((remain <= 0) ? 0 : remain);
	});

	$('#meta-description<?php echo $language['language_id']; ?>').trigger('load');
});
<?php } ?>
//--></script>

<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();

	$('#content').prepend('<div id="dialog" style="padding:3px 0 0 0;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin:0; display:block; width:100%; height:100%;" frameborder="no" scrolling="auto"></iframe></div>');

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

<script type="text/javascript"><!--
$(document).ready(function() {
	var keyword = $("input[name=keyword]");

	if (!keyword.val()) {
		$("input[name^='category_description'], input[name='name']").keyup(function() {
			var SEOname = $("input[name^='category_description'], input[name='name']").val();

			SEOname = SEOname.replace(/^\s+|\s+$/g, '');
			SEOname = SEOname.toLowerCase();

			var from = "ảãạàáäâấầậẩẫẻẹẽèéëêềếệểễăắằẳẵặìíïîịơởỡợờớọõỏòóöôốộồổỗđưứừựửữùúüûủụùúũñcçčlľštžýnrrdçõã·/_,:;";
			var to = "aaaaaaaaaaaaeeeeeeeeeeeeaaaaaaiiiiiooooooooooooooooooduuuuuuuuuuuuuuuncccllstzynrrdcoa------";

			for (var i=0, l=from.length ; i<l ; i++) {
				SEOname = SEOname.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
			}

			SEOname = SEOname.replace(/[^a-z0-9( -]/g, '').replace(/\(/g,"-").replace(/\s+/g, '-').replace(/-+/g, '-');

			keyword.val(SEOname);
		});
	}
});
//--></script>

<script type="text/javascript"><!--
$('#tabs a').tabs();
$('#languages a').tabs();
//--></script>

<?php echo $footer; ?>