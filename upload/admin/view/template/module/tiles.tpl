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
        <a onclick="location = '<?php echo $manager; ?>';" class="button ripple"><i class="fa fa-th-large"></i> &nbsp; <?php echo $button_manager; ?></a>
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" name="tiles">
        <h2><?php echo $heading_module_style; ?></h2>
        <table class="form">
        <tbody>
          <tr>
            <td><?php echo $entry_theme; ?></td>
            <td><?php if ($tiles_theme) { ?>
              <input type="radio" name="tiles_theme" value="1" id="theme-on" class="radio" checked />
              <label for="theme-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="tiles_theme" value="0" id="theme-off" class="radio" />
              <label for="theme-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="tiles_theme" value="1" id="theme-on" class="radio" />
              <label for="theme-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="tiles_theme" value="0" id="theme-off" class="radio" checked />
              <label for="theme-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_title; ?></td>
            <td><?php foreach ($languages as $language) { ?>
              <input type="text" name="tiles_title<?php echo $language['language_id']; ?>" id="tiles_title<?php echo $language['language_id']; ?>" size="30" value="<?php echo ${'tiles_title' . $language['language_id']}; ?>" />
              <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" alt="" style="vertical-align:top;" /><br />
            <?php } ?></td>
          </tr>
        </tbody>
        </table>
        <h2><?php echo $heading_module_parameters; ?></h2>
        <table class="form">
        <tbody>
          <tr>
            <td><?php echo $entry_shape; ?></td>
            <td><select name="tiles_shape">
              <?php foreach ($shapes as $shape) { ?>
                <?php if ($shape['shape'] === $tiles_shape) { ?>
                  <option value="<?php echo $shape['shape']; ?>" selected="selected"><?php echo $shape['title']; ?></option>
                <?php } else { ?>
                  <option value="<?php echo $shape['shape']; ?>"><?php echo $shape['title']; ?></option>
                <?php } ?>
              <?php } ?>
            </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_hover; ?></td>
            <td><select name="tiles_hover">
              <?php foreach ($hovers as $key => $label) { ?>
                <?php if ($key === $tiles_hover) { ?>
                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $label; ?></option>
                <?php } else { ?>
                  <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php } ?>
              <?php } ?>
            </select></td>
          </tr>
        </tbody>
        </table>
        <?php if (!$tiles) { ?>
          <div class="tooltip"><?php echo $text_none; ?></div>
        <?php } ?>
        <table id="module" class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_tile; ?></td>
              <td class="left"><span class="required">*</span> <?php echo $entry_dimension; ?></td>
              <td class="left"><?php echo $entry_min_height; ?></td>
              <td class="left"><?php echo $entry_gap; ?></td>
              <td class="left"><?php echo $entry_layout; ?></td>
              <td class="left"><?php echo $entry_position; ?></td>
              <td class="left"><?php echo $entry_status; ?></td>
              <td class="center"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td colspan="8"></td>
              <td class="center"><a onclick="addModule();" class="button ripple"><?php echo $button_add_module; ?></a></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var module_row = 0;

var tile_options = <?php echo json_encode($tiles, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT); ?>;
var layout_options = <?php echo json_encode($layouts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT); ?>;
var position_options = <?php echo json_encode($positions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var gap_options = <?php echo json_encode($gaps); ?>;
var status_options = {'1': '<?php echo addslashes($text_enabled); ?>', '0': '<?php echo addslashes($text_disabled); ?>'};
var modules = <?php echo json_encode(array_values($modules), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function escapeHtml(text) {
	return $('<div>').text(text).html();
}

// Labels are already HTML-safe (language strings and stored names), so entities such as &#9651; must not be escaped again
function selectHtml(name, options, value) {
	var html = '<select name="' + name + '">';

	$.each(options, function(key, label) {
		html += '<option value="' + escapeHtml(String(key)) + '"' + ((String(key) === String(value)) ? ' selected="selected"' : '') + '>' + String(label) + '</option>';
	});

	html += '</select>';

	return html;
}

function addModule(module) {
	var n = module_row;

	module = module || {
		tile_id: '',
		width: 600,
		height: 400,
		min_height: 0,
		gap: 10,
		layout_id: '',
		position: 'content_high',
		status: 1,
		sort_order: 0
	};

	var gaps = {};

	$.each(gap_options, function(i, gap) {
		gaps[gap] = gap + ' px';
	});

	var html = '<tbody id="module-row' + n + '">';
		html += '  <tr>';
		html += '    <td class="left">' + selectHtml('tiles_module[' + n + '][tile_id]', tile_options, module.tile_id) + '</td>';
		html += '    <td class="left">';
		html += '      <input type="text" name="tiles_module[' + n + '][width]" value="' + parseInt(module.width, 10) + '" size="3" /> x ';
		html += '      <input type="text" name="tiles_module[' + n + '][height]" value="' + parseInt(module.height, 10) + '" size="3" /> px';
		html += '    </td>';
		html += '    <td class="left"><input type="text" name="tiles_module[' + n + '][min_height]" value="' + (parseInt(module.min_height, 10) || 0) + '" size="3" /> px</td>';
		html += '    <td class="left">' + selectHtml('tiles_module[' + n + '][gap]', gaps, module.gap) + '</td>';
		html += '    <td class="left">' + selectHtml('tiles_module[' + n + '][layout_id]', layout_options, module.layout_id) + '</td>';
		html += '    <td class="left">' + selectHtml('tiles_module[' + n + '][position]', position_options, module.position) + '</td>';
		html += '    <td class="left">' + selectHtml('tiles_module[' + n + '][status]', status_options, module.status ? '1' : '0') + '</td>';
		html += '    <td class="center"><input type="text" name="tiles_module[' + n + '][sort_order]" value="' + (parseInt(module.sort_order, 10) || 0) + '" size="3" /></td>';
		html += '    <td class="center"><a onclick="$(\'#module-row' + n + '\').remove();" class="button-delete ripple"><?php echo addslashes($button_remove); ?></a></td>';
		html += '  </tr>';
		html += '</tbody>';

	$('#module tfoot').before(html);

	module_row++;
}

$(document).ready(function() {
	$.each(modules, function(i, module) {
		addModule(module);
	});
});
//--></script>

<?php echo $footer; ?>