<?php echo $header; ?>
<div id="content" style="max-width: 480px; margin: 40px auto; padding: 0 20px;">
  <h1 style="font-size: 20px; margin-bottom: 20px;"><?php echo $text_title; ?></h1>
  <?php if ($testmode) { ?>
  <div class="payment-pp-testmode-warning">
    <span class="pp-warning-icon">&#9888;</span> <?php echo $text_testmode; ?>
  </div>
  <?php } ?>
  <p style="margin-bottom: 24px; color: #555; font-size: 14px;">
    Your order has been placed. Click the button below to complete your payment securely on PayPal.
  </p>

  <!-- All PayPal field data stored as data-* attributes.
       pp_standard_redirect.js reads these and builds the form on button click. -->
  <div id="pp-paypal-data"
    data-action="<?php echo htmlspecialchars($pp_data['action'], ENT_QUOTES, 'UTF-8'); ?>"
    data-business="<?php echo htmlspecialchars($pp_data['business'], ENT_QUOTES, 'UTF-8'); ?>"
    data-currency="<?php echo htmlspecialchars($pp_data['currency'], ENT_QUOTES, 'UTF-8'); ?>"
    data-paymentaction="<?php echo htmlspecialchars($pp_data['paymentaction'], ENT_QUOTES, 'UTF-8'); ?>"
    data-lc="<?php echo htmlspecialchars($pp_data['lc'], ENT_QUOTES, 'UTF-8'); ?>"
    data-invoice="<?php echo htmlspecialchars($pp_data['invoice'], ENT_QUOTES, 'UTF-8'); ?>"
    data-custom="<?php echo (int)$pp_data['custom']; ?>"
    data-first-name="<?php echo htmlspecialchars($pp_data['first_name'], ENT_QUOTES, 'UTF-8'); ?>"
    data-last-name="<?php echo htmlspecialchars($pp_data['last_name'], ENT_QUOTES, 'UTF-8'); ?>"
    data-address1="<?php echo htmlspecialchars($pp_data['address1'], ENT_QUOTES, 'UTF-8'); ?>"
    data-address2="<?php echo htmlspecialchars($pp_data['address2'], ENT_QUOTES, 'UTF-8'); ?>"
    data-city="<?php echo htmlspecialchars($pp_data['city'], ENT_QUOTES, 'UTF-8'); ?>"
    data-zip="<?php echo htmlspecialchars($pp_data['zip'], ENT_QUOTES, 'UTF-8'); ?>"
    data-country="<?php echo htmlspecialchars($pp_data['country'], ENT_QUOTES, 'UTF-8'); ?>"
    data-email="<?php echo htmlspecialchars($pp_data['email'], ENT_QUOTES, 'UTF-8'); ?>"
    data-return-url="<?php echo htmlspecialchars($pp_data['return_url'], ENT_QUOTES, 'UTF-8'); ?>"
    data-notify-url="<?php echo htmlspecialchars($pp_data['notify_url'], ENT_QUOTES, 'UTF-8'); ?>"
    data-cancel-url="<?php echo htmlspecialchars($pp_data['cancel_url'], ENT_QUOTES, 'UTF-8'); ?>"
    data-discount="<?php echo (float)$pp_data['discount']; ?>"
    data-products="<?php echo htmlspecialchars(json_encode($pp_data['products']), ENT_QUOTES, 'UTF-8'); ?>">

    <button type="button" id="pp-redirect-btn" class="btn-paypal">
      <img src="catalog/view/theme/<?php echo $template; ?>/image/payment/paypal-logo.png" alt="PayPal" class="btn-paypal-logo" />
      <span class="btn-paypal-text"><?php echo $button_confirm; ?></span>
    </button>
  </div>
  <p style="margin-top: 20px; font-size: 12px; color: #999;">
    <a href="<?php echo htmlspecialchars($pp_data['cancel_url'], ENT_QUOTES, 'UTF-8'); ?>" style="color: #999;">&#8592; Return to checkout</a>
  </p>
</div>

<script type="text/javascript" src="catalog/view/javascript/payment/pp_standard_redirect.js"></script>

<?php echo $footer; ?>
