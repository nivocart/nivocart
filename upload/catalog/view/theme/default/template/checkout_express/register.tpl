<?php $nodata = (!$this->config->get('config_express_phone') && ($this->config->get('config_express_password') == 2)); ?>
<div class="left" <?php echo ($nodata) ? 'style="display:none;"' : ''; ?>>
  <h2><?php echo $text_your_details; ?></h2>
  <span>
    <span class="required">* </span><?php echo $entry_firstname; ?><br />
    <input type="text" name="firstname" value="<?php echo $firstname; ?>" class="large-field" />
    <br />
    <br />
  </span>
  <span>
    <span class="required">* </span><?php echo $entry_lastname; ?><br />
    <input type="text" name="lastname" value="<?php echo $lastname; ?>" class="large-field" />
    <br />
    <br />
  </span>
  <span>
    <span class="required">* </span><?php echo $entry_email; ?><br />
    <input type="text" name="email" value="<?php echo $email; ?>" class="large-field" />
    <br />
    <br />
  </span>
  <span <?php echo ($this->config->get('config_express_password') == 2) ? 'style="display:none;"' : ''; ?>>
    <span class="required">* </span><?php echo $entry_password; ?>
    <span id="express_password" style="color:#AAA; <?php echo (!$this->config->get('config_express_password')) ? "display:none;" : ""; ?>"><?php echo $text_express_generated; ?></span><br />
    <input type="text" name="password" oninput="$('#express_password').hide();" value="<?php echo $password; ?>" class="large-field" />
    <br/>
    <br/>
  </span>
  <span <?php echo ($this->config->get('config_express_phone') == 0) ? 'style="display:none;"' : ''; ?>>
    <?php echo ($this->config->get('config_express_phone') == 2) ? '<span class="required">* </span>' : ''; ?>
    <?php echo $entry_telephone; ?><br />
    <input type="text" name="telephone" value="<?php echo $telephone; ?>" class="large-field" />
    <br />
    <br />
  </span>
  <span <?php echo ($this->config->get('config_customer_gender') == 0) ? 'style="display:none;"' : ''; ?>>
    <?php echo $entry_gender; ?><br />
    <?php if ($gender) { ?>
      <input type="radio" name="gender" value="1" checked="checked" /><?php echo $text_female; ?>
      <input type="radio" name="gender" value="0" /><?php echo $text_male; ?>
    <?php } else { ?>
      <input type="radio" name="gender" value="1" /><?php echo $text_female; ?>
      <input type="radio" name="gender" value="0" checked="checked" /><?php echo $text_male; ?>
    <?php } ?>
  <br />
  <br />
  </span>
  <span <?php echo ($this->config->get('config_customer_dob') == 0) ? 'style="display:none;"' : ''; ?>>
  <span class="required">*</span> <?php echo $entry_date_of_birth; ?><br />
  <input type="text" name="date_of_birth" value="<?php echo $date_of_birth; ?>" id="date-of-birth" size="12" />
  <br />
  <br />
  </span>
<?php if ($this->config->get('config_express_newsletter') == 2) { ?>
  <input type="checkbox" name="newsletter" value="1" id="newsletter" checked="checked" />
  <label for="newsletter"><?php echo $entry_express_newsletter; ?></label>
<?php } ?>
  <br/>
  <br/>
</div>
<div class="<?php echo ($nodata) ? 'left' : 'right'; ?>" <?php echo (!$this->config->get('config_express_billing')) ? 'style="display:none;"' : ''; ?>>
  <h2><?php echo $text_your_address; ?></h2>
  <div style="display:<?php echo (count($customer_groups) > 1) ? 'table-row' : 'none'; ?>;">
    <?php echo $entry_customer_group; ?><br />
    <?php foreach ($customer_groups as $customer_group) { ?>
      <?php if ($customer_group['customer_group_id'] == $customer_group_id) { ?>
        <input type="radio" name="customer_group_id" value="<?php echo $customer_group['customer_group_id']; ?>" id="customer_group_id<?php echo $customer_group['customer_group_id']; ?>" checked="checked" />
        <label for="customer_group_id<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></label>
        <br />
      <?php } else { ?>
        <input type="radio" name="customer_group_id" value="<?php echo $customer_group['customer_group_id']; ?>" id="customer_group_id<?php echo $customer_group['customer_group_id']; ?>" />
        <label for="customer_group_id<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></label>
        <br />
      <?php } ?>
    <?php } ?>
    <br />
  </div>
  <div id="company_link" style="margin-bottom:5px;">
    <a onclick="$('#company-row').show(500);$(this).hide(100);"><?php echo $text_express_company_info; ?></a>
  </div>
  <div id="company-row" style="display:none;">
    <?php echo $entry_company; ?><br />
    <input type="text" name="company" value="" class="large-field" />
    <br />
    <br />
    <div id="company-id-display" style="display:none;">
      <span id="company-id-required" class="required">* </span><?php echo $entry_company_id; ?><br />
      <input type="text" name="company_id" value="" class="large-field" />
      <br />
      <br />
    </div>
  </div>
  <div id="tax-id-display" style="display:none;">
    <span id="tax-id-required" class="required">* </span><?php echo $entry_tax_id; ?><br />
    <input type="text" name="tax_id" value="" class="large-field" />
    <br />
    <br />
  </div>
  <span class="required">* </span><?php echo $text_express_address; ?><br />
  <input type="text" name="address_1" value="" class="large-field" />
  <br />
  <br />
  <span class="required">* </span><?php echo $entry_country; ?><br/>
  <select name="country_id" class="large-field">
    <option value=""><?php echo $text_select; ?></option>
    <?php foreach ($countries as $country) { ?>
      <?php if ($country['country_id'] == $country_id) { ?>
        <option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
      <?php } else { ?>
        <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
      <?php } ?>
    <?php } ?>
  </select>
  <br />
  <br />
  <span class="required">* </span><?php echo $entry_zone; ?><br />
  <select name="zone_id" id="zone" onchange="city=$('#zone option:selected').text();
    if (!$('#zone option:selected').val()) { city='';
    <?php if ($this->config->get('config_express_autofill')) { ?>
      document.getElementsByName('city')[0].value=city;
    <?php } ?> }
    if (city) { $('#city-row').show(500); } else { $('#city-row').hide(100); }" class="large-field">
  </select>
  <br />
  <br />
  <div id="city-row" style="display:none;">
    <span class="required">* </span><?php echo $entry_city; ?><br />
    <input type="text" name="city" value="" class="large-field" />
    <br />
    <br />
    <span id="payment-postcode-required" class="required">* </span><?php echo $entry_postcode; ?><br />
    <input type="text" name="postcode" value="<?php echo $postcode; ?>" class="large-field" />
    <br />
    <br />
    </span>
  </div>
<?php if ($shipping_required && $this->config->get('config_express_billing')) { ?>
  <input type="checkbox" name="shipping_address" value="1" id="shipping" checked="checked" />
  <label for="shipping"><?php echo $entry_shipping; ?></label>
  <br />
<?php } ?>
  <br />
</div>
<?php if ($text_agree) { ?>
  <div class="buttons">
    <div class="left"><?php echo $text_agree; ?>
      <input type="checkbox" name="agree" value="1" />
      <input type="button" value="<?php echo $button_express_go; ?>" id="button-register" class="button" />
    </div>
  </div>
<?php } else { ?>
  <div class="buttons">
    <div class="left">
      <input type="hidden" name="agree" value="1" />
      <input type="button" value="<?php echo $button_express_go; ?>" id="button-register" class="button" />
    </div>
  </div>
<?php } ?>

<script type="text/javascript"><!--
$('#payment-address input[name=\'customer_group_id\']:checked').on('change', function() {
	var customer_group = [];

<?php foreach ($customer_groups as $customer_group) { ?>
	customer_group[<?php echo $customer_group['customer_group_id']; ?>] = [];
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['company_id_display'] = '<?php echo $customer_group['company_id_display']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['company_id_required'] = '<?php echo $customer_group['company_id_required']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['tax_id_display'] = '<?php echo $customer_group['tax_id_display']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['tax_id_required'] = '<?php echo $customer_group['tax_id_required']; ?>';
<?php } ?>

	if (customer_group[this.value]) {
		if (customer_group[this.value]['company_id_display'] == '1') {
			$('#company-id-display').show(500);
		} else {
			$('#company-id-display').hide(100);
		}

		if (customer_group[this.value]['company_id_required'] == '1') {
			$('#company-id-required').show(500);
			$('#company-row').show(500);
			$('#company-link').hide(100);
		} else {
			$('#company-id-required').hide(100);
		}

		if (customer_group[this.value]['tax_id_display'] == '1') {
			$('#tax-id-display').show(500);
		} else {
			$('#tax-id-display').hide(100);
		}

		if (customer_group[this.value]['tax_id_required'] == '1') {
			$('#tax-id-required').show(500);
		} else {
			$('#tax-id-required').hide(100);
		}
	}
});

$('#payment-address input[name=\'customer_group_id\']:checked').trigger('change');
//--></script>

<script type="text/javascript"><!--
$('#payment-address select[name=\'country_id\']').on('change', function() {
	$.ajax({
		url: 'index.php?route=checkout_express/checkout/country&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#payment-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#payment-postcode-required').show(500);
			} else {
				$('#payment-postcode-required').hide(100);
			}

			var html = '<option value = ""><?php echo $text_select; ?></option>';

			if (json['zone'] != '') {
				for (var i = 0; i < json['zone'].length; i++) {
					html += '<option value="' + json['zone'][i]['zone_id'] + '"';

					if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
						html += ' selected="selected"';
					}

					html += '>' + json['zone'][i]['name'] + '</option>';
				}

			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}

			$('#payment-address select[name=\'zone_id\']').html(html);

			document.getElementsByName('city')[0].value='';

			$('#payment-address select[name=\'zone_id\']').trigger('change');

			if (json['zone'] == '') {
				$('#city-row').show(500);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('#payment-address select[name=\'country_id\']').trigger('change');
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date-of-birth').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<script type="text/javascript"><!--
$('.colorbox').colorbox({
	overlayClose: true,
	opacity: 0.3,
	width: 600,
	height: 480
});
//--></script>