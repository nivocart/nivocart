<div id="global-notifications"></div>
<table id="global-table" class="form-modal">
  <tr>
    <td><?php echo $entry_gl_store; ?></td>
    <td><select name="gl_store_id">
      <option value="-1" selected="selected"><?php echo $text_gl_store_nochange; ?></option>
      <?php foreach ($stores as $store) { ?>
      <option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
      <?php } ?>
    </select></td>
  </tr>
  <tr>
    <td><?php echo $entry_gl_shipping; ?></td>
    <td><select name="gl_shipping">
      <option value="1"><?php echo $text_enabled; ?></option>
      <option value="0"><?php echo $text_disabled; ?></option>
    </select></td>
  </tr>
  <tr>
    <td><?php echo $entry_gl_subtract; ?></td>
    <td><select name="gl_subtract">
      <option value="1"><?php echo $text_enabled; ?></option>
      <option value="0"><?php echo $text_disabled; ?></option>
    </select></td>
  </tr>
  <tr>
    <td><?php echo $entry_gl_length_class; ?></td>
    <td><select name="gl_length_class_id">
      <?php foreach ($length_classes as $length_class) { ?>
      <option value="<?php echo $length_class['length_class_id']; ?>"<?php if ($length_class['length_class_id'] == $default_length_class_id) { ?> selected="selected"<?php } ?>><?php echo $length_class['title']; ?> (<?php echo $length_class['unit']; ?>)</option>
      <?php } ?>
    </select></td>
  </tr>
  <tr>
    <td><?php echo $entry_gl_weight_class; ?></td>
    <td><select name="gl_weight_class_id">
      <?php foreach ($weight_classes as $weight_class) { ?>
      <option value="<?php echo $weight_class['weight_class_id']; ?>"<?php if ($weight_class['weight_class_id'] == $default_weight_class_id) { ?> selected="selected"<?php } ?>><?php echo $weight_class['title']; ?> (<?php echo $weight_class['unit']; ?>)</option>
      <?php } ?>
    </select></td>
  </tr>
</table>
<div style="margin:20px; text-align:right;">
  <img src="view/image/loading.gif" alt="" id="img-global-update" style="display:none;" />
  <a id="button-global-update" class="button ripple" style="font-size:12px; color:#FFF;"><?php echo $button_submit; ?></a>
</div>

<script type="text/javascript"><!--
$('body').on('click', '#button-global-update', function() {
	$('#global-notifications').html('');
	$('#img-global-update').show();
	$('div.success').remove();

	$.ajax({
		url:'index.php?route=catalog/product/updateGlobal&token=<?php echo $token; ?>',
		type:'post',
		dataType: 'json',
		data: $.param($('#update-global-dialog').find('input[type="hidden"], select')),
		success: function(json) {
			$('#img-global-update').hide();

			if (json['success']) {
				$('#update-global-dialog').dialog('close');

				$('.box').before('<div class="success">' + json['success'] + '</div>');
			} else if (json['error']) {
				var error = '';

				if (json['error'] instanceof Array) {
					for (var i=0; i<json['error'].length; i++) {
						error += '<div>' + json['error'][i] + '</div>';
					}
				} else {
					error = json['error'];
				}
				$('#global-notifications').html('<div class="warning">' + error + '</div>');
			} else {
				alert('Unsupported response!');
			}
		},
		failure: function() {
			$('#img-global-update').hide();
			alert('Ajax failure!');
		}
	});
});
//--></script>