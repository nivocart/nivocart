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
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $insert; ?>" class="button ripple"><?php echo $button_insert; ?></a>
        <a id="delete" class="button-delete ripple"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content-body">
    <?php if ($rates_overdue && $rates_auto) { ?>
      <div class="warning"><?php echo $text_rates_overdue; ?></div>
    <?php } ?>
      <div class="tooltip" style="margin:5px 0 15px 0;"><?php echo $text_rates_last_success; ?>
      <?php if ($rates_last_success && $rates_last_success['message']) { ?>
        <br /><?php echo htmlspecialchars($rates_last_success['message'], ENT_QUOTES, 'UTF-8'); ?>
      <?php } ?>
      <?php if ($rates_last_failed) { ?>
        <br /><?php echo $text_rates_failed; ?> <?php echo htmlspecialchars($rates_last_failed['date'] . ' - ' . $rates_last_failed['message'], ENT_QUOTES, 'UTF-8'); ?>
      <?php } ?>
      <?php if (!$rates_auto) { ?>
        <br /><?php echo $text_rates_auto_off; ?>
      <?php } ?>
      </div>
	<?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
        <thead>
          <tr>
            <td width="1" style="text-align:center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
            <label for="check-all"><span></span></label></td>
            <td class="left"><?php if ($sort === 'title') { ?>
              <a href="<?php echo $sort_title; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_title; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_title; ?>"><?php echo $column_title; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'code') { ?>
              <a href="<?php echo $sort_code; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_code; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_code; ?>"><?php echo $column_code; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'status') { ?>
              <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="right"><?php if ($sort === 'value') { ?>
              <a href="<?php echo $sort_value; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_value; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_value; ?>"><?php echo $column_value; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="right"><?php if ($sort === 'date_modified') { ?>
              <a href="<?php echo $sort_date_modified; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_modified; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_date_modified; ?>"><?php echo $column_date_modified; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($currencies) { ?>
          <?php foreach ($currencies as $currency) { ?>
          <tr>
            <td style="text-align:center;"><?php if ($currency['selected']) { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $currency['currency_id']; ?>" id="<?php echo $currency['currency_id']; ?>" class="checkbox" checked />
              <label for="<?php echo $currency['currency_id']; ?>"><span></span></label>
            <?php } else { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $currency['currency_id']; ?>" id="<?php echo $currency['currency_id']; ?>" class="checkbox" />
              <label for="<?php echo $currency['currency_id']; ?>"><span></span></label>
            <?php } ?></td>
            <td class="left"><?php echo $currency['title']; ?></td>
            <td class="left"><?php echo $currency['code']; ?></td>
            <?php if ($currency['status'] === '1') { ?>
              <td class="center"><span class="enabled"><?php echo $text_enabled; ?></span></td>
            <?php } else { ?>
              <td class="center"><span class="disabled"><?php echo $text_disabled; ?></span></td>
            <?php } ?>
            <td class="right"><?php echo $currency['value']; ?></td>
            <td class="right"><?php echo $currency['date_modified']; ?></td>
            <td class="right"><?php foreach ($currency['action'] as $action) { ?>
              <a href="<?php echo $action['href']; ?>" class="button-form animated fadeIn ripple"><?php echo $action['text']; ?></a>
            <?php } ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="7"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
        </table>
      </form>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
      <h2><?php echo $tab_rates; ?></h2>
      <div class="tooltip"><?php echo $help_currencies; ?></div>
      <table class="form">
        <tr>
          <td><?php echo $text_rates_command_primary; ?></td>
          <td><input type="text" value="<?php echo htmlspecialchars($cron_schedule_primary, ENT_QUOTES, 'UTF-8'); ?>" size="14" readonly="readonly" onfocus="this.select();" />
            <span class="help"><?php echo htmlspecialchars($cron_time_primary, ENT_QUOTES, 'UTF-8'); ?></span>
            <textarea rows="2" cols="110" readonly="readonly" onfocus="this.select();"><?php echo htmlspecialchars($cron_command_primary, ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $text_rates_command_backup; ?></td>
          <td><input type="text" value="<?php echo htmlspecialchars($cron_schedule_backup, ENT_QUOTES, 'UTF-8'); ?>" size="14" readonly="readonly" onfocus="this.select();" />
            <span class="help"><?php echo htmlspecialchars($cron_time_backup, ENT_QUOTES, 'UTF-8'); ?></span>
            <textarea rows="2" cols="110" readonly="readonly" onfocus="this.select();"><?php echo htmlspecialchars($cron_command_backup, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <span class="help"><?php echo $text_rates_note; ?></span></td>
        </tr>
        <tr>
          <td><?php echo $text_rates_last_run; ?></td>
          <td><?php if ($rates_last_success) { ?>
            <span class="enabled"><?php echo $text_rates_success; ?></span>
            <?php echo htmlspecialchars($rates_last_success['date'], ENT_QUOTES, 'UTF-8'); ?>
            <span class="help"><?php echo htmlspecialchars($rates_last_success['message'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php } else { ?>
            <?php echo $text_rates_never; ?>
            <?php } ?>
            <?php if ($rates_last_failed) { ?>
            <br /><span class="disabled"><?php echo $text_rates_error; ?></span>
            <?php echo htmlspecialchars($rates_last_failed['date'], ENT_QUOTES, 'UTF-8'); ?>
            <span class="help"><?php echo htmlspecialchars($rates_last_failed['message'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php } ?></td>
        </tr>
      </table>
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
				$('form').submit();
			},
			cancel: function() {}
		}
	});
});
//--></script>

<?php echo $footer; ?>