<?php echo $header; ?>
<div id="content" style="max-width: 480px; margin: 60px auto; padding: 0 20px; text-align: center;">
  <h1 style="font-size: 20px; margin-bottom: 16px;"><?php echo $text_title; ?></h1>
  <p style="color: #555; font-size: 14px; margin-bottom: 24px;">
    Redirecting you to Sage Pay to complete your payment securely&hellip;
  </p>
  <div class="sagepay-spinner" style="margin: 0 auto 20px auto; width: 32px; height: 32px;
       border: 3px solid #ddd; border-top-color: #555; border-radius: 50%;
       animation: sagepay-spin 0.8s linear infinite;"></div>
  <style>
    @keyframes sagepay-spin { to { transform: rotate(360deg); } }
  </style>

  <!-- All Sage Pay field data — auto-submitted by sagepay_redirect.js -->
  <div id="sagepay-data"
    data-action="<?php echo htmlspecialchars($sagepay_data['action'], ENT_QUOTES, 'UTF-8'); ?>"
    data-transaction="<?php echo htmlspecialchars($sagepay_data['transaction'], ENT_QUOTES, 'UTF-8'); ?>"
    data-vendor="<?php echo htmlspecialchars($sagepay_data['vendor'], ENT_QUOTES, 'UTF-8'); ?>"
    data-crypt="<?php echo htmlspecialchars($sagepay_data['crypt'], ENT_QUOTES, 'UTF-8'); ?>">
  </div>

  <noscript>
    <p style="color:#c0392b; font-size:13px;">
      JavaScript is required to complete this payment. Please enable JavaScript and refresh this page.
    </p>
  </noscript>
</div>

<script type="text/javascript" src="catalog/view/javascript/payment/sagepay_redirect.js"></script>

<?php echo $footer; ?>