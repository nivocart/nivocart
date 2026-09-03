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
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><span class="required">*</span> <?php echo $entry_publishable_key; ?></td>
          <td><input type="text" name="stripe_payments_publishable_key" value="<?php echo $stripe_payments_publishable_key; ?>" size="42" />
          <?php if ($error_publishable_key) { ?>
            <span class="error"><?php echo $error_publishable_key; ?></span>
          <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_secret_key; ?></td>
          <td><input type="text" name="stripe_payments_secret_key" value="<?php echo $stripe_payments_secret_key; ?>" size="42" />
          <?php if ($error_secret_key) { ?>
            <span class="error"><?php echo $error_secret_key; ?></span>
          <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_webhook_secret; ?></td>
          <td><input type="text" name="stripe_payments_webhook_secret" value="<?php echo $stripe_payments_webhook_secret; ?>" size="42" />
          <?php if ($error_webhook_secret) { ?>
            <span class="error"><?php echo $error_webhook_secret; ?></span>
          <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_mode; ?></td>
          <td><select name="stripe_payments_mode">
            <?php if ($stripe_payments_mode === 'live') { ?>
              <option value="live" selected="selected"><?php echo $text_live; ?></option>
            <?php } else { ?>
              <option value="live"><?php echo $text_live; ?></option>
            <?php } ?>
            <?php if ($stripe_payments_mode === 'test') { ?>
              <option value="test" selected="selected"><?php echo $text_test; ?></option>
            <?php } else { ?>
              <option value="test"><?php echo $text_test; ?></option>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_method; ?></td>
          <td><select name="stripe_payments_method">
            <option value="charge" selected="selected"><?php echo $text_charge; ?></option>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_order_status; ?></td>
          <td><select name="stripe_payments_order_status_id">
            <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] === $stripe_payments_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_order_failed; ?></td>
          <td><select name="stripe_payments_order_failed_id">
            <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] === $stripe_payments_order_failed_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_order_disputed; ?></td>
          <td><select name="stripe_payments_order_disputed_id">
            <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] === $stripe_payments_order_disputed_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="stripe_payments_geo_zone_id">
            <option value="0"><?php echo $text_all_zones; ?></option>
            <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] === $stripe_payments_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_total; ?></td>
          <td><input type="text" name="stripe_payments_total" value="<?php echo $stripe_payments_total; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="stripe_payments_sort_order" value="<?php echo $stripe_payments_sort_order; ?>" size="1" /></td>
        </tr>
        <tr class="highlighted">
          <td><?php echo $entry_status; ?></td>
          <td><select name="stripe_payments_status">
            <?php if ($stripe_payments_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select></td>
        </tr>
      </table>
    </form>
    </div>
  </div>

<?php
// ---------------------------------------------------------------------------
// Webhook URL for this store — constructed from the live HTTP_HOST.
// Checks all common HTTPS indicators: $_SERVER['HTTPS'] is often absent
// behind reverse proxies, load balancers, and cPanel-managed hosts.
// HTTP_X_FORWARDED_PROTO and REQUEST_SCHEME cover those cases.
// ---------------------------------------------------------------------------
$_stripe_is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
    || (isset($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

$_stripe_webhook_url = ($_stripe_is_https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com') . '/catalog/webhooks/stripe.php';
?>
<div class="box" style="margin-top:20px;">
  <div class="heading">
    <h1><img src="view/image/api.png" alt="" /> Stripe Webhook Setup Reference</h1>
  </div>
  <div class="content">
    <table class="form">
      <tr>
        <td colspan="2" style="padding:10px 0 4px;">
          <strong>Step 1 &mdash; Register the endpoint in the Stripe Dashboard</strong><br>
          Go to <em>Developers &rarr; Webhooks &rarr; Add endpoint</em> and paste the URL below.
        </td>
      </tr>
      <tr>
        <td style="width:180px;">Endpoint URL</td>
        <td>
          <input type="text" value="<?php echo htmlspecialchars($_stripe_webhook_url); ?>" size="60" readonly onclick="this.select();" style="font-family:monospace;cursor:pointer;" title="Click to select" />
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding:14px 0 4px;">
          <strong>Step 2 &mdash; Subscribe to exactly these 3 events</strong>
        </td>
      </tr>
      <tr>
        <td><code>payment_intent.succeeded</code></td>
        <td>
          Async payment confirmation fallback &mdash; fires when Stripe collects payment
          successfully. Used to mark the order as <em>Paid</em> when the customer's browser
          closed before the redirect completed. Idempotent: skipped if the order is already
          in the configured paid status.
        </td>
      </tr>
      <tr>
        <td><code>payment_intent.payment_failed</code></td>
        <td>
          Fires on card decline, insufficient funds, SCA / 3D-Secure failure, or any other
          payment error. Updates the order to the configured <em>Failed</em> status and writes
          the Stripe error code and message to the order history for admin review.
        </td>
      </tr>
      <tr>
        <td><code>charge.dispute.created</code></td>
        <td>
          Fires when a customer raises a chargeback. Looks up the order via the payment intent
          ID stored in order history and moves it to the configured <em>Disputed</em> status so
          it is flagged for manual review before responding to Stripe.
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding:14px 0 4px;">
          <strong>Step 3 &mdash; Copy the Signing Secret and save it above</strong><br>
          After adding the endpoint, Stripe reveals a <em>Signing Secret</em> (starts with
          <code>whsec_</code>). Paste it into the <strong>Webhook Secret</strong> field at the
          top of this page. The webhook handler verifies every incoming request against this
          secret &mdash; without it all webhook calls are rejected with HTTP&nbsp;400.
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding:14px 0 4px;">
          <strong>Technical notes</strong>
        </td>
      </tr>
      <tr>
        <td>API version</td>
        <td>
          In the Stripe Dashboard under <em>Developers &rarr; Webhooks &rarr; API version</em>
          select <strong>2026-08-26.dahlia</strong> (or the latest Dahlia release). The three
          events above are stable across all Dahlia versions; the new granular error codes in
          this release automatically enrich the failure messages logged to order history.
        </td>
      </tr>
      <tr>
        <td>Payload field used</td>
        <td>
          The handler reads <code>data.object.metadata.order_ref</code> from
          <code>payment_intent.succeeded</code> and <code>payment_intent.payment_failed</code>
          to resolve the NivoCart order ID. This metadata key is written by the payment
          controller at intent-creation time &mdash; do not rename it.
        </td>
      </tr>
      <tr>
        <td>Dispute lookup</td>
        <td>
          For <code>charge.dispute.created</code> there is no <code>order_ref</code> metadata.
          The handler resolves the order by searching the order history table for the payment
          intent ID. Ensure order history comments are never cleared.
        </td>
      </tr>
      <tr>
        <td>.htaccess rule</td>
        <td>
          Add this rule to your root <code>.htaccess</code> so the webhook URL is not rewritten
          by NivoCart's SEO router:<br>
          <code>RewriteRule ^catalog/webhooks/ - [L]</code>
        </td>
      </tr>
      <tr>
        <td>Stripe retry policy</td>
        <td>
          Stripe retries failed deliveries (non-2xx responses) up to 3&nbsp;days with
          exponential back-off. The handler sends HTTP&nbsp;200 immediately and flushes the
          response before any DB work, so server-side timeouts will not cause duplicate
          order-status updates &mdash; the idempotency guard handles retries safely.
        </td>
      </tr>
      <tr>
        <td>Log file</td>
        <td>
          All webhook activity is written to <code>system/logs/stripe_webhook.log</code>.
          Check this file first when debugging missing order updates.
        </td>
      </tr>
    </table>
  </div>
</div>
</div>

<?php echo $footer; ?>