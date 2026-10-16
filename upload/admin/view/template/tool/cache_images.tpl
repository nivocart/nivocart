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
  <?php if ($attention) { ?>
    <div class="attention"><?php echo $attention; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/stock-status.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a id="delete-selected" class="button-delete ripple"><?php echo $button_delete; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content-body">
      <h2><?php echo $text_summary; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $text_files; ?></td>
          <td><strong><?php echo $summary['files']; ?></strong> / <strong><?php echo $summary['size']; ?></strong></td>
        </tr>
        <tr>
          <td><?php echo $text_oldest; ?></td>
          <td><?php echo $summary['oldest'] ? $summary['oldest'] : $text_none; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_newest; ?></td>
          <td><?php echo $summary['newest'] ? $summary['newest'] : $text_none; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_older_30; ?> / 60 / 90</td>
          <td><?php echo $summary['older_30']; ?> / <?php echo $summary['older_60']; ?> / <?php echo $summary['older_90']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_orphans; ?></td>
          <td><?php echo $summary['orphans']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_legacy; ?></td>
          <td><?php echo $summary['legacy']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_dimensions; ?></td>
          <td>
          <?php if ($dimensions) { ?>
            <?php foreach ($dimensions as $dimension) { ?>
              <a href="<?php echo $dimension['href']; ?>"><?php echo $dimension['name']; ?></a>: <?php echo $dimension['files']; ?> (<?php echo $dimension['size']; ?>)<br />
            <?php } ?>
          <?php } else { ?>
            <?php echo $text_none; ?>
          <?php } ?>
          </td>
        </tr>
      </table>
      <h2><?php echo $text_bulk; ?></h2>
      <form action="<?php echo $purge; ?>" method="post" enctype="multipart/form-data" id="purge-form">
        <input type="hidden" name="type" value="" />
        <table class="form">
          <tr>
            <td><?php echo $text_bulk; ?><span class="help"><?php echo $text_bulk_help; ?></span></td>
            <td><?php echo $text_older_than; ?> <input type="text" name="days" value="60" size="3" /> <?php echo $text_days; ?>
              <a class="button button-align ripple" data-purge="older" data-confirm="<?php echo $text_confirm_older; ?>"><?php echo $button_delete_older; ?></a>
              <a class="button button-align ripple" data-purge="orphans" data-confirm="<?php echo $text_confirm_orphans; ?>"><?php echo $button_delete_orphans; ?></a>
              <a class="button button-align ripple" data-purge="legacy" data-confirm="<?php echo $text_confirm_legacy; ?>"><?php echo $button_delete_legacy; ?></a>
              <a class="button-delete button-align ripple" data-purge="all" data-confirm="<?php echo $text_confirm_all; ?>"><?php echo $button_delete_all; ?></a></td>
          </tr>
        </table>
      </form>
    <?php if ($navigation_hi) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form" name="images">
      <table class="list">
        <thead>
          <tr>
            <td width="1" class="center">
              <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
              <label for="check-all"><span></span></label>
            </td>
            <td class="left"><?php if ($sort === 'name') { ?>
              <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'size') { ?>
              <a href="<?php echo $sort_size; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_size; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_size; ?>"><?php echo $column_size; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'date') { ?>
              <a href="<?php echo $sort_date; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_modified; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_date; ?>"><?php echo $column_modified; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php echo $column_status; ?></td>
            <td class="right"></td>
          </tr>
        </thead>
        <tbody>
          <tr class="filter">
            <td></td>
            <td><select name="filter_dimension">
              <option value=""><?php echo $text_all; ?></option>
            <?php foreach ($dimension_options as $dimension) { ?>
              <option value="<?php echo $dimension; ?>"<?php echo $filter_dimension === $dimension ? ' selected="selected"' : ''; ?>><?php echo $dimension; ?></option>
            <?php } ?>
            </select></td>
            <td></td>
            <td><?php echo $text_older_than; ?> <input type="text" name="filter_days" value="<?php echo $filter_days; ?>" size="3" /> <?php echo $text_days; ?></td>
            <td><select name="filter_status">
              <option value=""><?php echo $text_all; ?></option>
              <option value="orphan"<?php echo $filter_status === 'orphan' ? ' selected="selected"' : ''; ?>><?php echo $text_filter_orphan; ?></option>
              <option value="legacy"<?php echo $filter_status === 'legacy' ? ' selected="selected"' : ''; ?>><?php echo $text_filter_legacy; ?></option>
            </select></td>
            <td class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a>
            <?php if ($filtered) { ?>
              <a href="<?php echo $reset; ?>" class="button ripple"><?php echo $button_reset; ?></a>
            <?php } ?></td>
          </tr>
        <?php if ($cache_images) { ?>
          <?php foreach ($cache_images as $index => $cache_image) { ?>
          <tr>
            <td class="center">
              <input type="checkbox" name="selected[]" value="<?php echo $cache_image['name']; ?>" id="file-<?php echo $index; ?>" class="checkbox" />
              <label for="file-<?php echo $index; ?>"><span></span></label>
            </td>
            <td class="left"><?php echo $cache_image['name']; ?></td>
            <td class="left"><?php echo $cache_image['size']; ?></td>
            <td class="left"><?php echo $cache_image['modified']; ?></td>
            <td class="left" colspan="2"><?php echo $cache_image['status']; ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="6"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
        <?php if ($cache_images) { ?>
        <tfoot>
          <tr>
            <td colspan="2" class="left"><strong><?php echo $text_total_size; ?></strong></td>
            <td colspan="4" class="left"><strong><?php echo $cache_total_files; ?> / <?php echo $cache_total_size; ?></strong></td>
          </tr>
        </tfoot>
        <?php } ?>
      </table>
    </form>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
function filter() {
	var url = 'index.php?route=tool/cache_images&token=<?php echo $token; ?>';

	var filter_dimension = $('select[name=\'filter_dimension\']').val();

	if (filter_dimension) {
		url += '&filter_dimension=' + encodeURIComponent(filter_dimension);
	}

	var filter_days = parseInt($('input[name=\'filter_days\']').val(), 10);

	if (filter_days > 0) {
		url += '&filter_days=' + filter_days;
	}

	var filter_status = $('select[name=\'filter_status\']').val();

	if (filter_status) {
		url += '&filter_status=' + encodeURIComponent(filter_status);
	}

	location = url;
}

function confirmSubmit(content, submit) {
	$.confirm({
		title: <?php echo json_encode($text_confirm_title); ?>,
		content: content,
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: submit,
			cancel: function() { }
		}
	});
}

$('#delete-selected').on('click', function() {
	if (!$('#form input[name=\'selected[]\']:checked').length) {
		confirmSubmit(<?php echo json_encode($text_nothing_selected); ?>, function() { });

		return;
	}

	confirmSubmit(<?php echo json_encode($text_confirm_selected); ?>, function() {
		$('#form').submit();
	});
});

$('a[data-purge]').on('click', function() {
	var type = $(this).attr('data-purge');

	confirmSubmit($(this).attr('data-confirm'), function() {
		$('#purge-form input[name=\'type\']').val(type);
		$('#purge-form').submit();
	});
});

$('tr.filter input').on('keydown', function(e) {
	if (e.which === 13) {
		filter();
	}
});
//--></script>
<?php echo $footer; ?>