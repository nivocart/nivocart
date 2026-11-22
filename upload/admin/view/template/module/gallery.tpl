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
        <a onclick="location = '<?php echo $manager; ?>';" class="button ripple"><i class="fa fa-photo"></i> &nbsp; <?php echo $button_manager; ?></a>
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" name="gallery">
        <h2><?php echo $heading_module_style; ?></h2>
        <table class="form">
        <tbody>
          <tr>
            <td><?php echo $entry_theme; ?></td>
            <td><?php if ($gallery_theme) { ?>
              <input type="radio" name="gallery_theme" value="1" id="theme-on" class="radio" checked />
              <label for="theme-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_theme" value="0" id="theme-off" class="radio" />
              <label for="theme-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="gallery_theme" value="1" id="theme-on" class="radio" />
              <label for="theme-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_theme" value="0" id="theme-off" class="radio" checked />
              <label for="theme-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_title; ?></td>
            <td><?php foreach ($languages as $language) { ?>
              <input type="text" name="gallery_title<?php echo $language['language_id']; ?>" id="gallery_title<?php echo $language['language_id']; ?>" size="30" value="<?php echo ${'gallery_title' . $language['language_id']}; ?>" />
              <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" alt="" style="vertical-align:top;" /><br />
            <?php } ?></td>
          </tr>
        </tbody>
        </table>
        <h2><?php echo $heading_module_parameters; ?></h2>
        <table class="form">
        <tbody>
          <tr>
            <td><?php echo $entry_links; ?></td>
            <td><?php if ($gallery_links) { ?>
              <input type="radio" name="gallery_links" value="1" id="links-on" class="radio" checked />
              <label for="links-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_links" value="0" id="links-off" class="radio" />
              <label for="links-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="gallery_links" value="1" id="links-on" class="radio" />
              <label for="links-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_links" value="0" id="links-off" class="radio" checked />
              <label for="links-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_month; ?></td>
            <td><?php if ($installed) { ?>
              <select name="gallery_month">
                <option value="0"><?php echo $text_none; ?></option>
                <?php foreach ($approved as $image) { ?>
                  <option value="<?php echo $image['gallery_id']; ?>"<?php echo ((int)$gallery_month === $image['gallery_id']) ? ' selected="selected"' : ''; ?>><?php echo $image['name']; ?></option>
                <?php } ?>
              </select>
            <?php } else { ?>
              <?php echo $text_not_installed; ?>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_month_title; ?></td>
            <td><?php foreach ($languages as $language) { ?>
              <input type="text" name="gallery_month_title<?php echo $language['language_id']; ?>" size="30" value="<?php echo ${'gallery_month_title' . $language['language_id']}; ?>" />
              <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" alt="" style="vertical-align:top;" /><br />
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_transition; ?></td>
            <td><select name="gallery_transition">
              <?php if (isset($gallery_transition)) { $selected = "selected"; ?>
                <option value="horizontal" <?php if ($gallery_transition === 'horizontal') { echo $selected; } ?>>Horizontal</option>
                <option value="vertical" <?php if ($gallery_transition === 'vertical') { echo $selected; } ?>>Vertical</option>
                <option value="fade" <?php if ($gallery_transition === 'fade') { echo $selected; } ?>>Fade</option>
              <?php } else { ?>
                <option value="horizontal">Horizontal</option>
                <option value="vertical">Vertical</option>
                <option value="fade">Fade</option>
              <?php } ?>
            </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_duration; ?></td>
            <td><select name="gallery_duration">
              <?php if (isset($gallery_duration)) { $selected = "selected"; ?>
                <option value="1000" <?php if ($gallery_duration === '1000') { echo $selected; } ?>>1000</option>
                <option value="2000" <?php if ($gallery_duration === '2000') { echo $selected; } ?>>2000</option>
                <option value="3000" <?php if ($gallery_duration === '3000') { echo $selected; } ?>>3000</option>
                <option value="4000" <?php if ($gallery_duration === '4000') { echo $selected; } ?>>4000</option>
                <option value="5000" <?php if ($gallery_duration === '5000') { echo $selected; } ?>>5000</option>
                <option value="6000" <?php if ($gallery_duration === '6000') { echo $selected; } ?>>6000</option>
                <option value="7000" <?php if ($gallery_duration === '7000') { echo $selected; } ?>>7000</option>
                <option value="8000" <?php if ($gallery_duration === '8000') { echo $selected; } ?>>8000</option>
                <option value="9000" <?php if ($gallery_duration === '9000') { echo $selected; } ?>>9000</option>
              <?php } else { ?>
                <option value="1000">1000</option>
                <option value="2000">2000</option>
                <option value="3000">3000</option>
                <option value="4000">4000</option>
                <option value="5000">5000</option>
                <option value="6000">6000</option>
                <option value="7000">7000</option>
                <option value="8000">8000</option>
                <option value="9000">9000</option>
              <?php } ?>
            </select> ms</td>
          </tr>
          <tr>
            <td><?php echo $entry_speed; ?></td>
            <td><select name="gallery_speed">
              <?php if (isset($gallery_speed)) { $selected = "selected"; ?>
                <option value="100" <?php if ($gallery_speed === '100') { echo $selected; } ?>>100</option>
                <option value="200" <?php if ($gallery_speed === '200') { echo $selected; } ?>>200</option>
                <option value="300" <?php if ($gallery_speed === '300') { echo $selected; } ?>>300</option>
                <option value="400" <?php if ($gallery_speed === '400') { echo $selected; } ?>>400</option>
                <option value="500" <?php if ($gallery_speed === '500') { echo $selected; } ?>>500</option>
                <option value="600" <?php if ($gallery_speed === '600') { echo $selected; } ?>>600</option>
                <option value="700" <?php if ($gallery_speed === '700') { echo $selected; } ?>>700</option>
                <option value="800" <?php if ($gallery_speed === '800') { echo $selected; } ?>>800</option>
                <option value="900" <?php if ($gallery_speed === '900') { echo $selected; } ?>>900</option>
              <?php } else { ?>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="300">300</option>
                <option value="400">400</option>
                <option value="500">500</option>
                <option value="600">600</option>
                <option value="700">700</option>
                <option value="800">800</option>
                <option value="900">900</option>
              <?php } ?>
            </select> ms</td>
          </tr>
          <tr>
            <td><?php echo $entry_random; ?></td>
            <td><?php if ($gallery_random) { ?>
              <input type="radio" name="gallery_random" value="1" id="random-on" class="radio" checked />
              <label for="random-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_random" value="0" id="random-off" class="radio" />
              <label for="random-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } else { ?>
              <input type="radio" name="gallery_random" value="1" id="random-on" class="radio" />
              <label for="random-on"><span><span></span></span><?php echo $text_yes; ?></label>
              <input type="radio" name="gallery_random" value="0" id="random-off" class="radio" checked />
              <label for="random-off"><span><span></span></span><?php echo $text_no; ?></label>
            <?php } ?></td>
          </tr>
        </tbody>
        </table>
        <table id="module" class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_filter; ?></td>
              <td class="left"><span class="required">*</span> <?php echo $entry_limit; ?></td>
              <td class="left"><span class="required">*</span> <?php echo $entry_dimension; ?></td>
              <td class="left"><?php echo $entry_layout; ?></td>
              <td class="left"><?php echo $entry_position; ?></td>
              <td class="left"><?php echo $entry_status; ?></td>
              <td class="left"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>
        <?php $module_row = 0; ?>
        <?php foreach ($modules as $module) { ?>
          <tbody id="module-row<?php echo $module_row; ?>">
            <tr>
              <td class="left"><select name="gallery_module[<?php echo $module_row; ?>][filter_id]">
                <option value="0"><?php echo $text_all; ?></option>
                <?php foreach ($filters as $filter_id => $filter_name) { ?>
                  <option value="<?php echo $filter_id; ?>"<?php echo ((int)($module['filter_id'] ?? 0) === $filter_id) ? ' selected="selected"' : ''; ?>><?php echo $filter_name; ?></option>
                <?php } ?>
              </select></td>
              <td class="left">
                <input type="text" name="gallery_module[<?php echo $module_row; ?>][limit]" value="<?php echo $module['limit'] ?? 8; ?>" size="3" />
                <?php if (isset($error_limit[$module_row])) { ?>
                  <span class="error"><?php echo $error_limit[$module_row]; ?></span>
                <?php } ?>
              </td>
              <td class="left">
                <input type="text" name="gallery_module[<?php echo $module_row; ?>][width]" value="<?php echo $module['width']; ?>" size="3" /> x
                <input type="text" name="gallery_module[<?php echo $module_row; ?>][height]" value="<?php echo $module['height']; ?>" size="3" /> px
                <?php if (isset($error_dimension[$module_row])) { ?>
                  <span class="error"><?php echo $error_dimension[$module_row]; ?></span>
                <?php } ?>
              </td>
              <td class="left"><select name="gallery_module[<?php echo $module_row; ?>][layout_id]">
                <?php foreach ($layouts as $layout) { ?>
                  <?php if ($layout['layout_id'] === $module['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select></td>
              <td class="left"><select name="gallery_module[<?php echo $module_row; ?>][position]">
                <?php if ($module['position'] === 'content_higher') { ?>
                  <option value="content_higher" selected="selected"><?php echo $text_content_higher; ?></option>
                <?php } else { ?>
                  <option value="content_higher"><?php echo $text_content_higher; ?></option>
                <?php } ?>
                <?php if ($module['position'] === 'content_high') { ?>
                  <option value="content_high" selected="selected"><?php echo $text_content_high; ?></option>
                <?php } else { ?>
                  <option value="content_high"><?php echo $text_content_high; ?></option>
                <?php } ?>
                <?php if ($module['position'] === 'content_left') { ?>
                  <option value="content_left" selected="selected"><?php echo $text_content_left; ?></option>
                <?php } else { ?>
                  <option value="content_left"><?php echo $text_content_left; ?></option>
                <?php } ?>
                <?php if ($module['position'] === 'content_right') { ?>
                  <option value="content_right" selected="selected"><?php echo $text_content_right; ?></option>
                <?php } else { ?>
                  <option value="content_right"><?php echo $text_content_right; ?></option>
                <?php } ?>
                <?php if ($module['position'] === 'content_low') { ?>
                  <option value="content_low" selected="selected"><?php echo $text_content_low; ?></option>
                <?php } else { ?>
                  <option value="content_low"><?php echo $text_content_low; ?></option>
                <?php } ?>
                <?php if ($module['position'] === 'content_lower') { ?>
                  <option value="content_lower" selected="selected"><?php echo $text_content_lower; ?></option>
                <?php } else { ?>
                  <option value="content_lower"><?php echo $text_content_lower; ?></option>
                <?php } ?>
              </select></td>
              <td class="left"><select name="gallery_module[<?php echo $module_row; ?>][status]">
                <?php if ($module['status']) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
              <td class="center">
                <input type="text" name="gallery_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" />
              </td>
              <td class="center">
                <a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a>
              </td>
            </tr>
          </tbody>
        <?php $module_row++; ?>
        <?php } ?>
          <tfoot>
            <tr>
              <td colspan="7" class="script">Powered by <?php echo $gallery_plugin; ?> <?php echo $gallery_version; ?></td>
              <td class="center"><a onclick="addModule();" class="button ripple"><?php echo $button_add_module; ?></a></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="gallery_module[' + module_row + '][filter_id]">';
	html += '      <option value="0"><?php echo $text_all; ?></option>';
	<?php foreach ($filters as $filter_id => $filter_name) { ?>
	html += '      <option value="<?php echo $filter_id; ?>"><?php echo addslashes($filter_name); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><input type="text" name="gallery_module[' + module_row + '][limit]" value="8" size="3" /></td>';
	html += '    <td class="left">';
	html += '      <input type="text" name="gallery_module[' + module_row + '][width]" value="200" size="3" /> x ';
	html += '      <input type="text" name="gallery_module[' + module_row + '][height]" value="200" size="3" /> px';
	html += '    </td>';
	html += '    <td class="left"><select name="gallery_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="gallery_module[' + module_row + '][position]">';
	html += '      <option value="content_higher"><?php echo $text_content_higher; ?></option>';
	html += '      <option value="content_high"><?php echo $text_content_high; ?></option>';
	html += '      <option value="content_left"><?php echo $text_content_left; ?></option>';
	html += '      <option value="content_right"><?php echo $text_content_right; ?></option>';
	html += '      <option value="content_low"><?php echo $text_content_low; ?></option>';
	html += '      <option value="content_lower"><?php echo $text_content_lower; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="gallery_module[' + module_row + '][status]">';
	html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
	html += '      <option value="0"><?php echo $text_disabled; ?></option>';
	html += '    </select></td>';
	html += '    <td class="center"><input type="text" name="gallery_module[' + module_row + '][sort_order]" value="0" size="3" /></td>';
	html += '    <td class="center"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button-delete ripple"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';

	$('#module tfoot').before(html);

	module_row++;
}
//--></script>

<?php echo $footer; ?>