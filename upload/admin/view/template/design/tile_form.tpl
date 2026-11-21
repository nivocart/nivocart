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
      <h1><img src="view/image/tile.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><span class="required">*</span> <?php echo $entry_name; ?></td>
          <td><?php if ($error_name) { ?>
            <input type="text" name="name" value="<?php echo $name; ?>" size="30" class="input-error" />
            <span class="error"><?php echo $error_name; ?></span>
          <?php } else { ?>
            <input type="text" name="name" value="<?php echo $name; ?>" size="30" />
          <?php } ?></td>
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
        <tr>
          <td><?php echo $text_link; ?></td>
          <td><?php echo $text_info; ?></td>
        </tr>
      </table>
      <div class="tooltip"><?php echo $text_limits; ?></div>
      <table id="tiles" class="list">
        <thead>
          <tr>
            <td class="left"><span class="required">*</span> <?php echo $entry_content; ?></td>
            <td class="left"><?php echo $entry_image; ?></td>
            <td class="left"><?php echo $entry_link; ?></td>
            <td class="left"><?php echo $entry_design; ?></td>
            <td class="left"><?php echo $entry_sort_order; ?></td>
            <td></td>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <td colspan="5">
              <?php if ($error_items) { ?>
                <span class="error"><?php echo $error_items; ?></span>
              <?php } ?>
            </td>
            <td class="center"><a onclick="addTile();" class="button"><?php echo $button_add_tile; ?></a></td>
          </tr>
        </tfoot>
      </table>
    </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var tile_row = 0;
var tile_max = 12;

var languages = [
<?php foreach ($languages as $language) { ?>
  {id: '<?php echo $language['language_id']; ?>', name: '<?php echo addslashes($language['name']); ?>', image: '<?php echo $language['image']; ?>'},
<?php } ?>
];

var layout_options = <?php echo json_encode($layout_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var align_options = <?php echo json_encode($align_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var valign_options = <?php echo json_encode($valign_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

var colour_options = <?php echo json_encode($colour_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

var tile_items = <?php echo json_encode($tile_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var tile_errors = <?php echo json_encode($error_tile_item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function escapeHtml(text) {
	return $('<div>').text(text).html();
}

function selectHtml(name, options, value) {
	var html = '<select name="' + name + '">';

	$.each(options, function(key, label) {
		html += '<option value="' + key + '"' + ((key === value) ? ' selected="selected"' : '') + '>' + escapeHtml(label) + '</option>';
	});

	html += '</select>';

	return html;
}

function errorHtml(message) {
	return message ? '<span class="error">' + message + '</span><br />' : '';
}

function colourHtml(n, field, value) {
	var html = '<div class="tile-colour">#<input type="text" name="tile_item[' + n + '][' + field + ']" value="' + escapeHtml(value).replace(/"/g, '&quot;') + '" size="6" maxlength="7" class="colour-text" />';

	html += '<select class="colour-select">';
	html += '<option value=""><?php echo addslashes($text_default); ?></option>';
	html += '<option value="*"><?php echo addslashes($text_custom); ?></option>';

	$.each(colour_options, function(i, colour) {
		html += '<option value="' + colour.hex + '">' + escapeHtml(colour.title) + '</option>';
	});

	html += '</select><span class="colour-swatch"></span></div>';

	return html;
}

// Keeps the select, the swatch and the sample text in step with the hex fields of one tile
function syncColours($row) {
	var colours = {};

	$row.find('input.colour-text').each(function() {
		var $input = $(this);
		var value = String($input.val()).trim().replace('#', '').toUpperCase();
		var valid = /^[0-9A-F]{6}$/.test(value);
		var $field = $input.closest('.tile-colour');
		var $select = $field.find('select.colour-select');

		if (value === '') {
			$select.val('');
		} else if (valid && $select.find('option[value="' + value + '"]').length) {
			$select.val(value);
		} else {
			$select.val('*');
		}

		$field.find('.colour-swatch').toggleClass('is-empty', !valid).css('background-color', valid ? '#' + value : '');

		colours[/\[(background|colour)\]$/.exec($input.attr('name'))[1]] = valid ? '#' + value : '';
	});

	$row.find('.tile-sample').css({'background-color': colours.background, 'color': colours.colour});
}

function addTile(item, errors) {
	if ($('#tiles > tbody').length >= tile_max) {
		$.alert({
			title: '',
			content: '<?php echo addslashes($text_limits); ?>',
			icon: 'fa fa-info-circle',
			theme: 'light',
			useBootstrap: false,
			boxWidth: 580,
			animation: 'zoom',
			closeAnimation: 'scale',
			opacity: 0.1
		});

		return;
	}

	var n = tile_row;

	item = item || {
		tile_item_description: {},
		image: '',
		thumb: '<?php echo $no_image; ?>',
		link: '',
		external_link: 0,
		layout: 'top',
		align: 'center',
		valign: 'middle',
		background: '',
		colour: '',
		sort_order: 0
	};

	errors = errors || {};

	var html  = '<tbody id="tile-row' + n + '">';
	html += '  <tr>';

	// Content
	html += '    <td class="left">';

	$.each(languages, function(i, language) {
		var d = item.tile_item_description[language.id] || {title: '', text: '', link_text: ''};
		var base = 'tile_item[' + n + '][tile_item_description][' + language.id + ']';

		html += '      <input type="text" name="' + base + '[title]" value="' + escapeHtml(d.title).replace(/"/g, '&quot;') + '" size="34" placeholder="<?php echo addslashes($entry_title); ?>" /> <img src="view/image/flags/' + language.image + '" alt="" title="' + escapeHtml(language.name) + '" /><br />';
		html += errorHtml(errors.title ? errors.title[language.id] : '');
		html += '      <input type="text" name="' + base + '[text]" value="' + escapeHtml(d.text).replace(/"/g, '&quot;') + '" size="34" placeholder="<?php echo addslashes($entry_text); ?>" /><br />';
		html += errorHtml(errors.text ? errors.text[language.id] : '');
		html += '      <input type="text" name="' + base + '[link_text]" value="' + escapeHtml(d.link_text).replace(/"/g, '&quot;') + '" size="34" placeholder="<?php echo addslashes($entry_link_text); ?>" /><br />';
		html += errorHtml(errors.link_text ? errors.link_text[language.id] : '');
	});

	html += '    </td>';

	// Image
	html += '    <td class="center"><div class="image"><img src="' + item.thumb + '" alt="" id="thumb' + n + '" /><br />';
	html += '      <input type="hidden" name="tile_item[' + n + '][image]" value="' + escapeHtml(item.image).replace(/"/g, '&quot;') + '" id="image' + n + '" />';
	html += '      <a onclick="image_upload(\'image' + n + '\', \'thumb' + n + '\');" class="button-browse"></a><a onclick="$(\'#thumb' + n + '\').attr(\'src\', \'<?php echo $no_image; ?>\'); $(\'#image' + n + '\').attr(\'value\', \'\');" class="button-recycle"></a>';
	html += '    </div>';
	html += errorHtml(errors.image);
	html += '    </td>';

	// Link
	html += '    <td class="left"><input type="text" name="tile_item[' + n + '][link]" value="' + escapeHtml(item.link).replace(/"/g, '&quot;') + '" size="30" /><br />';
	html += '      <?php echo addslashes($entry_external_link); ?>: <select name="tile_item[' + n + '][external_link]">';
	html += '        <option value="1"' + (item.external_link ? ' selected="selected"' : '') + '><?php echo addslashes($text_yes); ?></option>';
	html += '        <option value="0"' + (item.external_link ? '' : ' selected="selected"') + '><?php echo addslashes($text_no); ?></option>';
	html += '      </select>';
	html += '    </td>';

	// Design
	html += '    <td class="left">';
	html += '      <div class="tile-design">';
	html += '        <span><?php echo addslashes($entry_layout); ?>:</span><div>' + selectHtml('tile_item[' + n + '][layout]', layout_options, item.layout) + '</div>';
	html += '        <span><?php echo addslashes($entry_align); ?>:</span><div>' + selectHtml('tile_item[' + n + '][align]', align_options, item.align) + '</div>';
	html += '        <span><?php echo addslashes($entry_valign); ?>:</span><div>' + selectHtml('tile_item[' + n + '][valign]', valign_options, item.valign) + '</div>';
	html += '        <span><?php echo addslashes($entry_background); ?>:</span>';
	html += '        <div>' + colourHtml(n, 'background', item.background) + errorHtml(errors.background) + '</div>';
	html += '        <span><?php echo addslashes($entry_colour); ?>:</span>';
	html += '        <div>' + colourHtml(n, 'colour', item.colour) + errorHtml(errors.colour) + '</div>';
	html += '        <span><?php echo addslashes($text_sample); ?>:</span>';
	html += '        <div><span class="tile-sample"><?php echo addslashes($text_sample_text); ?></span></div>';
	html += '      </div>';
	html += '    </td>';

	// Sort order and remove
	html += '    <td class="center"><input type="text" name="tile_item[' + n + '][sort_order]" value="' + parseInt(item.sort_order, 10) + '" size="2" /></td>';
	html += '    <td class="center"><a onclick="$(\'#tile-row' + n + '\').remove();" class="button-delete ripple"><?php echo addslashes($button_remove); ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';

	$('#tiles > tfoot').before(html);

	syncColours($('#tile-row' + n));

	tile_row++;
}

// Hex fields, colour selects, swatches and sample text stay in step
$('#tiles').on('input', 'input.colour-text', function() {
	syncColours($(this).closest('tbody'));
});

$('#tiles').on('change', 'select.colour-select', function() {
	var value = $(this).val();

	if (value !== '*') {
		$(this).closest('.tile-colour').find('input.colour-text').val(value);
	}

	syncColours($(this).closest('tbody'));
});

$(document).ready(function() {
	$.each(tile_items, function(i, item) {
		addTile(item, tile_errors[item.row]);
	});

	if (!tile_items.length) {
		addTile();
	}

	$('.colorbox').colorbox({
		overlayClose: true,
		opacity: 0.3,
		width: <?php echo ($this->browser->checkMobile()) ? 580 : 760; ?>,
		height: 400
	});
});
//--></script>

<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();

	$('#content').prepend('<div id="dialog" style="padding:3px 0 0 0;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin:0; display:block; width:100%; height:100%;" frameborder="no" scrolling="auto"></iframe></div>');

	$('#dialog').dialog({
		title: '<?php echo addslashes($text_image_manager); ?>',
		close: function(event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
					dataType: 'text',
					success: function(text) {
						$('#' + thumb).replaceWith('<img src="' + text + '" alt="" id="' + thumb + '" />');
					}
				});
			} else {
				$('#' + thumb).replaceWith('<img src="<?php echo $no_image; ?>" alt="" id="' + thumb + '" />');
			}
		},
		bgiframe: false,
		width: Math.min($(window).width() - 20, 1400),
		height: Math.min($(window).height() - 20, 500),
		resizable: false,
		modal: false
	});
};
//--></script>

<?php echo $footer; ?>