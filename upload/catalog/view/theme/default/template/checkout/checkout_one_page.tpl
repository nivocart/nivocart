<?php echo $header; ?>
<?php echo $content_higher; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <div style="float:right;">
    <a href="<?php echo $one_page_cart; ?>" title="<?php echo $text_cart; ?>" style="margin-left:25px;"><img src="catalog/view/theme/<?php echo $template; ?>/image/cart.png" alt="<?php echo $text_cart; ?>" /></a>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($wrapping_status || $this->config->get('config_one_page_coupon') || $this->config->get('config_one_page_voucher') || $reward_point) { ?>
    <div style="margin-bottom:15px;">
      <?php if ($wrapping_status) { ?>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
          <?php if (isset($this->session->data['wrapping'])) { ?>
            <input type="submit" name="remove_wrapping" value="<?php echo $button_wrapping_remove; ?>" class="button-wrap-remove" />
          <?php } else { ?>
            <input type="submit" name="add_wrapping" value="<?php echo $button_wrapping_add; ?>" class="button-wrap-add" />
          <?php } ?>
        </form>
      <?php } ?>
      <?php if ($this->config->get('config_one_page_coupon')) { ?>
        <a onclick="$('#coupon').toggle(500);$('#voucher').hide(500);$('#reward').hide(500);" class="button"><?php echo $text_one_page_coupon; ?></a>
      <?php } ?>
      <?php if ($this->config->get('config_one_page_voucher')) { ?>
        <a onclick="$('#voucher').toggle(500);$('#coupon').hide(500);$('#reward').hide(500);" class="button"><?php echo $text_one_page_voucher; ?></a>
      <?php } ?>
      <?php if ($show_point && $reward_point) { ?>
        <a onclick="$('#reward').toggle(500);$('#coupon').hide(500);$('#voucher').hide(500);" class="button"><?php echo $text_one_page_reward; ?></a>
      <?php } ?>
      <div id="coupon" class="content" style="margin-top:10px; margin-bottom:20px; display:none;">
        <img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" onclick="dismiss1('coupon');" class="close" />
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
          <?php echo $entry_coupon; ?>&nbsp;
          <input type="text" name="coupon" value="<?php echo $coupon; ?>" />
          <input type="hidden" name="next" value="coupon" />
          &nbsp;
          <input type="submit" value="<?php echo $button_coupon; ?>" class="button" />
        </form>
      </div>
      <div id="voucher" class="content" style="margin-top:10px; margin-bottom:20px; display:none;">
        <img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" onclick="dismiss2('voucher');" class="close" />
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
          <?php echo $entry_voucher; ?>&nbsp;
          <input type="text" name="voucher" value="<?php echo $voucher; ?>" />
          <input type="hidden" name="next" value="voucher" />
          &nbsp;
          <input type="submit" value="<?php echo $button_voucher; ?>" class="button" />
        </form>
      </div>
      <div id="reward" class="content" style="margin-top:10px; margin-bottom:20px; display:none;">
        <img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" onclick="dismiss3('reward');" class="close" />
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
          <?php echo $entry_reward; ?>&nbsp;
          <input type="text" name="reward" value="<?php echo $reward; ?>" />
          <input type="hidden" name="next" value="reward" />
          &nbsp;
          <input type="submit" value="<?php echo $button_reward; ?>" class="button" />
        </form>
      </div>
    </div>
  <?php } ?>
  <?php if (!empty($attention)) { ?>
    <div class="attention"><?php echo $attention; ?><img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" class="close" /></div>
  <?php } ?>
  <?php if (!empty($success)) { ?>
    <div class="success"><?php echo $success; ?><img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" class="close" /></div>
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
                  <option value="<?php echo $country['country_id']; ?>"<?php echo ($country['country_id'] == $country_id) ? ' selected="selected"' : ''; ?>><?php echo (strlen($country['name']) > 24) ? substr(strip_tags(html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8')), 0, 22) . '..' : html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8'); ?></option>
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
          <input type="checkbox" name="check_shipping_address" value="1"<?php echo ($check_shipping_address == 1) ? ' checked="checked"' : ''; ?> /> <?php echo $entry_shipping; ?>
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
                  <option value="<?php echo $country['country_id']; ?>"<?php echo ($country['country_id'] == $shipping_country_id) ? ' selected="selected"' : ''; ?>><?php echo (strlen($country['name']) > 24) ? substr(strip_tags(html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8')), 0, 22) . '..' : html_entity_decode($country['name'], ENT_QUOTES, 'UTF-8'); ?></option>
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
              <a onclick="refresh();" id="shipping-refresh" class="button" style="margin:0 5px 5px 5px;"><i class="fa fa-refresh"></i></a>
              <?php if ($shipping_methods) { ?>
                <?php if ($error_shipping_method) { ?>
                  <div class="attention" style="margin:5px 0;"><?php echo $error_shipping_method; ?></div>
                <?php } ?>
                <table id="shipping-lock" class="radio" style="margin-bottom:2px;">
                <?php foreach ($shipping_methods as $shipping_method) { ?>
                  <?php if (!$shipping_method['error']) { ?>
                    <?php foreach ($shipping_method['quote'] as $quote) { ?>
                      <tr class="highlight">
                        <td><input type="radio" name="shipping_method" value="<?php echo $quote['code']; ?>" id="<?php echo $quote['code']; ?>"<?php echo ($quote['code'] == $shipping_method_code) ? ' checked="checked"' : ''; ?> /></td>
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
                  <?php $apply_paypal_fee = ((substr($payment_method['code'], 0, 3) == 'pp_') || ($payment_method['code'] == 'paypal_email')) ? true : false; ?>
                  <tr class="highlight">
                    <td><input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" id="<?php echo $payment_method['code']; ?>"<?php echo ($payment_method['code'] == $payment_method_code) ? ' checked="checked"' : ''; ?> /></td>
                    <td>
                      <?php if ($payment_images) { ?>
                        <?php foreach ($payment_images as $payment_image) { ?>
                          <?php if ($payment_image['payment'] == strtolower($payment_method['code'])) { ?>
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
             Stripe card widget — shown only when stripe_payments is selected
             ================================================================ -->
        <div id="stripe-widget" style="display:none; margin: 15px 0; padding: 12px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa;">
          <h3 style="margin: 0 0 10px 0; font-size: 14px;"><?php echo isset($text_credit_card) ? $text_credit_card : 'Card Details'; ?></h3>
          <label for="stripe-cc-owner" style="display:block; font-size:13px; margin-bottom:4px;"><?php echo isset($entry_cc_owner) ? $entry_cc_owner : 'Cardholder Name'; ?></label>
          <input type="text" id="stripe-cc-owner" placeholder="Name on card" size="30" style="margin-bottom:8px; width:100%; box-sizing:border-box;" />
          <div id="stripe-card-element" style="padding:8px; border:1px solid #ccc; border-radius:3px; background:#fff;"></div>
          <div id="stripe-card-errors" role="alert" style="color:#c0392b; font-size:13px; margin-top:6px; min-height:16px;"></div>
        </div>

        <div class="division"></div>
        <div id="checkout-one-cart"></div>
        <div style="margin-bottom:10px;">
          <h2><?php echo $text_comments; ?></h2>
          <textarea name="comment" rows="4" style="width:100%;"><?php echo $comment; ?></textarea>
        </div>
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

<!-- Stripe.js — only loaded when stripe_payments is available as a method -->
<?php foreach ($payment_methods as $pm) { ?>
  <?php if ($pm['code'] === 'stripe_payments') { ?>
    <script src="https://js.stripe.com/v3/"></script>
  <?php } ?>
<?php } ?>

<script type="text/javascript"><!--
// ============================================================================
// Stripe Elements setup
// Initialised once; widget shown/hidden based on payment method selection.
// ============================================================================
var stripeInstance = null;
var stripeCard = null;
var stripeInitialised = false;

var STRIPE_PUBLISHABLE_KEY = '';  // filled by intentCreate() response
var STRIPE_CLIENT_SECRET = '';  // filled by intentCreate() response
var STRIPE_INTENT_URL = 'index.php?route=payment/stripe_payments/intentCreate';
var STRIPE_SEND_URL = 'index.php?route=payment/stripe_payments/send';

function initStripeElements(publishableKey) {
    if (stripeInitialised) return;

    stripeInstance = Stripe(publishableKey);
    var elements = stripeInstance.elements();

    stripeCard = elements.create('card', {
        style: {
            base: {
                fontSize: '14px',
                color: '#333',
                '::placeholder': { color: '#aaa' }
            },
            invalid: { color: '#c0392b' }
        }
    });

    stripeCard.mount('#stripe-card-element');

    stripeCard.on('change', function(event) {
        document.getElementById('stripe-card-errors').textContent = event.error ? event.error.message : '';
    });

    stripeInitialised = true;
}

// Show/hide Stripe widget when payment method changes
$('body').on('change', 'input[name="payment_method"]', function() {
    if ($(this).val() === 'stripe_payments') {
        $('#stripe-widget').slideDown(300);
        // Fetch a fresh PaymentIntent when Stripe is selected
        fetchStripeIntent();
    } else {
        $('#stripe-widget').slideUp(300);
    }
});

// Also check on page load in case stripe_payments is pre-selected
$(document).ready(function() {
    if ($('input[name="payment_method"]:checked').val() === 'stripe_payments') {
        $('#stripe-widget').show();
        fetchStripeIntent();
    }
});

function fetchStripeIntent() {
    $.ajax({
        url: STRIPE_INTENT_URL,
        type: 'post',
        dataType: 'json',
        success: function(json) {
            if (json['error']) {
                document.getElementById('stripe-card-errors').textContent = json['error'];
                $('#button-order').attr('disabled', true);
                return;
            }
            STRIPE_CLIENT_SECRET = json['client_secret'];
            initStripeElements(json['publishable_key']);
            $('#button-order').attr('disabled', false);
        },
        error: function() {
            document.getElementById('stripe-card-errors').textContent =
                'Payment system unavailable. Please refresh and try again.';
            $('#button-order').attr('disabled', true);
        }
    });
}

// ============================================================================
// Form submission — extracted into submitForm() so all paths can call it
// ============================================================================
function submitForm() {
    $.ajax({
        url: 'index.php?route=checkout/checkout_one_page',
        type: 'post',
        data: $('#form').serialize(),
        dataType: 'json',
        beforeSend: function() {
            $('#button-order').attr('disabled', true);
            $('#button-order').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
            $('#order-errors').hide().empty();
        },
        complete: function() {
            $('#button-order').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.warning, .error, .attention').remove();

            if (json['redirect']) {
                location = json['redirect'];
            } else if (json['error']) {
                var errorHtml = '<div class="warning">';
                $.each(json['error'], function(field, message) {
                    errorHtml += message + '<img src="catalog/view/theme/<?php echo $template; ?>/image/close.png" alt="" class="close" /><br />';
                });
                errorHtml += '</div>';
                $('#order-errors').html(errorHtml).show();
                $('html, body').animate({ scrollTop: $('#order-errors').offset().top - 20 }, 500);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

// ============================================================================
// Place Order button — routes to Stripe or silent flow
// ============================================================================
$('#button-order').on('click', function() {
    var selectedPayment = $('input[name="payment_method"]:checked').val();

    if (selectedPayment === 'stripe_payments') {
        handleStripePayment(function() {
            // Card confirmed — now submit the form as normal
            submitForm();
        });

    // Future interactive gateways: add else if branches here
    // } else if (selectedPayment === 'klarna') {
    //     handleKlarnaPayment(function() { submitForm(); });

    } else {
        // Silent gateway — original behaviour unchanged
        submitForm();
    }
});

// ============================================================================
// Stripe payment handler
// Confirms the card with Stripe.js, then calls the callback on success.
// ============================================================================
function handleStripePayment(onSuccess) {
    var btn = document.getElementById('button-order');
    var errorDiv = document.getElementById('stripe-card-errors');
    var owner = document.getElementById('stripe-cc-owner').value;

    btn.disabled = true;
    errorDiv.textContent = '';
    btn.value = '<?php echo isset($text_wait) ? $text_wait : "Please wait..."; ?>';

    if (!STRIPE_CLIENT_SECRET) {
        errorDiv.textContent = 'Payment not ready. Please wait a moment and try again.';
        btn.disabled = false;
        btn.value = '<?php echo isset($button_continue) ? $button_continue : "Place Order"; ?>';
        return;
    }

    stripeInstance.confirmCardPayment(STRIPE_CLIENT_SECRET, {
        payment_method: {
            card: stripeCard,
            billing_details: { name: owner }
        }
    }).then(function(result) {
        if (result.error) {
            errorDiv.textContent = result.error.message;
            btn.disabled = false;
            btn.value = '<?php echo isset($button_continue) ? $button_continue : "Place Order"; ?>';
            return;
        }

        if (result.paymentIntent.status === 'succeeded') {
            // Store intent ID for the confirm controller to verify
            $.ajax({
                url: STRIPE_SEND_URL,
                type: 'post',
                data: { payment_intent_id: result.paymentIntent.id },
                dataType: 'json',
                success: function(json) {
                    if (json['error']) {
                        errorDiv.textContent = json['error'];
                        btn.disabled = false;
                        btn.value = '<?php echo isset($button_continue) ? $button_continue : "Place Order"; ?>';
                        return;
                    }
                    // Payment verified — proceed to submit the form
                    onSuccess();
                },
                error: function() {
                    errorDiv.textContent = 'Network error verifying payment. Please contact support.';
                    btn.disabled = false;
                    btn.value = '<?php echo isset($button_continue) ? $button_continue : "Place Order"; ?>';
                }
            });
        }
    });
}

//--></script>

<script type="text/javascript"><!--
// Shipping address toggle
var check_shipping_address = $('input[name=\'check_shipping_address\']').is(':checked');

if (check_shipping_address) {
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
//--></script>

<script type="text/javascript"><!--
// Customer group display logic
$('input[name=\'customer_group_id\']').on('change', function() {
  var customer_group = [];

  <?php foreach ($customer_groups as $customer_group) { ?>
  customer_group[<?php echo $customer_group['customer_group_id']; ?>] = {
    company_id_display: '<?php echo $customer_group['company_id_display']; ?>',
    company_id_required: '<?php echo $customer_group['company_id_required']; ?>',
    tax_id_display: '<?php echo $customer_group['tax_id_display']; ?>',
    tax_id_required: '<?php echo $customer_group['tax_id_required']; ?>'
  };
  <?php } ?>

  if (customer_group[this.value]) {
    $('#company-id-display').toggle(customer_group[this.value]['company_id_display'] === '1');
    $('#company-id-required').toggle(customer_group[this.value]['company_id_required'] === '1');
    $('#tax-id-display').toggle(customer_group[this.value]['tax_id_display'] === '1');
    $('#tax-id-required').toggle(customer_group[this.value]['tax_id_required'] === '1');
  }
});
$('input[name=\'customer_group_id\']:checked').trigger('change');
//--></script>

<script type="text/javascript"><!--
// Payment country zone loader
$('select[name=\'country_id\']').on('change', function() {
  if (this.value == '') return;

  $.ajax({
    url: 'index.php?route=checkout/checkout_one_page/country&country_id=' + this.value,
    dataType: 'json',
    beforeSend: function() {
      $('.attention, .warning, .error').remove();
      $('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
    },
    complete: function() { $('.wait').remove(); },
    success: function(json) {
      if (json['postcode_required'] == '1') {
        $('#payment-postcode-required').show();
      } else {
        $('#payment-postcode-required').hide();
      }

      var html = '<option value=""><?php echo $text_select; ?></option>';

      if (json['zone'] != '') {
        for (var i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';
          if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') html += ' selected="selected"';
          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }
      $('select[name=\'zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
  });
});

$('select[name=\'country_id\']').on('change', function() {
  if ($(this).val() != <?php echo $country_id; ?>) {
    $('#shipping-refresh').fadeIn(500); $('#shipping-lock').hide(); $('#payment-lock').hide();
  } else {
    $('#shipping-refresh').hide(); $('#shipping-lock').show(); $('#payment-lock').show();
  }
});

$('select[name=\'country_id\']').trigger('change');
//--></script>

<script type="text/javascript"><!--
// Shipping country zone loader — unchanged
$('select[name=\'shipping_country_id\']').on('change', function() {
  if (this.value == '') return;

  $.ajax({
    url: 'index.php?route=checkout/checkout_one_page/country&country_id=' + this.value,
    dataType: 'json',
    beforeSend: function() {
      $('.attention, .warning, .error').remove();
      $('select[name=\'shipping_country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /></span>');
    },
    complete: function() { $('.wait').remove(); },
    success: function(json) {
      var html = '<option value=""><?php echo $text_select; ?></option>';

      if (json['zone'] != '') {
        for (var i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';
          if (json['zone'][i]['zone_id'] == '<?php echo $shipping_zone_id; ?>') html += ' selected="selected"';
          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }
      $('select[name=\'shipping_zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
  });
});

$('select[name=\'shipping_country_id\']').on('change', function() {
  if ($(this).val() != <?php echo $shipping_country_id; ?>) {
    $('#shipping-refresh').fadeIn(500); $('#shipping-lock').hide(); $('#payment-lock').hide();
  } else {
    $('#shipping-refresh').hide(); $('#shipping-lock').show(); $('#payment-lock').show();
  }
});

$('select[name=\'shipping_country_id\']').trigger('change');
//--></script>

<script type="text/javascript"><!--
// Shipping and payment method change handlers
function refresh() {
  $('.attention, .warning, .error, .wait').remove();
  $('#form').append('<input type="hidden" id="refresh" name="refresh" value="1" />');
  $('#form').submit();
}

$('#checkout-one-cart').load('index.php?route=checkout/checkout_one_cart');

$('body').on('change', 'input[name=\'shipping_method\']:checked', function() {
  $.ajax({
    url: 'index.php?route=checkout/checkout_one_page/shippingMethod',
    type: 'post',
    data: 'shipping_method=' + $('input[name=\'shipping_method\']:checked').attr('value'),
    dataType: 'json',
    success: function(json) {
      if (json['code']) { $('#checkout-one-cart').load('index.php?route=checkout/checkout_one_cart'); }
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
  });
});

$('body').on('change', 'input[name=\'payment_method\']:checked', function() {
  $.ajax({
    url: 'index.php?route=checkout/checkout_one_page/paymentMethod',
    type: 'post',
    data: 'payment_method=' + $('input[name=\'payment_method\']:checked').attr('value'),
    dataType: 'json',
    success: function(json) {
      if (json['code']) { $('#checkout-one-cart').load('index.php?route=checkout/checkout_one_cart'); }
    },
    error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
  });
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
  $('#date-of-birth').datepicker({dateFormat: 'yy-mm-dd'});
  $('.colorbox').colorbox({ overlayClose: true, opacity: 0.3, width: 600, height: 480 });
});

function dismiss1(coupon) { document.getElementById('coupon').style.display = 'none'; }
function dismiss2(voucher) { document.getElementById('voucher').style.display = 'none'; }
function dismiss3(reward) { document.getElementById('reward').style.display = 'none'; }
//--></script>

<?php echo $footer; ?>