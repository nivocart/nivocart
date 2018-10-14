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
      <h1><img src="view/image/order.png" alt="" /> <?php echo $heading_title; ?></h1>
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
        <a href="#tab-option"><?php echo $tab_option; ?></a>
        <a href="#tab-related"><?php echo $tab_related; ?></a>
        <a href="#tab-design"><?php echo $tab_design; ?></a>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <div id="tab-general">
        <div id="languages" class="htabs">
        <?php foreach ($languages as $language) { ?>
          <a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
        <?php } ?>
        </div>
      <?php foreach ($languages as $language) { ?>
        <div id="language<?php echo $language['language_id']; ?>">
          <table class="form">
            <tr>
              <td class="left" width="25%"><span class="required">*</span> <?php echo $entry_title; ?></td>
              <td><input type="text" name="article_description[<?php echo $language['language_id']; ?>][article_title]" value="<?php echo isset($article_description[$language['language_id']]['article_title']) ? $article_description[$language['language_id']]['article_title'] : ''; ?>" size="40" />
              <?php if (isset($error_article_title[$language['language_id']])) { ?>
                <span class="error"><?php echo $error_article_title[$language['language_id']]; ?></span>
              <?php } ?>
              </td>
            </tr>
            <tr>
              <td class="left"><?php echo $entry_meta_description; ?></td>
              <td><textarea name="article_description[<?php echo $language['language_id']; ?>][meta_description]" id="meta-description<?php echo $language['language_id']; ?>" data-limit="156" cols="40" rows="5"><?php echo isset($article_description[$language['language_id']]['meta_description']) ? $article_description[$language['language_id']]['meta_description'] : ''; ?></textarea>
              <span id="remaining<?php echo $language['language_id']; ?>"></span></td>
            </tr>
            <tr>
              <td class="left"> <?php echo $entry_meta_keyword; ?></td>
              <td><textarea name="article_description[<?php echo $language['language_id']; ?>][meta_keyword]" cols="40" rows="5"><?php echo isset($article_description[$language['language_id']]['meta_keyword']) ? $article_description[$language['language_id']]['meta_keyword'] : ''; ?></textarea></td>
            </tr>
            <tr>
              <td class="left"><span class="required">*</span> <?php echo $entry_description; ?></td>
              <td><textarea name="article_description[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($article_description[$language['language_id']]['description']) ? $article_description[$language['language_id']]['description'] : ''; ?></textarea>
              <?php if (isset($error_description[$language['language_id']])) { ?>
                <span class="error"><?php echo $error_description[$language['language_id']]; ?></span>
              <?php } ?>
              </td>
            </tr>
          </table>
        </div>
      <?php } ?>
      </div>
      <div id="tab-option">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_author_name; ?></td>
            <td><input type="text" name="author_name" value="<?php echo $author_name; ?>" size="40" />
              <input type="hidden" name="blog_author_id" value="<?php echo $blog_author_id; ?>" />
            <?php if ($error_author_name) { ?>
              <span class="error"><?php echo $error_author_name; ?></span>
            <?php } ?>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_keyword; ?></td>
            <td><input type="text" name="keyword" value="<?php echo $keyword; ?>" size="40" />
            <?php if ($error_seo_keyword) { ?>
              <span class="error"><?php echo $error_seo_keyword; ?></span>
            <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_category; ?></td>
            <td><div class="scrollbox" style="width:500px;">
              <?php $class = 'odd'; ?>
              <?php foreach ($categories as $category) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                <?php if (in_array($category['blog_category_id'], $article_category)) { ?>
                  <input type="checkbox" name="article_category[]" value="<?php echo $category['blog_category_id']; ?>" checked="checked" /><?php echo $category['name']; ?>
                <?php } else { ?>
                  <input type="checkbox" name="article_category[]" value="<?php echo $category['blog_category_id']; ?>" /><?php echo $category['name']; ?>
                <?php } ?>
                </div>
              <?php } ?>
              </div>
			  <br />
              <a onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="button-select"></a><a onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="button-unselect"></a>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_store; ?></td>
            <td><div class="scrollbox-store">
              <?php $class = 'odd'; ?>
                <div class="<?php echo $class; ?>">
                <?php if (in_array(0, $article_store)) { ?>
                  <input type="checkbox" name="article_store[]" value="0" checked="checked" /><?php echo $text_default; ?>
                <?php } else { ?>
                  <input type="checkbox" name="article_store[]" value="0" /><?php echo $text_default; ?>
                <?php } ?>
                </div>
              <?php foreach ($stores as $store) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                <?php if (in_array($store['store_id'], $article_store)) { ?>
                  <input type="checkbox" name="article_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" /><?php echo $store['name']; ?>
                <?php } else { ?>
                  <input type="checkbox" name="article_store[]" value="<?php echo $store['store_id']; ?>" /><?php echo $store['name']; ?>
                <?php } ?>
                </div>
              <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="button-select"></a><a onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="button-unselect"></a>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_allow_comment; ?></td>
            <td><select name="allow_comment">
              <option value="1" <?php if ($allow_comment == 1) { echo "selected='selected'"; } ?>><?php echo $text_yes; ?></option>
              <option value="0" <?php if ($allow_comment == 0) { echo "selected='selected'"; } ?>><?php echo $text_no; ?></option>
            </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="3" /></td>
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
      <div id="tab-data">
        <table class="form">
          <tr>
            <td><?php echo $entry_image; ?></td>
            <td><div class="image"><img src="<?php echo $thumb; ?>" alt="" id="thumb" /><br />
              <input type="hidden" name="image" value="<?php echo $image; ?>" id="image" />
              <a onclick="image_upload('image', 'thumb');" class="button-browse"></a><a onclick="$('#thumb').attr('src', '<?php echo $no_image; ?>'); $('#image').attr('value', '');" class="button-recycle"></a>	
            </div>
            </td>
          </tr>
        </table>
        <table id="additional" class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $entry_additional_description; ?></td>
            <td></td>
          </tr>
        </thead>
      <?php $module_row = 0; ?>
      <?php foreach ($article_additional_description as $additional_description) { ?>
        <tbody id="additional-description<?php echo $module_row; ?>">
          <tr>
            <td class="left">
            <?php foreach ($languages as $language) { ?>
              <div id="">
                <textarea name="article_additional_description[<?php echo $module_row ?>][<?php echo $language['language_id']; ?>][additional]" id="description-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>"><?php if (isset($additional_description[$language['language_id']]['additional'])) { echo $additional_description[$language['language_id']]['additional']; } else { echo ""; } ?></textarea>
                <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" align="top" />
              </div>
            <?php } ?>
            </td>
            <td class="right"><a onclick="$('#additional-description<?php echo $module_row; ?>').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>
          </tr>
        </tbody>
        <?php $module_row++; ?>
        <?php } ?>
        <tfoot>
          <tr>
            <td></td>
            <td class="right"><a onclick="addDescription();" class="button"><?php echo $button_add_description; ?></a></td>
          </tr>
        </tfoot>
        </table>
      </div>
      <div id="tab-related">
        <table class="form">
          <tr>
            <td><?php echo $entry_article_related_method; ?></td>
            <td>
              <select name="related_article" onchange="getRelatedMethod(this.value);">
                <option value="category_wise" <?php if ($related_article == 'category_wise') { echo "selected='selected'"; } ?>><?php echo $entry_category_wise; ?></option>
                <option value="manufacturer_wise" <?php if ($related_article == 'manufacturer_wise') { echo "selected='selected'"; } ?>><?php echo $entry_manufacturer_wise; ?></option>
                <option value="product_wise" <?php if ($related_article == 'product_wise') { echo "selected='selected'"; } ?>><?php echo $entry_product_wise; ?></option>
              </select>
            </td>
          </tr>
          <tr id="category-wise" style="display:none">
            <td><?php echo $entry_category; ?></td>
            <td>
              <div class="scrollbox" style="width:350px;">
              <?php $class = 'odd'; ?>
              <?php foreach ($default_categories as $category) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <input type="checkbox" name="category_wise[]" value="<?php echo $category['category_id']; ?>" <?php if (isset($category_ids)) { for ($i = 0; $i < count($category_ids); $i++) { if ($category_ids[$i] == $category['category_id']) { echo "checked='checked'"; } } } ?> />
                  <?php echo $category['name']; ?>
                </div>
              <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="button-select"></a><a onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="button-unselect"></a>
            </td>
          </tr>
          <tr id="manufacturer-wise" style="display:none">
            <td><?php echo $entry_manufacturer; ?></td>
            <td>
              <div class="scrollbox" style="width:350px;">
              <?php $class = 'odd'; ?>
              <?php foreach ($default_manufacturers as $manufacturer) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <input type="checkbox" name="manufacturer_wise[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if (isset($manufacturer_ids)) { for ($i = 0; $i < count($manufacturer_ids); $i++) { if ($manufacturer_ids[$i] == $manufacturer['manufacturer_id']) { echo "checked='checked'"; } } } ?> />
                  <?php echo $manufacturer['name']; ?>
                </div>
              <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="button-select"></a><a onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="button-unselect"></a>
            </td>
          </tr>
          <tr id="product-wise" style="display:none">
            <td><?php echo $entry_product; ?></td>
            <td>
              <table>
                <tr>
                  <td><?php echo $entry_productwise; ?><br />
                    <input type="text" name="product" value="" />
                  </td>
                  <td>&nbsp;&nbsp;</td>
                  <td>
                    <div class="scrollbox" id="product-wise-list" style="width:350px;">
                    <?php $class = 'odd'; ?>
                    <?php if (isset($products)) { ?>
                      <?php foreach ($products as $product) { ?>
                        <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                        <div id="product-wise-list<?php echo $product['product_id']; ?>" class="<?php echo $class; ?>"> <?php echo $product['name']; ?><img src="view/image/delete.png" alt="" />
                          <input type="hidden" name="product_wise[]" value="<?php echo $product['product_id']; ?>" />
                        </div>
                      <?php } ?>
                    <?php } ?>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      <h2><?php echo $entry_blog_related_articles; ?></h2>
        <table id="related-article" class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $entry_related_article_name; ?></td>
            <td class="left"><?php echo $entry_sort_order; ?></td>
            <td class="left"><?php echo $entry_status; ?></td>
            <td></td>
          </tr>
        </thead>
      <?php $article_row = 0; ?>
      <?php foreach ($blog_related_articles as $related_articles) { ?>
        <tbody id="article-row<?php echo $article_row; ?>">
          <tr>
            <td class="left">
              <input type="text" name="blog_related_articles[<?php echo $article_row; ?>][article_title]" value="<?php echo $related_articles['article_title']; ?>" id="article-title-<?php echo $article_row; ?>" onkeyup="getArticles(<?php echo $article_row; ?>, this.value);" />
              <input type="hidden" name="blog_related_articles[<?php echo $article_row; ?>][blog_article_related_id]" value="<?php echo $related_articles['blog_article_related_id']; ?>" />
            </td>
            <td class="left">
              <input type="text" name="blog_related_articles[<?php echo $article_row; ?>][sort_order]" value="<?php echo $related_articles['sort_order']; ?>" />
            </td>
            <td class="left">
              <select name="blog_related_articles[<?php echo $article_row; ?>][status]">
              <?php if ($related_articles['status']) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
              </select>
            </td>
            <td class="left"><a onclick="$('#article-row<?php echo $article_row; ?>').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>
          </tr>
        </tbody>
      <?php $article_row++; ?>
      <?php } ?>
        <tfoot>
          <tr>
            <td colspan="3"></td>
            <td class="left"><a onclick="addArticles();" class="button ripple"><?php echo $button_add_articles; ?></a></td>
          </tr>
        </tfoot>
        </table>
      </div>
      <div id="tab-design">
        <table class="form">
          <tr>
            <td class="left"><?php echo $entry_store; ?></td>
            <td class="left"><?php echo $entry_layout; ?></td>
          </tr>
          <tr>
            <td class="left"><?php echo $text_default; ?></td>
            <td class="left">
              <select name="article_layout[0][layout_id]">
                <option value=""><?php echo $text_none; ?></option>
                <?php foreach ($layouts as $layout) { ?>
                  <?php if (isset($article_layout[0]) && $article_layout[0] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
        <?php foreach ($stores as $store) { ?>
          <tr>
            <td class="left"><?php echo $store['name']; ?></td>
            <td class="left">
              <select name="article_layout[<?php echo $store['store_id']; ?>][layout_id]">
                <option value=""><?php echo $text_none; ?></option>
                <?php foreach ($layouts as $layout) { ?>
                  <?php if (isset($article_layout[$store['store_id']]) && $article_layout[$store['store_id']] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
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

<?php $module_row = 0; ?>
<?php foreach ($article_additional_description as $module) { ?>
CKEDITOR.replace('description-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php $module_row++; ?>
<?php } ?>

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
var module_row = <?php echo $module_row; ?>;

function addDescription() {
	html  = '<tbody id="additional-description' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left">';
	<?php foreach ($languages as $language) { ?>
	html += '      <div id="">'
	html += '        <textarea name="article_additional_description[' + module_row + '][<?php echo $language['language_id']; ?>][additional]" id="description-' + module_row + '-<?php echo $language['language_id']; ?>"></textarea> <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" alt="" align="top" />';
	html += '      </div>';
	<?php } ?>
	html += '    </td>';
	html += '    <td class="right"><a onclick="$(\'#additional-description' + module_row + '\').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';

	$('#additional tfoot').before(html);

<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description-' + module_row + '-<?php echo $language['language_id']; ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>

	module_row++;
}
//--></script>

<script type="text/javascript"><!--
$('input[name=\'author_name\']').autocomplete({
	delay: 10,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=blog/author/autocomplete&token=<?php echo $token; ?>&author_name=' + encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.blog_author_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('input[name=\'author_name\']').val(ui.item.label);
		$('input[name=\'blog_author_id\']').val(ui.item.value);

		return false;
	},
	focus: function(event, ui) {
		return false;
	}
});
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
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
					dataType: 'text',
					success: function(text) {
						$('#' + thumb).replaceWith('<img src="' + text + '" alt="" id="' + thumb + '" />');
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
$('input[name=\'product\']').autocomplete({
	delay: 10,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' + encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('#product-wise-list' + ui.item.value).remove();

		$('#product-wise-list').append('<div id="product-wise-list' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" alt="" /><input type="hidden" name="product_wise[]" value="' + ui.item.value + '" /></div>');

		$('#product-wise-list div:odd').attr('class', 'odd');
		$('#product-wise-list div:even').attr('class', 'even');

		$('input[name=\'product\']').val('');

		return false;
	}
});

$('#product-wise-list div img').live('click', function() {
	$(this).parent().remove();

	$('#product-wise-list div:odd').attr('class', 'odd');
	$('#product-wise-list div:even').attr('class', 'even');
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
<?php if ($related_article == 'product_wise') { ?>
	$("#category-wise").css({display: "none"});
	$("#manufacturer-wise").css({display: "none"});
	$("#product-wise").css({display: "table-row"});
<?php } else if ($related_article == 'category_wise') { ?>
	$("#category-wise").css({display: "table-row"});
	$("#manufacturer-wise").css({display: "none"});
	$("#product-wise").css({display: "none"});
<?php } else if ($related_article == 'manufacturer_wise') { ?>
	$("#category-wise").css({display: "none"});
	$("#manufacturer-wise").css({display: "table-row"});
	$("#product-wise").css({display: "none"});
<?php } ?>
});
//--></script>

<script type="text/javascript"><!--
function getRelatedMethod(value) {
	if (value == 'product_wise') {
		$("#category-wise").css({display: "none"});
		$("#manufacturer-wise").css({display: "none"});
		$("#product-wise").css({display: "table-row"});
	} else if (value == 'category_wise') {
		$("#category-wise").css({display: "table-row"});
		$("#manufacturer-wise").css({display: "none"});
		$("#product-wise").css({display: "none"});
	} else if (value == 'manufacturer_wise') {
		$("#category-wise").css({display: "none"});
		$("#manufacturer-wise").css({display: "table-row"});
		$("#product-wise").css({display: "none"});
	}
}
//--></script>

<script type="text/javascript"><!--
var article_row = <?php echo $article_row; ?>;

function addArticles() {
	html = '<tbody id="article-row' + article_row + '">';
	html += '  <tr>';
	html += '    <td class="left">';
	html += '      <input type="text" name="blog_related_articles[' + article_row + '][article_title]" value="" id="article-title-' + article_row + '" onkeyup="getArticles(' + article_row + ', this.value);" /> <input type="hidden" name="blog_related_articles[' + article_row + '][blog_article_related_id]" value="0" />';
	html += '    </td>';
	html += '    <td class="left">';
	html += '      <input type="text" name="blog_related_articles[' + article_row + '][sort_order]" value="" />';
	html += '    </td>';
	html += '    <td class="left">';
	html += '      <select name="blog_related_articles[' + article_row + '][status]">';
	html +='         <option value="1"><?php echo $text_enabled; ?></option>';
	html +='         <option value="0"><?php echo $text_disabled; ?></option>';
	html += '      </select>';
	html += '    </td>';
	html += '    <td class="left"><a onclick="$(\'#article-row' + article_row + '\').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>';
	html += '	</tr>';
	html += '</tbody>';

	$('#related-article tfoot').before(html);

	article_row++;
}
//--></script>

<script type="text/javascript"><!--
function getArticles(article_row, value) {
	$('input[name=\'blog_related_articles[' + article_row + '][article_title]\']').autocomplete({
		delay: 10,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=blog/article/autocomplete_article&token=<?php echo $token; ?>&blog_article_id=<?php echo $blog_article_id; ?>&filter_name=' + encodeURIComponent(request.term),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item.article_title,
							value: item.blog_article_id
						}
					}));
				}
			});
		},
		select: function(event, ui) {
			$('input[name=\'blog_related_articles[' + article_row + '][article_title]\']').val(ui.item.label);
			$('input[name=\'blog_related_articles[' + article_row + '][blog_article_related_id]\']').val(ui.item.value);

			return false;
		},
		focus: function(event, ui) {
			return false;
		}
	});
}
//--></script>

<script type="text/javascript"><!--
$('#tabs a').tabs();
$('#languages a').tabs();
//--></script>

<?php echo $footer; ?>