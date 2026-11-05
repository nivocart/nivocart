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
      <h1><img src="view/image/voucher.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $insert; ?>" class="button ripple"><?php echo $button_insert; ?></a>
        <a id="delete" class="button-delete ripple"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content-body">
    <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align:center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
              <label for="check-all"><span></span></label></td>
              <td class="left"><?php if ($sort === 'v.code') { ?>
                <a href="<?php echo $sort_code; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_code; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_code; ?>"><?php echo $column_code; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="left"><?php if ($sort === 'v.from_name') { ?>
                <a href="<?php echo $sort_from; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_from; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_from; ?>"><?php echo $column_from; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="left"><?php if ($sort === 'v.to_name') { ?>
                <a href="<?php echo $sort_to; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_to; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_to; ?>"><?php echo $column_to; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="left"><?php if ($sort === 'v.design') { ?>
                <a href="<?php echo $sort_design; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_design; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_design; ?>"><?php echo $column_design; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="right"><?php if ($sort === 'v.amount') { ?>
                <a href="<?php echo $sort_amount; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_amount; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_amount; ?>"><?php echo $column_amount; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="right"><?php if ($sort === 'balance') { ?>
                <a href="<?php echo $sort_balance; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_balance; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_balance; ?>"><?php echo $column_balance; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="center"><?php if ($sort === 'state') { ?>
                <a href="<?php echo $sort_state; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_state; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_state; ?>"><?php echo $column_state; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="center"><?php if ($sort === 'v.date_sent') { ?>
                <a href="<?php echo $sort_sent; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_sent; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_sent; ?>"><?php echo $column_sent; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="center"><?php if ($sort === 'v.date_expire') { ?>
                <a href="<?php echo $sort_expire; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_expire; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_expire; ?>"><?php echo $column_expire; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="center"><?php if ($sort === 'v.date_added') { ?>
                <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
              <?php } else { ?>
                <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
              <?php } ?></td>
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
            <tr class="filter">
              <td></td>
              <td><input type="text" name="filter_code" value="<?php echo $filter_code; ?>" size="10" /></td>
              <td colspan="2"><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td></td>
              <td></td>
              <td></td>
              <td class="center"><select name="filter_state">
                <option value=""></option>
                <?php foreach ($states as $state) { ?>
                  <?php if ($state['value'] === $filter_state) { ?>
                    <option value="<?php echo $state['value']; ?>" selected="selected"><?php echo $state['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $state['value']; ?>"><?php echo $state['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select></td>
              <td></td>
              <td class="center"><input type="text" name="filter_date_expire" value="<?php echo $filter_date_expire; ?>" size="10" id="date-expire" /></td>
              <td class="center"><input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" size="10" id="date-added" /></td>
              <td style="text-align:right;"><a onclick="filter();" class="button-filter"><?php echo $button_filter; ?></a></td>
            </tr>
          </thead>
          <tbody>
          <?php if ($vouchers) { ?>
            <?php foreach ($vouchers as $voucher) { ?>
              <tr>
                <td style="text-align:center;"><?php if ($voucher['selected']) { ?>
                  <input type="checkbox" name="selected[]" value="<?php echo $voucher['voucher_id']; ?>" id="<?php echo $voucher['voucher_id']; ?>" class="checkbox" checked />
                  <label for="<?php echo $voucher['voucher_id']; ?>"><span></span></label>
                <?php } else { ?>
                  <input type="checkbox" name="selected[]" value="<?php echo $voucher['voucher_id']; ?>" id="<?php echo $voucher['voucher_id']; ?>" class="checkbox" />
                  <label for="<?php echo $voucher['voucher_id']; ?>"><span></span></label>
                <?php } ?></td>
                <td class="left"><?php echo $voucher['code']; ?></td>
                <td class="left"><?php echo $voucher['from']; ?></td>
                <td class="left"><?php echo $voucher['to']; ?></td>
                <td class="left"><?php echo $voucher['design']; ?></td>
                <td class="right"><?php echo $voucher['amount']; ?></td>
                <td class="right"><?php echo $voucher['balance']; ?></td>
                <?php if ($voucher['state'] === 'active') { ?>
                  <td class="center"><span class="enabled"><?php echo $voucher['state_text']; ?></span></td>
                <?php } elseif ($voucher['state'] === 'disabled' || $voucher['state'] === 'expired') { ?>
                  <td class="center"><span class="disabled"><?php echo $voucher['state_text']; ?></span></td>
                <?php } else { ?>
                  <td class="center"><?php echo $voucher['state_text']; ?></td>
                <?php } ?>
                <td class="center"><?php echo $voucher['sent']; ?></td>
                <td class="center"><?php echo $voucher['expire']; ?></td>
                <td class="center"><?php echo $voucher['date_added']; ?></td>
                <td class="right"><?php if ($voucher['can_send']) { ?>
                  <a onclick="sendVoucher('<?php echo $voucher['voucher_id']; ?>');" class="button-save"><?php echo $voucher['is_sent'] ? $text_resend : $text_send; ?></a>
                <?php } ?>
                <?php foreach ($voucher['action'] as $action) { ?>
                  <a href="<?php echo $action['href']; ?>" class="button-form animated fadeIn ripple"><?php echo $action['text']; ?></a>
                <?php } ?></td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td class="center" colspan="12"><?php echo $text_no_results; ?></td>
            </tr>
          <?php } ?>
          </tbody>
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
	url = 'index.php?route=sale/voucher&token=<?php echo $token; ?>';

	var filter_code = $('input[name=\'filter_code\']').prop('value');

	if (filter_code) {
		url += '&filter_code=' + encodeURIComponent(filter_code);
	}

	var filter_name = $('input[name=\'filter_name\']').prop('value');

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	var filter_state = $('select[name=\'filter_state\']').prop('value');

	if (filter_state) {
		url += '&filter_state=' + encodeURIComponent(filter_state);
	}

	var filter_date_expire = $('input[name=\'filter_date_expire\']').prop('value');

	if (filter_date_expire) {
		url += '&filter_date_expire=' + encodeURIComponent(filter_date_expire);
	}

	var filter_date_added = $('input[name=\'filter_date_added\']').prop('value');

	if (filter_date_added) {
		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
	}

	location = url;
}
//--></script>

<script type="text/javascript"><!--
window.addEventListener("keydown", function(event) {
	if (event.defaultPrevented) {
		return;
	}

	switch (event.key) {
	case "Enter": filter();
		break;
	default:
		return;
	}

	event.preventDefault();
}, true);
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date-expire').datepicker({dateFormat: 'yy-mm-dd'});
	$('#date-added').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<script type="text/javascript"><!--
function sendVoucher(voucher_id) {
	$.confirm({
		title: '<?php echo $text_confirm_send; ?>',
		content: '<?php echo $text_confirm_send_text; ?>',
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				$.ajax({
					url: 'index.php?route=sale/voucher/send&token=<?php echo $token; ?>&voucher_id=' + voucher_id,
					type: 'post',
					dataType: 'json',
					beforeSend: function() {
						$('.success, .warning').remove();
						$('.box').before('<div class="attention"><img src="view/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
					},
					complete: function() {
						$('.attention').remove();
					},
					success: function(json) {
						if (json['error']) {
							$('.box').before('<div class="warning">' + json['error'] + '</div>');
						}

						if (json['success']) {
							$('.box').before('<div class="success">' + json['success'] + '</div>');
						}
					}
				});
			},
			cancel: function() {}
		}
	});
}
//--></script>

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