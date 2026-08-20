<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <div style="float:right;">
    <a href="<?php echo $one_page_cart; ?>" title="<?php echo $text_cart; ?>" style="margin-left:25px;"><img src="catalog/view/theme/<?php echo $template; ?>/image/cart.png" alt="<?php echo $text_cart; ?>" /></a>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if (!$logged && $guest_checkout) { ?>
    <div class="guest-notice"><?php echo $text_guest_login; ?></div>
  <?php } ?>
  <?php if (!empty($attention)) { ?>
    <div class="attention"><?php echo $attention; ?><img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" class="close" /></div>
  <?php } ?>
  <?php if (!empty($error_warning)) { ?>
    <div class="warning"><?php echo $error_warning; ?><img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" class="close" /></div>
  <?php } ?>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
    <div class="checkout-one-page">
      <div class="checkout-page-left">
        <table class="address-options">
          <tr>
            <td colspan="2"><h2><?php echo $text_checkout_payment_address; ?></h2></td>
          </tr>
          <tr>
            <td colspan="2"><label for="firstname" class="hidden">firstname</label>
              <input type="text" name="firstname" id="firstname" placeholder="<?php echo $entry_firstname; ?>" value="<?php echo $firstname; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_firstname) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_firstname; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="lastname" class="hidden">lastname</label>
              <input type="text" name="lastname" id="lastname" placeholder="<?php echo $entry_lastname; ?>" value="<?php echo $lastname; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_lastname) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_lastname; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="email" class="hidden">email</label>
              <input type="text" name="email" id="email" placeholder="<?php echo $entry_email; ?>" value="<?php echo $email; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_email) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_email; ?></div></td></tr>
          <?php } ?>
          <?php if ($one_page_phone) { ?>
            <tr>
              <td colspan="2"><label for="telephone" class="hidden">telephone</label>
                <input type="text" name="telephone" id="telephone" placeholder="<?php echo $entry_telephone; ?>" value="<?php echo $telephone; ?>" size="26" /> <span class="required">*</span>
              </td>
            </tr>
            <?php if ($error_telephone) { ?>
              <tr><td colspan="2"><div class="error"><?php echo $error_telephone; ?></div></td></tr>
            <?php } ?>
          <?php } ?>
          <?php if ($one_page_gender) { ?>
            <tr>
              <td colspan="2">
                <?php if ($gender == 0) { ?>
                  <input type="radio" name="gender" value="0" checked="checked" /><?php echo $text_male; ?>&nbsp;&nbsp;
                  <input type="radio" name="gender" value="1" /><?php echo $text_female; ?>
                <?php } else { ?>
                  <input type="radio" name="gender" value="0" /><?php echo $text_male; ?>&nbsp;&nbsp;
                  <input type="radio" name="gender" value="1" checked="checked" /><?php echo $text_female; ?>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          <?php if ($one_page_dob) { ?>
            <tr>
              <td colspan="2"><label for="date-of-birth" class="hidden">date of birth</label>
                <input type="text" name="date_of_birth" id="date-of-birth" placeholder="<?php echo $entry_date_of_birth; ?>" value="<?php echo $date_of_birth; ?>" size="26" /> <span class="required">*</span>
              </td>
            </tr>
            <?php if ($error_date_of_birth) { ?>
              <tr><td colspan="2"><div class="error"><?php echo $error_date_of_birth; ?></div></td></tr>
            <?php } ?>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="company" class="hidden">company</label>
              <input type="text" name="company" id="company" placeholder="<?php echo $entry_company; ?>" value="<?php echo $company; ?>" size="26" />
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div style="display:<?php echo (count($customer_groups) > 1 ? 'table-row' : 'none'); ?>;">
                <?php echo $entry_customer_group; ?><br />
                <?php foreach ($customer_groups as $customer_group) { ?>
                  <input type="radio" name="customer_group_id" value="<?php echo $customer_group['customer_group_id']; ?>" id="customer-group-id<?php echo $customer_group['customer_group_id']; ?>"<?php echo ($customer_group['customer_group_id'] == $customer_group_id) ? ' checked="checked"' : ''; ?> />
                  <label for="customer-group-id<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></label><br />
                <?php } ?>
              </div>
            </td>
          </tr>
          <tr id="company-id-display">
            <td colspan="2"><label for="company-id" class="hidden">company id</label>
              <input type="text" name="company_id" id="company-id" placeholder="<?php echo $entry_company_id; ?>" value="<?php echo $company_id; ?>" size="26" />
            </td>
          </tr>
          <?php if ($error_company_id) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_company_id; ?></div></td></tr>
          <?php } ?>
          <tr id="tax-id-display">
            <td colspan="2"><label for="tax-id" class="hidden">tax id</label>
              <input type="text" name="tax_id" id="tax-id" placeholder="<?php echo $entry_tax_id; ?>" value="<?php echo $tax_id; ?>" size="26" />
            </td>
          </tr>
          <?php if ($error_tax_id) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_tax_id; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="address-1" class="hidden">address 1</label>
              <input type="text" name="address_1" id="address-1" placeholder="<?php echo $entry_address_1; ?>" value="<?php echo $address_1; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_address_1) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_address_1; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="address-2" class="hidden">address 2</label>
              <input type="text" name="address_2" id="address-2" placeholder="<?php echo $entry_address_2; ?>" value="<?php echo $address_2; ?>" size="26" />
            </td>
          </tr>
          <tr>
            <td colspan="2"><label for="city" class="hidden">city</label>
              <input type="text" name="city" id="city" placeholder="<?php echo $entry_city; ?>" value="<?php echo $city; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_city) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_city; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="postcode" class="hidden">postcode</label>
              <input type="text" name="postcode" id="postcode" placeholder="<?php echo $entry_postcode; ?>" value="<?php echo $postcode; ?>" size="26" /> <span id="payment-postcode-required" class="required">*</span>
            </td>
          </tr>
          <?php if ($error_postcode) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_postcode; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2">
              <select name="country_id">
                <option value=""><?php echo $text_select; ?></option>
                <?php foreach ($countries as $country) { ?>
                  <option value="<?php echo $country['country_id']; ?>"<?php echo ($country['country_id'] === $country_id) ? ' selected="selected"' : ''; ?>>
                  <?php echo (strlen($country['name']) > 24) ? substr(strip_tags(html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8')), 0, 22) . '..' : html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <?php if ($error_country) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_country; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><select name="zone_id"></select> <span class="required">*</span></td>
          </tr>
          <?php if ($error_zone) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_zone; ?></div></td></tr>
          <?php } ?>
        </table>
        <div class="address-checkbox">
          <input type="checkbox" name="check_shipping_address" value="1"<?php echo ($check_shipping_address === 1) ? ' checked="checked"' : ''; ?> /> <?php echo $entry_shipping; ?>
        </div>
        <table class="address-options" id="shipping-address-display">
          <tr>
            <td colspan="2"><h2><?php echo $text_checkout_shipping_address; ?></h2></td>
          </tr>
          <tr>
            <td colspan="2"><label for="shipping-firstname" class="hidden">shipping firstname</label>
              <input type="text" name="shipping_firstname" id="shipping-firstname" placeholder="<?php echo $entry_firstname; ?>" value="<?php echo $shipping_firstname; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_shipping_firstname) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_firstname; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="shipping-lastname" class="hidden">shipping lastname</label>
              <input type="text" name="shipping_lastname" id="shipping-lastname" placeholder="<?php echo $entry_lastname; ?>" value="<?php echo $shipping_lastname; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_shipping_lastname) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_lastname; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="shipping-company" class="hidden">shipping company</label>
              <input type="text" name="shipping_company" id="shipping-company" placeholder="<?php echo $entry_company; ?>" value="<?php echo $shipping_company; ?>" size="26" />
            </td>
          </tr>
          <tr>
            <td colspan="2"><label for="shipping-address-1" class="hidden">shipping address 1</label>
              <input type="text" name="shipping_address_1" id="shipping-address-1" placeholder="<?php echo $entry_address_1; ?>" value="<?php echo $shipping_address_1; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_shipping_address_1) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_address_1; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="shipping-address-2" class="hidden">shipping address 2</label>
              <input type="text" name="shipping_address_2" id="shipping-address-2" placeholder="<?php echo $entry_address_2; ?>" value="<?php echo $shipping_address_2; ?>" size="26" />
            </td>
          </tr>
          <tr>
            <td colspan="2"><label for="shipping-city" class="hidden">shipping city</label>
              <input type="text" name="shipping_city" id="shipping-city" placeholder="<?php echo $entry_city; ?>" value="<?php echo $shipping_city; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_shipping_city) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_city; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><label for="shipping-postcode" class="hidden">shipping postcode</label>
              <input type="text" name="shipping_postcode" id="shipping-postcode" placeholder="<?php echo $entry_postcode; ?>" value="<?php echo $shipping_postcode; ?>" size="26" /> <span class="required">*</span>
            </td>
          </tr>
          <?php if ($error_shipping_postcode) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_postcode; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2">
              <select name="shipping_country_id">
                <option value=""><?php echo $text_select; ?></option>
                <?php foreach ($countries as $country) { ?>
                  <option value="<?php echo $country['country_id']; ?>"<?php echo ($country['country_id'] === $shipping_country_id) ? ' selected="selected"' : ''; ?>>
                  <?php echo (strlen($country['name']) > 24) ? substr(strip_tags(html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8')), 0, 22) . '..' : html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <?php if ($error_shipping_country) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_country; ?></div></td></tr>
          <?php } ?>
          <tr>
            <td colspan="2"><select name="shipping_zone_id"></select> <span class="required">*</span></td>
          </tr>
          <?php if ($error_shipping_zone) { ?>
            <tr><td colspan="2"><div class="error"><?php echo $error_shipping_zone; ?></div></td></tr>
          <?php } ?>
        </table>
        <div class="address-checkbox"></div>
      </div>
      <div class="spacer"></div>
      <div class="checkout-page-right">
        <table class="order-options">
          <tr>
            <td><h2><?php echo $text_shipping_method; ?></h2></td>
            <td class="spacer"></td>
            <td><h2><?php echo $text_payment_method; ?></h2></td>
          </tr>
          <tr>
            <td id="shipping-method">
              <?php if ($shipping_methods) { ?>
                <?php if ($error_shipping_method) { ?>
                  <div class="attention" style="margin:5px 0;"><?php echo $error_shipping_method; ?></div>
                <?php } ?>
                <table id="shipping-lock" class="radio" style="margin-bottom:2px;">
                <?php foreach ($shipping_methods as $shipping_method) { ?>
                  <?php if (!$shipping_method['error']) { ?>
                    <?php foreach ($shipping_method['quote'] as $quote) { ?>
                      <tr class="highlight">
                        <td><input type="radio" name="shipping_method" value="<?php echo $quote['code']; ?>" id="<?php echo $quote['code']; ?>"<?php echo ($quote['code'] === $shipping_method_code) ? ' checked="checked"' : ''; ?> /></td>
                        <td><label for="<?php echo $quote['code']; ?>"><?php echo $quote['title']; ?></label></td>
                        <td style="text-align:right;"><label for="<?php echo $quote['code']; ?>"><?php echo $quote['text']; ?></label></td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr><td colspan="3"><div class="error"><?php echo $shipping_method['error']; ?></div></td></tr>
                  <?php } ?>
                <?php } ?>
                </table>
              <?php } ?>
            </td>
            <td class="spacer"></td>
            <td id="payment-method">
              <?php if ($payment_methods) { ?>
                <?php if ($error_payment_method) { ?>
                  <div class="attention" style="margin:5px 0;"><?php echo $error_payment_method; ?></div>
                <?php } ?>
                <table id="payment-lock" class="radio" style="margin-bottom:2px;">
                <?php foreach ($payment_methods as $payment_method) { ?>
                  <?php $apply_paypal_fee = ((substr($payment_method['code'], 0, 3) === 'pp_') || ($payment_method['code'] === 'paypal_email')) ? true : false; ?>
                  <tr class="highlight">
                    <td><input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" id="<?php echo $payment_method['code']; ?>"<?php echo ($payment_method['code'] === $payment_method_code) ? ' checked="checked"' : ''; ?> /></td>
                    <td>
                      <?php if ($payment_images) { ?>
                        <?php foreach ($payment_images as $payment_image) { ?>
                          <?php if ($payment_image['payment'] === strtolower($payment_method['code'])) { ?>
                            <?php if ($payment_image['status']) { ?>
                              <label for="<?php echo $payment_method['code']; ?>"><img src="<?php echo $payment_image['image']; ?>" title="<?php echo $payment_method['title']; ?>" alt="<?php echo $payment_method['title']; ?>" />
                                <?php if ($paypal_fee && $apply_paypal_fee) { ?><span> + <?php echo $paypal_fee; ?></span><?php } ?>
                              </label>
                            <?php } else { ?>
                              <label for="<?php echo $payment_method['code']; ?>"><?php echo $payment_method['title']; ?>
                                <?php if ($paypal_fee && $apply_paypal_fee) { ?><span> + <?php echo $paypal_fee; ?></span><?php } ?>
                              </label>
                            <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      <?php } else { ?>
                        <label for="<?php echo $payment_method['code']; ?>"><?php echo $payment_method['title']; ?>
                          <?php if ($paypal_fee && $apply_paypal_fee) { ?><span> + <?php echo $paypal_fee; ?></span><?php } ?>
                        </label>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
                </table>
              <?php } ?>
            </td>
          </tr>
        </table>

        <!-- ================================================================
             Payment gateway widgets
             ================================================================ -->

        <!-- Stripe — mounted by stripe_payments.js -->
        <div id="widget-stripe_payments" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <h3 style="margin:0 0 10px 0; font-size:14px;"><?php echo isset($text_credit_card) ? $text_credit_card : 'Card Details'; ?></h3>
          <label for="stripe-cc-owner" style="display:block; font-size:13px; margin-bottom:4px;"><?php echo isset($entry_cc_owner) ? $entry_cc_owner : 'Cardholder Name'; ?></label>
          <input type="text" id="stripe-cc-owner" placeholder="Name on card" size="30" style="margin-bottom:8px; width:100%; box-sizing:border-box;" />
          <div id="stripe-card-element" style="padding:8px; border:1px solid #ccc; border-radius:3px; background:#fff;"></div>
          <div id="stripe-card-errors" role="alert" style="color:#c0392b; font-size:13px; margin-top:6px; min-height:16px;"></div>
        </div>

        <!-- PayPal Express — mounted by pp_express.js -->
        <div id="widget-pp_express" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <div id="payment-widget-pp_express"></div>
          <div id="payment-widget-paylater-pp_express"></div>
        </div>

        <!-- Klarna Payments — mounted by klarna.js -->
        <div id="widget-klarna" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <div id="klarna-payments-container"></div>
          <div id="klarna-payments-errors" role="alert" style="color:#c0392b; font-size:13px; margin-top:6px; min-height:16px;"></div>
        </div>

        <!-- PayPal Standard — mounted by pp_standard.js -->
        <div id="widget-pp_standard" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <div class="pp-testmode-warning payment-pp-testmode-warning" style="display:none; margin-bottom:10px;">
            <span class="pp-warning-icon">&#9888;</span> PayPal Sandbox mode active — no real payments will be taken.
          </div>
          <p style="font-size:13px; color:#555; margin:0;">
            You will be redirected to PayPal to complete your payment securely after placing your order.
          </p>
          <img src="catalog/view/theme/<?php echo $template; ?>/image/payment/paypal-logo.png" alt="PayPal" style="height:24px; margin-top:10px; vertical-align:middle;" />
        </div>

        <!-- Sage Pay — mounted by sagepay.js -->
        <div id="widget-sagepay" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <div class="sagepay-testmode-warning payment-pp-testmode-warning" style="display:none; margin-bottom:10px;">
            <span class="pp-warning-icon">&#9888;</span> Sage Pay test mode active — no real payments will be taken.
          </div>
          <p style="font-size:13px; color:#555; margin:0;">
            You will be redirected to Sage Pay to complete your payment securely after placing your order.
          </p>
        </div>

        <!-- Bank Transfer — mounted by bank_transfer.js -->
        <div id="widget-bank_transfer" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <h3 id="bank-transfer-heading" style="margin:0 0 10px 0; font-size:14px;"></h3>
          <p id="bank-transfer-description" style="font-size:13px; color:#555; margin:0 0 8px 0;"></p>
          <div id="bank-transfer-details" style="font-size:13px; color:#333; white-space:pre-line; margin-bottom:8px;"></div>
          <p id="bank-transfer-payment-note" style="font-size:12px; color:#888; margin:0;"></p>
        </div>

        <!-- Cheque / Money Order — mounted by cheque.js -->
        <div id="widget-cheque" class="payment-gateway-widget" style="display:none; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:4px; background:#fafafa;">
          <h3 id="cheque-heading" style="margin:0 0 10px 0; font-size:14px;"></h3>
          <p style="font-size:13px; color:#333; margin:0 0 4px 0;"><span id="cheque-payable-label"></span><strong id="cheque-payable-to"></strong></p>
          <p style="font-size:13px; color:#333; white-space:pre-line; margin:0 0 8px 0;"><span id="cheque-address-label"></span><span id="cheque-address"></span></p>
          <p id="cheque-payment-note" style="font-size:12px; color:#888; margin:0;"></p>
        </div>

        <!-- Add future gateway widget divs here -->

        <div class="division"></div>
        <div id="checkout-one-cart"></div>
        <?php if ($checkout_comments) { ?>
        <div style="margin-bottom:10px;">
          <h2><?php echo $text_comments; ?></h2>
          <textarea name="comment" rows="4" style="width:100%;"><?php echo $comment; ?></textarea>
        </div>
        <?php } ?>
        <div>
          <?php if ($error_agree) { ?>
            <div class="attention" style="margin:5px 0;"><?php echo $error_agree; ?></div>
          <?php } ?>
          <?php if ($text_agree) { ?>
            <div class="buttons">
              <div class="right"><?php echo $text_agree; ?>
                <input type="checkbox" name="agree" value="1"<?php echo $agree ? ' checked="checked"' : ''; ?> />
              </div>
            </div>
          <?php } ?>
          <div id="order-errors"></div>
          <input type="button" value="<?php echo $button_continue; ?>" id="button-order" class="button" style="float:right; margin-bottom:10px;" />
        </div>
      </div>
    </div>
  </form>
  <div style="clear:both;"></div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<!-- ============================================================
     Payment gateway data blob + script loader
     ============================================================ -->

<script type="text/javascript">
  window.NIVOCART_PAYMENT_DATA = <?php echo $payment_widget_data; ?>;
  window.NIVOCART_TEMPLATE = '<?php echo $template; ?>';
</script>

<?php foreach ($payment_gateway_scripts as $ext_script) { ?>
  <script type="text/javascript" src="<?php echo $ext_script; ?>"></script>
<?php } ?>

<script type="text/javascript" src="catalog/view/javascript/payment/gateway_loader.js"></script>

<?php foreach ($local_gateway_scripts as $gw_script) { ?>
  <script type="text/javascript" src="<?php echo $gw_script; ?>"></script>
<?php } ?>

<!-- ============================================================
     Non-payment JS
     ============================================================ -->

<script type="text/javascript"><!--
$(document).ready(function() {
  if ($('input[name=\'check_shipping_address\']').is(':checked')) {
    $('#shipping-address-display').hide();
  } else {
    $('#shipping-address-display').show();
  }

  $('input[name=\'check_shipping_address\']').on('click', function() {
    if ($(this).is(':checked')) {
      $('#shipping-address-display').hide();
    } else {
      $('#shipping-address-display').show();
    }
  });
});
//--></script>

<script type="text/javascript"><!--
function loadPaymentZones(country_id) {
  if (!country_id) return;
  $.ajax({
    url: 'index.php?route=checkout/checkout/country&country_id=' + country_id,
    dataType: 'json',
    beforeSend: function() {
      $('.attention, .warning, .error').remove();
      $('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
    },
    complete: function() { $('.wait').remove(); },
    success: function(json) {
      if (json['postcode_required'] === '1') {
        $('#payment-postcode-required').show();
      } else {
        $('#payment-postcode-required').hide();
      }
      var html = '<option value=""><?php echo $text_select; ?></option>';
      if (json['zone'] && json['zone'].length > 0) {
        for (var i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';
          if (json['zone'][i]['zone_id'] === '<?php echo $zone_id; ?>') html += ' selected="selected"';
          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }
      $('select[name=\'zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
  });
}

function loadShippingZones(country_id) {
  if (!country_id) return;
  $.ajax({
    url: 'index.php?route=checkout/checkout/country&country_id=' + country_id,
    dataType: 'json',
    beforeSend: function() {
      $('.attention, .warning, .error').remove();
      $('select[name=\'shipping_country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
    },
    complete: function() { $('.wait').remove(); },
    success: function(json) {
      var html = '<option value=""><?php echo $text_select; ?></option>';
      if (json['zone'] && json['zone'].length > 0) {
        for (var i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';
          if (json['zone'][i]['zone_id'] === '<?php echo $shipping_zone_id; ?>') html += ' selected="selected"';
          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }
      $('select[name=\'shipping_zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
  });
}

$('select[name=\'country_id\']').on('change', function() {
  if (this.value === '') return;
  loadPaymentZones(this.value);
  if ($(this).val() != <?php echo $country_id; ?>) {
    $('#shipping-refresh').fadeIn(500); $('#shipping-lock').hide(); $('#payment-lock').hide();
  } else {
    $('#shipping-refresh').hide(); $('#shipping-lock').show(); $('#payment-lock').show();
  }
});

$('select[name=\'shipping_country_id\']').on('change', function() {
  if (this.value === '') return;
  loadShippingZones(this.value);
  if ($(this).val() != <?php echo $shipping_country_id; ?>) {
    $('#shipping-refresh').fadeIn(500); $('#shipping-lock').hide(); $('#payment-lock').hide();
  } else {
    $('#shipping-refresh').hide(); $('#shipping-lock').show(); $('#payment-lock').show();
  }
});

loadPaymentZones(<?php echo (int)$country_id; ?>);
loadShippingZones(<?php echo (int)$shipping_country_id; ?>);

$('#checkout-one-cart').load('index.php?route=checkout/checkout_cart');

$('body').on('change', 'input[name=\'shipping_method\']:checked', function() {
  $.ajax({
    url: 'index.php?route=checkout/checkout/shippingMethod',
    type: 'post',
    data: 'shipping_method=' + $('input[name=\'shipping_method\']:checked').attr('value'),
    dataType: 'json',
    success: function(json) {
      if (json['code']) {
        $('#checkout-one-cart').load('index.php?route=checkout/checkout_cart');
        var active = $('input[name="payment_method"]:checked').val();
        if (active && window.GatewayModules[active] && typeof window.GatewayModules[active].sessionUpdate === 'function') {
          window.GatewayModules[active].sessionUpdate();
        }
      }
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
  });
});

$('body').on('change', 'input[name=\'payment_method\']:checked', function() {
  $.ajax({
    url: 'index.php?route=checkout/checkout/paymentMethod',
    type: 'post',
    data: 'payment_method=' + $('input[name=\'payment_method\']:checked').attr('value'),
    dataType: 'json',
    success: function(json) {
      if (json['code']) { $('#checkout-one-cart').load('index.php?route=checkout/checkout_cart'); }
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
  });
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
  var date_of_birth = $('#date-of-birth');
  $(date_of_birth).mouseover(function() {
    $('#date-of-birth').datepicker({
      dateFormat: 'yy-mm-dd',
      changeYear: true,
      changeMonth: true,
      yearRange: '-100:+0'
    });
  });
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
  $('.colorbox').colorbox({
    overlayClose: true,
    opacity: 0.3,
    width: 600,
    height: 480
  });
});
//--></script>

<?php echo $footer; ?>