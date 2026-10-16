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
      <h1><img src="view/image/feed.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <h2><?php echo $heading_title; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="pinterest_feed_status">
            <?php if ($pinterest_feed_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_data_feed; ?></td>
          <td><textarea cols="40" rows="3" readonly="readonly"><?php echo $data_feed; ?></textarea></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_currency; ?><span class="help"><?php echo $help_currency; ?></span></td>
          <td><input type="text" name="pinterest_feed_currency" value="<?php echo $pinterest_feed_currency; ?>" size="5" maxlength="3" />
            <?php if ($error_currency) { ?>
              <span class="error"><?php echo $error_currency; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_cache_hours; ?><span class="help"><?php echo $help_cache_hours; ?></span></td>
          <td><input type="text" name="pinterest_feed_cache_hours" value="<?php echo $pinterest_feed_cache_hours; ?>" size="5" />
            <?php if ($error_cache_hours) { ?>
              <span class="error"><?php echo $error_cache_hours; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_min_image; ?><span class="help"><?php echo $help_min_image; ?></span></td>
          <td><input type="text" name="pinterest_feed_min_image" value="<?php echo $pinterest_feed_min_image; ?>" size="5" />
            <?php if ($error_min_image) { ?>
              <span class="error"><?php echo $error_min_image; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_extra_images; ?><span class="help"><?php echo $help_extra_images; ?></span></td>
          <td><input type="text" name="pinterest_feed_extra_images" value="<?php echo $pinterest_feed_extra_images; ?>" size="5" />
            <?php if ($error_extra_images) { ?>
              <span class="error"><?php echo $error_extra_images; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_taxonomy; ?><span class="help"><?php echo $help_taxonomy; ?></span></td>
          <td><input type="text" id="taxonomy-search" value="" size="40" placeholder="<?php echo $text_taxonomy_placeholder; ?>" autocomplete="off" />
            <br /><span id="taxonomy-target"></span>
            <div id="taxonomy-results"></div></td>
        </tr>
        <tr>
          <td><?php echo $entry_category_default; ?><span class="help"><?php echo $help_category; ?></span></td>
          <td><input type="text" name="pinterest_feed_category_default" value="<?php echo $pinterest_feed_category_default; ?>" size="10" />
            <?php if ($error_category) { ?>
              <span class="error"><?php echo $error_category; ?></span>
            <?php } ?>
            <a href="<?php echo $taxonomy_url; ?>" target="_blank" rel="noopener" class="button button-align ripple"><?php echo $text_taxonomy_link; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $entry_category_map; ?><span class="help"><?php echo $help_category_map; ?></span></td>
          <td><table class="form">
            <?php foreach ($categories as $category) { ?>
              <tr>
                <td><?php echo $category['indent']; ?><?php echo $category['name']; ?></td>
                <td><input type="text" name="pinterest_feed_category[<?php echo $category['category_id']; ?>]" value="<?php echo $category['value']; ?>" size="10" />
                  <?php if ($category['error']) { ?>
                    <span class="error"><?php echo $category['error']; ?></span>
                  <?php } ?></td>
              </tr>
            <?php } ?>
          </table></td>
        </tr>
        <tr>
          <td><?php echo $entry_cache; ?></td>
          <td><a href="<?php echo $clear; ?>" class="button ripple"><?php echo $text_clear_cache; ?></a>
            <br /><?php echo $cache_info; ?>
            <?php if ($cache_skipped) { ?>
              <br /><?php echo $cache_skipped; ?>
            <?php } ?>
            <?php if ($variants_info) { ?>
              <br /><?php echo $variants_info; ?>
            <?php } ?></td>
        </tr>
      </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var taxonomyUrl = <?php echo json_encode($taxonomy); ?>;
var taxonomyEmpty = <?php echo json_encode($text_taxonomy_empty); ?>;
var taxonomyTargetText = <?php echo json_encode($text_taxonomy_target); ?>;
var taxonomyDefaultName = <?php echo json_encode($text_taxonomy_default); ?>;
var taxonomyTimer = null;
var taxonomyInputs = $('input[name="pinterest_feed_category_default"], input[name^="pinterest_feed_category["]');
var taxonomyTarget = $('input[name="pinterest_feed_category_default"]');

function taxonomyShowTarget() {
	var name = taxonomyDefaultName;

	if (taxonomyTarget.attr('name') !== 'pinterest_feed_category_default') {
		name = $.trim(taxonomyTarget.closest('tr').children('td:first').text()).replace(/^- /, '');
	}

	$('#taxonomy-target').text(taxonomyTargetText.replace('%s', name));
}

function taxonomySearch() {
	var term = $.trim($('#taxonomy-search').val());
	var results = $('#taxonomy-results');

	if (term.length < 2) {
		results.empty();

		return;
	}

	$.ajax({
		url: taxonomyUrl + '&q=' + encodeURIComponent(term),
		dataType: 'json',
		success: function(json) {
			results.empty();

			if (json['error']) {
				results.append($('<span class="error"></span>').text(json['error']));

				return;
			}

			if (!json['results'] || !json['results'].length) {
				results.text(taxonomyEmpty);

				return;
			}

			$.each(json['results'], function(index, item) {
				results.append($('<a href="#" class="taxonomy-pick"></a>').attr('data-id', item['id']).text(item['id'] + ' - ' + item['path'])).append('<br />');
			});
		}
	});
}

taxonomyInputs.on('focus', function() {
	taxonomyTarget = $(this);

	taxonomyShowTarget();
});

$('#taxonomy-search').on('keydown', function(e) {
	if (e.which === 13) {
		e.preventDefault();
	}
}).on('keyup', function() {
	clearTimeout(taxonomyTimer);

	taxonomyTimer = setTimeout(taxonomySearch, 300);
});

$('#taxonomy-results').on('click', 'a.taxonomy-pick', function() {
	taxonomyTarget.val($(this).attr('data-id'));

	return false;
});

taxonomyShowTarget();
//--></script>
<?php echo $footer; ?>