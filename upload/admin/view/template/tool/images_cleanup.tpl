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
        <?php if ($scanned && $orphans) { ?>
          <a id="delete" class="button-delete ripple"><?php echo $button_delete; ?></a>
        <?php } ?>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content-body">
      <!-- Folder selector (GET form — route + token as hidden fields) -->
      <form method="get" action="index.php" id="scan-form" style="margin-bottom:16px;">
        <input type="hidden" name="route" value="tool/images_cleanup" />
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <table class="form">
          <tr>
            <td><?php echo $entry_folder; ?></td>
            <td>
              <select name="folder" id="folder" onchange="document.getElementById('scan-form').submit();" style="vertical-align: middle;">
                <option value=""><?php echo $text_select_folder; ?></option>
                <?php foreach ($folders as $key => $label) { ?>
                  <option value="<?php echo $key; ?>"<?php if ($selected_folder === $key) echo ' selected'; ?>><?php echo $label; ?></option>
                <?php } ?>
              </select>
              &nbsp;
              <a onclick="document.getElementById('scan-form').submit(); return false;" class="button ripple" style="vertical-align: middle;"><?php echo $button_scan; ?></a>
            </td>
          </tr>
        </table>
      </form>
      <?php if ($scanned) { ?>
        <!-- Scan summary banner -->
        <?php if ($orphans) { ?>
          <div class="attention"><?php echo $summary; ?></div>
        <?php } else { ?>
          <div class="success"><?php echo $text_clean_summary; ?></div>
        <?php } ?>
        <!-- Orphan images table with delete form -->
        <form action="<?php echo $delete; ?>" method="post" id="delete-form">
          <input type="hidden" name="folder" value="<?php echo $selected_folder; ?>" />
          <table class="list">
            <thead>
              <tr>
                <td width="1" style="text-align:center;">
                  <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox"<?php echo $orphans ? ' checked' : ''; ?> />
                  <label for="check-all"><span></span></label>
                </td>
                <td class="left"><?php echo $column_name; ?></td>
                <td class="left"><?php echo $column_size; ?></td>
                <td class="left"><?php echo $column_modified; ?></td>
              </tr>
            </thead>
            <tbody>
              <?php if ($orphans) { ?>
                <?php foreach ($orphans as $orphan) { ?>
                  <?php $safe_id = 'img-' . md5($orphan['name']); ?>
                  <tr>
                    <td style="text-align:center;">
                      <input type="checkbox" name="selected[]" value="<?php echo $orphan['name']; ?>" id="<?php echo $safe_id; ?>" class="checkbox" checked />
                      <label for="<?php echo $safe_id; ?>"><span></span></label>
                    </td>
                    <td class="left"><?php echo $orphan['name']; ?></td>
                    <td class="left"><?php echo $orphan['size']; ?></td>
                    <td class="left"><?php echo $orphan['modified']; ?></td>
                  </tr>
                <?php } ?>
              <?php } else { ?>
                <tr>
                  <td class="center" colspan="4"><?php echo $text_no_orphans; ?></td>
                </tr>
              <?php } ?>
            </tbody>
            <?php if ($orphans) { ?>
              <tfoot>
                <tr>
                  <td colspan="2" class="left"><strong><?php echo $text_total_size; ?></strong></td>
                  <td colspan="2" class="left"><strong><?php echo $orphan_total_size; ?></strong></td>
                </tr>
              </tfoot>
            <?php } ?>
          </table>
        </form>
      <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#delete').on('click', function() {
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
				$('#delete-form').submit();
			},
			cancel: function() { }
		}
	});
});
//--></script>

<?php echo $footer; ?>