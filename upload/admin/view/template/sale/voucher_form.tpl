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
      <h1><img src="view/image/voucher.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-general"><?php echo $tab_general; ?></a>
        <?php if ($voucher_id) { ?>
          <a href="#tab-history"><?php echo $tab_voucher_history; ?></a>
        <?php } ?>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <div id="tab-general">
        <table class="form">
          <?php if ($voucher_id) { ?>
          <tr>
            <td><?php echo $entry_state; ?></td>
            <td><?php echo $state; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_balance; ?></td>
            <td><?php echo $balance; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_sent; ?></td>
            <td><?php echo $sent; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td><?php echo (!$voucher_id) ? '' : '<span class="required">*</span> '; ?><?php echo $entry_code; ?></td>
            <td><?php if ($error_code) { ?>
              <input type="text" name="code" value="<?php echo $code; ?>" id="code" class="input-error" />
              <span class="error"><?php echo $error_code; ?></span>
            <?php } else { ?>
              <input type="text" name="code" value="<?php echo $code; ?>" id="code" />
            <?php } ?>
            <a onclick="generateCode();" class="button button-align ripple"><i class="fa fa-random"></i> &nbsp; <?php echo $button_generate; ?></a></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_from_name; ?></td>
            <td><?php if ($error_from_name) { ?>
              <input type="text" name="from_name" value="<?php echo $from_name; ?>" size="30" class="input-error" />
              <span class="error"><?php echo $error_from_name; ?></span>
            <?php } else { ?>
              <input type="text" name="from_name" value="<?php echo $from_name; ?>" size="30" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_from_email; ?></td>
            <td><?php if ($error_from_email) { ?>
              <input type="text" name="from_email" value="<?php echo $from_email; ?>" size="40" class="input-error" />
              <span class="error"><?php echo $error_from_email; ?></span>
            <?php } else { ?>
              <input type="text" name="from_email" value="<?php echo $from_email; ?>" size="40" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_to_name; ?></td>
            <td><?php if ($error_to_name) { ?>
              <input type="text" name="to_name" value="<?php echo $to_name; ?>" size="30" class="input-error" />
              <span class="error"><?php echo $error_to_name; ?></span>
            <?php } else { ?>
              <input type="text" name="to_name" value="<?php echo $to_name; ?>" size="30" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_to_email; ?></td>
            <td><?php if ($error_to_email) { ?>
              <input type="text" name="to_email" value="<?php echo $to_email; ?>" size="40" class="input-error" />
              <span class="error"><?php echo $error_to_email; ?></span>
            <?php } else { ?>
              <input type="text" name="to_email" value="<?php echo $to_email; ?>" size="40" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_design; ?></td>
            <td><select name="design">
              <?php foreach ($designs as $design_option) { ?>
                <?php if ($design_option['value'] === $design) { ?>
                  <option value="<?php echo $design_option['value']; ?>" selected="selected"><?php echo $design_option['name']; ?></option>
                <?php } else { ?>
                  <option value="<?php echo $design_option['value']; ?>"><?php echo $design_option['name']; ?></option>
                <?php } ?>
              <?php } ?>
            </select>
            <?php if ($error_design) { ?>
              <span class="error"><?php echo $error_design; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_message; ?></td>
            <td><?php if ($error_message) { ?>
              <textarea name="message" cols="40" rows="5" class="input-error"><?php echo $message; ?></textarea>
              <span class="error"><?php echo $error_message; ?></span>
            <?php } else { ?>
              <textarea name="message" cols="40" rows="5"><?php echo $message; ?></textarea>
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_amount; ?></td>
            <td><?php if ($error_amount) { ?>
              <input type="text" name="amount" value="<?php echo $amount; ?>" class="input-error" />
              <span class="error"><?php echo $error_amount; ?></span>
            <?php } else { ?>
              <input type="text" name="amount" value="<?php echo $amount; ?>" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_expire; ?></td>
            <td><?php if ($error_expire) { ?>
              <input type="text" name="date_expire" value="<?php echo $date_expire; ?>" id="date-expire" size="12" class="input-error" />
              <span class="error"><?php echo $error_expire; ?></span>
            <?php } else { ?>
              <input type="text" name="date_expire" value="<?php echo $date_expire; ?>" id="date-expire" size="12" />
            <?php } ?></td>
          </tr>
          <tr>
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
        </table>
        </div>
      <?php if ($voucher_id) { ?>
        <div id="tab-history">
          <div id="history"></div>
        </div>
      <?php } ?>
      </form>
    </div>
  </div>
</div>

<?php if ($voucher_id) { ?>
<script type="text/javascript"><!--
$('#history .pagination').on('click', 'a', function() {
	$('#history').load(this.href);
	return false;
});

$('#history').load('index.php?route=sale/voucher/history&token=<?php echo $token; ?>&voucher_id=<?php echo $voucher_id; ?>');
//--></script>
<?php } ?>

<script type="text/javascript"><!--
function generateCode() {
	$.ajax({
		url: '<?php echo $generate; ?>',
		type: 'get',
		dataType: 'json',
		success: function(json) {
			$('.warning').remove();

			if (json['error']) {
				$('.box').before('<div class="warning">' + json['error'] + '</div>');
			}

			if (json['code']) {
				$('#code').val(json['code']);
			}
		}
	});
}
//--></script>

<script type="text/javascript"><!--
$('#tabs a').tabs();

$(document).ready(function() {
	$('#date-expire').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<?php echo $footer; ?>