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
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/review.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-submissions"><?php echo $tab_submissions; ?></a>
        <a href="#tab-settings"><?php echo $tab_settings; ?></a>
      </div>
      <div id="tab-submissions">
        <div class="tooltip"><?php echo $text_info; ?></div>
        <p><?php echo $text_pending_total; ?></p>
        <p>
          <a onclick="gallery('<?php echo $approve; ?>');" class="button button-align ripple"><i class="fa fa-check"></i> &nbsp; <?php echo $button_approve; ?></a>
          <a onclick="gallery('<?php echo $pending; ?>');" class="button button-align ripple"><i class="fa fa-clock-o"></i> &nbsp; <?php echo $button_pending; ?></a>
          <a onclick="gallery('<?php echo $refuse; ?>');" class="button button-align ripple"><i class="fa fa-ban"></i> &nbsp; <?php echo $button_refuse; ?></a>
          <a id="gallery-delete" class="button-delete button-align ripple"><?php echo $button_delete; ?></a>
        </p>
        <?php if ($navigation_hi) { ?>
          <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
        <?php } ?>
        <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-list">
          <table class="list">
            <thead>
              <tr>
                <td width="1" style="text-align:center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
                <label for="check-all"><span></span></label></td>
                <td class="center"><?php echo $column_image; ?></td>
                <td class="left"><?php if ($sort === 'pet_name') { ?>
                  <a href="<?php echo $sort_pet; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_pet; ?></a>
                <?php } else { ?>
                  <a href="<?php echo $sort_pet; ?>"><?php echo $column_pet; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
                <?php } ?></td>
                <td class="left"><?php if ($sort === 'filter_id') { ?>
                  <a href="<?php echo $sort_type; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_type; ?></a>
                <?php } else { ?>
                  <a href="<?php echo $sort_type; ?>"><?php echo $column_type; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
                <?php } ?></td>
                <td class="left"><?php if ($sort === 'customer_name') { ?>
                  <a href="<?php echo $sort_submitter; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_submitter; ?></a>
                <?php } else { ?>
                  <a href="<?php echo $sort_submitter; ?>"><?php echo $column_submitter; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
                <?php } ?></td>
                <td class="left"><?php if ($sort === 'date_added') { ?>
                  <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
                <?php } else { ?>
                  <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
                <?php } ?></td>
                <td class="center"><?php if ($sort === 'status') { ?>
                  <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                <?php } else { ?>
                  <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
                <?php } ?></td>
                <td class="right"><?php echo $column_action; ?></td>
              </tr>
            </thead>
            <tbody>
              <tr class="filter">
                <td></td>
                <td></td>
                <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="center"><select name="filter_status">
                  <option value=""><?php echo $text_all; ?></option>
                  <option value="0"<?php echo ($filter_status === '0') ? ' selected="selected"' : ''; ?>><?php echo $text_pending; ?></option>
                  <option value="1"<?php echo ($filter_status === '1') ? ' selected="selected"' : ''; ?>><?php echo $text_approved; ?></option>
                  <option value="2"<?php echo ($filter_status === '2') ? ' selected="selected"' : ''; ?>><?php echo $text_refused; ?></option>
                </select></td>
                <td class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a></td>
              </tr>
            <?php if ($images) { ?>
              <?php foreach ($images as $image) { ?>
              <tr>
                <td style="text-align:center;"><?php if ($image['selected']) { ?>
                  <input type="checkbox" name="selected[]" value="<?php echo $image['gallery_id']; ?>" id="gallery-<?php echo $image['gallery_id']; ?>" class="checkbox" checked />
                  <label for="gallery-<?php echo $image['gallery_id']; ?>"><span></span></label>
                <?php } else { ?>
                  <input type="checkbox" name="selected[]" value="<?php echo $image['gallery_id']; ?>" id="gallery-<?php echo $image['gallery_id']; ?>" class="checkbox" />
                  <label for="gallery-<?php echo $image['gallery_id']; ?>"><span></span></label>
                <?php } ?></td>
                <td class="center"><?php if ($image['thumb']) { ?>
                  <a href="<?php echo $image['edit']; ?>"><img src="<?php echo $image['thumb']; ?>" alt="" width="60" height="60" style="border-radius:4px;" /></a>
                <?php } ?></td>
                <td class="left"><?php echo $image['pet_name']; ?></td>
                <td class="left"><?php echo $image['type']; ?></td>
                <td class="left"><?php echo $image['customer_name']; ?><br /><span class="help"><?php echo $image['email']; ?></span></td>
                <td class="left"><?php echo $image['date_added']; ?></td>
                <?php if ($image['status'] === 1) { ?>
                  <td class="center"><span class="enabled"><?php echo $text_approved; ?></span></td>
                <?php } elseif ($image['status'] === 2) { ?>
                  <td class="center"><span class="missed"><?php echo $text_refused; ?></span></td>
                <?php } else { ?>
                  <td class="center"><span class="disabled"><?php echo $text_pending; ?></span></td>
                <?php } ?>
                <td class="right"><a href="<?php echo $image['edit']; ?>" class="button-form animated fadeIn ripple"><?php echo $button_edit; ?></a></td>
              </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </form>
        <?php if ($navigation_lo) { ?>
          <div class="pagination"><?php echo $pagination; ?></div>
        <?php } ?>
      </div>
      <div id="tab-settings">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
          <table class="form">
            <tr>
              <td><?php echo $entry_status; ?><span class="help"><?php echo $help_status; ?></span></td>
              <td><?php if ($gallery_page_status) { ?>
                <input type="radio" name="gallery_page_status" value="1" id="gallery-status-on" class="radio" checked />
                <label for="gallery-status-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="gallery_page_status" value="0" id="gallery-status-off" class="radio" />
                <label for="gallery-status-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } else { ?>
                <input type="radio" name="gallery_page_status" value="1" id="gallery-status-on" class="radio" />
                <label for="gallery-status-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="gallery_page_status" value="0" id="gallery-status-off" class="radio" checked />
                <label for="gallery-status-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_limit; ?><span class="help"><?php echo $help_limit; ?></span></td>
              <td><?php if ($error_limit) { ?>
                <input type="text" name="gallery_page_limit" value="<?php echo $gallery_page_limit; ?>" size="6" class="input-error" />
                <span class="error"><?php echo $error_limit; ?></span>
              <?php } else { ?>
                <input type="text" name="gallery_page_limit" value="<?php echo $gallery_page_limit; ?>" size="6" />
              <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_rate_limit; ?><span class="help"><?php echo $help_rate_limit; ?></span></td>
              <td><?php if ($error_rate_limit) { ?>
                <input type="text" name="gallery_page_rate_limit" value="<?php echo $gallery_page_rate_limit; ?>" size="6" class="input-error" />
                <span class="error"><?php echo $error_rate_limit; ?></span>
              <?php } else { ?>
                <input type="text" name="gallery_page_rate_limit" value="<?php echo $gallery_page_rate_limit; ?>" size="6" />
              <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_notify; ?><span class="help"><?php echo $help_notify; ?></span></td>
              <td><?php if ($gallery_page_notify) { ?>
                <input type="radio" name="gallery_page_notify" value="1" id="gallery-notify-on" class="radio" checked />
                <label for="gallery-notify-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="gallery_page_notify" value="0" id="gallery-notify-off" class="radio" />
                <label for="gallery-notify-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } else { ?>
                <input type="radio" name="gallery_page_notify" value="1" id="gallery-notify-on" class="radio" />
                <label for="gallery-notify-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="gallery_page_notify" value="0" id="gallery-notify-off" class="radio" checked />
                <label for="gallery-notify-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_filters; ?><span class="help"><?php echo $help_filters; ?></span></td>
              <td><?php if ($error_filters) { ?>
                <span class="error"><?php echo $error_filters; ?></span><br />
              <?php } ?>
                <table class="list" id="gallery-filters">
                  <thead>
                    <tr>
                      <td class="left"><?php echo $column_filter_name; ?></td>
                      <td class="right" width="90"><?php echo $column_sort_order; ?></td>
                      <td width="1"></td>
                    </tr>
                  </thead>
                  <?php $filter_row = 0; ?>
                  <?php foreach ($gallery_page_filters as $filter) { ?>
                    <tbody id="filter-row<?php echo $filter_row; ?>">
                      <tr>
                        <td class="left"><input type="hidden" name="gallery_page_filters[<?php echo $filter_row; ?>][filter_id]" value="<?php echo $filter['filter_id']; ?>" />
                        <?php foreach ($languages as $language) { ?>
                          <img src="view/image/flags/<?php echo $language['image']; ?>" alt="" title="<?php echo $language['name']; ?>" /> <input type="text" name="gallery_page_filters[<?php echo $filter_row; ?>][name][<?php echo $language['language_id']; ?>]" value="<?php echo isset($filter['name'][$language['language_id']]) ? $filter['name'][$language['language_id']] : ''; ?>" size="20" maxlength="32" />&nbsp;
                        <?php } ?></td>
                        <td class="right"><input type="text" name="gallery_page_filters[<?php echo $filter_row; ?>][sort_order]" value="<?php echo $filter['sort_order']; ?>" size="3" /></td>
                        <td class="left"><a onclick="$('#filter-row<?php echo $filter_row; ?>').remove();" class="button-delete button-align ripple"><?php echo $button_delete; ?></a></td>
                      </tr>
                    </tbody>
                    <?php $filter_row++; ?>
                  <?php } ?>
                  <tfoot>
                    <tr>
                      <td colspan="2"></td>
                      <td class="left"><a onclick="addFilter();" class="button button-align ripple"><?php echo $text_add_filter; ?></a></td>
                    </tr>
                  </tfoot>
                </table></td>
            </tr>
            <tr>
              <td><?php echo $entry_watermark; ?><span class="help"><?php echo $help_watermark; ?></span></td>
              <td><?php if ($error_watermark) { ?>
                <input type="text" name="gallery_page_watermark" value="<?php echo $gallery_page_watermark; ?>" size="40" class="input-error" />
                <span class="error"><?php echo $error_watermark; ?></span>
              <?php } else { ?>
                <input type="text" name="gallery_page_watermark" value="<?php echo $gallery_page_watermark; ?>" size="40" />
              <?php } ?></td>
            </tr>
          </table>
          <div id="languages" class="htabs">
          <?php foreach ($languages as $language) { ?>
            <a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
          <?php } ?>
          </div>
          <?php foreach ($languages as $language) { ?>
          <div id="language<?php echo $language['language_id']; ?>">
            <table class="form">
              <tr>
                <td><?php echo $entry_title; ?><span class="help"><?php echo $help_title; ?></span></td>
                <td><input type="text" name="gallery_page_content[<?php echo $language['language_id']; ?>][title]" size="40" value="<?php echo isset($gallery_page_content[$language['language_id']]['title']) ? $gallery_page_content[$language['language_id']]['title'] : ''; ?>" /></td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_description; ?><span class="help"><?php echo $help_meta_description; ?></span></td>
                <td><textarea name="gallery_page_content[<?php echo $language['language_id']; ?>][meta_description]" id="meta-description<?php echo $language['language_id']; ?>" data-limit="156" cols="40" rows="5"><?php echo isset($gallery_page_content[$language['language_id']]['meta_description']) ? $gallery_page_content[$language['language_id']]['meta_description'] : ''; ?></textarea>
                <span id="remaining<?php echo $language['language_id']; ?>"></span></td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_keyword; ?><span class="help"><?php echo $help_meta_keyword; ?></span></td>
                <td><textarea name="gallery_page_content[<?php echo $language['language_id']; ?>][meta_keyword]" cols="40" rows="5"><?php echo isset($gallery_page_content[$language['language_id']]['meta_keyword']) ? $gallery_page_content[$language['language_id']]['meta_keyword'] : ''; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo $entry_description; ?><span class="help"><?php echo $help_description; ?></span></td>
                <td><textarea name="gallery_page_content[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($gallery_page_content[$language['language_id']]['description']) ? $gallery_page_content[$language['language_id']]['description'] : ''; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo $entry_agreement; ?><span class="help"><?php echo $help_agreement; ?></span></td>
                <td><textarea name="gallery_page_content[<?php echo $language['language_id']; ?>][agreement]" id="agreement<?php echo $language['language_id']; ?>"><?php echo isset($gallery_page_content[$language['language_id']]['agreement']) ? $gallery_page_content[$language['language_id']]['agreement'] : ''; ?></textarea></td>
              </tr>
            </table>
          </div>
          <?php } ?>
        </form>
      </div>
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

CKEDITOR.replace('agreement<?php echo $language['language_id']; ?>', {
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
var filter_row = <?php echo $filter_row; ?>;

function addFilter() {
	html  = '<tbody id="filter-row' + filter_row + '">';
	html += '<tr>';
	html += '<td class="left"><input type="hidden" name="gallery_page_filters[' + filter_row + '][filter_id]" value="" />';
<?php foreach ($languages as $language) { ?>
	html += '<img src="view/image/flags/<?php echo $language['image']; ?>" alt="" title="<?php echo $language['name']; ?>" /> <input type="text" name="gallery_page_filters[' + filter_row + '][name][<?php echo $language['language_id']; ?>]" value="" size="20" maxlength="32" />&nbsp;';
<?php } ?>
	html += '</td>';
	html += '<td class="right"><input type="text" name="gallery_page_filters[' + filter_row + '][sort_order]" value="0" size="3" /></td>';
	html += '<td class="left"><a onclick="$(\'#filter-row' + filter_row + '\').remove();" class="button-delete button-align ripple"><?php echo $button_delete; ?></a></td>';
	html += '</tr>';
	html += '</tbody>';

	$('#gallery-filters tfoot').before(html);

	filter_row++;
}

$('#tabs a').tabs();
$('#languages a').tabs();
//--></script>

<script type="text/javascript"><!--
function filter() {
	url = 'index.php?route=modification/gallery&token=<?php echo $token; ?>';

	var filter_name = $('input[name=\'filter_name\']').prop('value');

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	var filter_status = $('select[name=\'filter_status\']').prop('value');

	if (filter_status !== '') {
		url += '&filter_status=' + encodeURIComponent(filter_status);
	}

	location = url;
}

function gallery(action) {
	$('#form-list').attr('action', action);
	$('#form-list').submit();
}

$('#gallery-delete').on('click', function() {
	$.confirm({
		title: '<?php echo $text_confirm_delete; ?>',
		content: '<?php echo $text_confirm; ?>',
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				gallery('<?php echo $delete; ?>');
			},
			cancel: function() { }
		}
	});
});

$('#tab-submissions input[name=\'filter_name\']').on('keydown', function(event) {
	if (event.key === 'Enter') {
		event.preventDefault();

		filter();
	}
});
//--></script>

<?php echo $footer; ?>