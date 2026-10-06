<div id="points-notifications"></div>
<table id="points-table" class="form-modal">
  <tr>
    <td><?php echo $entry_pt_points; ?></td>
    <td><select name="pt_points">
      <option value="-1" selected="selected"><?php echo $text_pt_nochange; ?></option>
      <option value="1"><?php echo $text_pt_calculate; ?></option>
    </select> <span class="form-note"><?php echo $reward_rate; ?></span></td>
  </tr>
  <tr class="separator">
    <td colspan="2"></td>
  </tr>
  <tr>
    <td><?php echo $entry_pt_customer_group; ?></td>
    <td><select name="pt_customer_group_id">
      <option value="0"><?php echo $text_pt_all_groups; ?></option>
      <?php foreach ($customer_groups as $customer_group) { ?>
      <option value="<?php echo $customer_group['customer_group_id']; ?>"<?php if ($customer_group['customer_group_id'] == $default_customer_group_id) { ?> selected="selected"<?php } ?>><?php echo $customer_group['name']; ?></option>
      <?php } ?>
    </select></td>
  </tr>
  <tr>
    <td><?php echo $entry_pt_reward; ?></td>
    <td><select name="pt_reward">
      <option value="-1" selected="selected"><?php echo $text_pt_nochange; ?></option>
      <?php foreach ($reward_steps as $reward_step) { ?>
      <option value="<?php echo $reward_step['value']; ?>"><?php echo $reward_step['text']; ?></option>
      <?php } ?>
    </select></td>
  </tr>
</table>
<div class="form-modal-buttons">
  <a id="button-points-reset" class="button-delete ripple"><?php echo $button_pt_reset; ?></a>
  <span>
    <img src="view/image/loading.gif" alt="" id="img-points-update" hidden />
    <a id="button-points-update" class="button ripple"><?php echo $button_submit; ?></a>
  </span>
</div>

<script type="text/javascript"><!--
function pointsSubmit(reset) {
	$('#points-notifications').html('');
	$('#img-points-update').show();
	$('div.success').remove();

	var data = $.param($('#update-points-dialog').find('select'));

	if (reset) {
		data += '&pt_action=reset';
	}

	$.ajax({
		url: 'index.php?route=catalog/product/updatePoints&token=<?php echo $token; ?>',
		type: 'post',
		dataType: 'json',
		data: data,
		success: function(json) {
			$('#img-points-update').hide();

			if (json['success']) {
				$('#update-points-dialog').dialog('close');

				$('.box').before('<div class="success">' + json['success'] + '</div>');
			} else if (json['error']) {
				$('#points-notifications').html('<div class="warning">' + json['error'] + '</div>');
			} else {
				alert('Unsupported response!');
			}
		},
		error: function() {
			$('#img-points-update').hide();
			alert('Ajax failure!');
		}
	});
}

$('body').off('click.points').on('click.points', '#button-points-update', function() {
	pointsSubmit(false);
});

$('body').on('click.points', '#button-points-reset', function() {
	$.confirm({
		title: '<?php echo $text_pt_reset_title; ?>',
		content: '<?php echo addslashes($text_pt_reset_confirm); ?>',
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				pointsSubmit(true);
			},
			cancel: function() {}
		}
	});
});
//--></script>