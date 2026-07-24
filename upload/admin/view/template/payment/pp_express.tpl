<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if (!empty($errors['warning'])) { ?>
    <div class="warning"><?php echo $errors['warning']; ?></div>
  <?php } ?>
  <?php if (!empty($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
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
      <div id="htabs" class="htabs">
        <a href="#tab-api"><?php echo $tab_api; ?></a>
        <a href="#tab-general"><?php echo $tab_general; ?></a>
        <a href="#tab-order-status"><?php echo $tab_order_status; ?></a>
        <?php if ($pp_express_debug) { ?>
          <a href="#tab-debug-log"><?php echo $tab_debug_log; ?></a>
        <?php } ?>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <!-- ═══════════════════════════════════════════════════════════════════
             TAB: API Credentials
        ════════════════════════════════════════════════════════════════════ -->
        <div id="tab-api">
          <table class="form">
            <tr><td colspan="2"><strong>Live</strong></td></tr>
            <tr>
              <td><span class="required">*</span> <label for="input-client-id"><?php echo $entry_client_id; ?></label></td>
              <td>
                <input type="text" name="pp_express_client_id" id="input-client-id" value="<?php echo $pp_express_client_id; ?>" size="60"<?php if (isset($errors['client_id'])) { ?> class="input-error"<?php } ?> />
                <?php if (isset($errors['client_id'])) { ?><span class="error"><?php echo $errors['client_id']; ?></span><?php } ?>
              </td>
            </tr>
            <tr>
              <td><span class="required">*</span> <label for="input-client-secret"><?php echo $entry_client_secret; ?></label></td>
              <td>
                <input type="password" name="pp_express_client_secret" id="input-client-secret" value="<?php echo $pp_express_client_secret; ?>" size="60"<?php if (isset($errors['client_secret'])) { ?> class="input-error"<?php } ?> />
                <?php if (isset($errors['client_secret'])) { ?><span class="error"><?php echo $errors['client_secret']; ?></span><?php } ?>
              </td>
            </tr>
            <tr>
              <td><label for="input-webhook-id"><?php echo $entry_webhook_id; ?><br /><span class="help"><?php echo $help_webhook_id; ?></span></label></td>
              <td><input type="text" name="pp_express_webhook_id" id="input-webhook-id" value="<?php echo $pp_express_webhook_id; ?>" size="60" /></td>
            </tr>
            <tr><td colspan="2"><strong>Sandbox</strong></td></tr>
            <tr>
              <td><label for="input-sandbox-client-id"><?php echo $entry_sandbox_client_id; ?></label></td>
              <td>
                <input type="text" name="pp_express_sandbox_client_id" id="input-sandbox-client-id" value="<?php echo $pp_express_sandbox_client_id; ?>" size="60"<?php if (isset($errors['sandbox_client_id'])) { ?> class="input-error"<?php } ?> />
                <?php if (isset($errors['sandbox_client_id'])) { ?><span class="error"><?php echo $errors['sandbox_client_id']; ?></span><?php } ?>
              </td>
            </tr>
            <tr>
              <td><label for="input-sandbox-client-secret"><?php echo $entry_sandbox_client_secret; ?></label></td>
              <td>
                <input type="password" name="pp_express_sandbox_client_secret" id="input-sandbox-client-secret" value="<?php echo $pp_express_sandbox_client_secret; ?>" size="60"<?php if (isset($errors['sandbox_client_secret'])) { ?> class="input-error"<?php } ?> />
                <?php if (isset($errors['sandbox_client_secret'])) { ?><span class="error"><?php echo $errors['sandbox_client_secret']; ?></span><?php } ?>
              </td>
            </tr>
            <tr>
              <td><label for="input-sandbox-webhook-id"><?php echo $entry_sandbox_webhook_id; ?></label></td>
              <td><input type="text" name="pp_express_sandbox_webhook_id" id="input-sandbox-webhook-id" value="<?php echo $pp_express_sandbox_webhook_id; ?>" size="60" /></td>
            </tr>
            <tr>
              <td><label><?php echo $this->language->get('entry_webhook_url') ?? 'Webhook URL'; ?></label></td>
              <td><code><?php echo $webhook_url; ?></code></td>
            </tr>
          </table>
        </div>
        <!-- ═══════════════════════════════════════════════════════════════════
             TAB: General
        ════════════════════════════════════════════════════════════════════ -->
        <div id="tab-general">
          <table class="form">
            <tr>
              <td><label for="input-sandbox"><?php echo $entry_sandbox; ?><br /><span class="help"><?php echo $help_sandbox; ?></span></label></td>
              <td><select name="pp_express_sandbox" id="input-sandbox">
                <option value="1"<?php if ($pp_express_sandbox) { ?> selected="selected"<?php } ?>><?php echo $text_yes; ?></option>
                <option value="0"<?php if (!$pp_express_sandbox) { ?> selected="selected"<?php } ?>><?php echo $text_no; ?></option>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-transaction-mode"><?php echo $entry_transaction_mode; ?><br /><span class="help"><?php echo $help_transaction_mode; ?></span></label></td>
              <td><select name="pp_express_transaction_mode" id="input-transaction-mode">
                <option value="CAPTURE"<?php if ($pp_express_transaction_mode === 'CAPTURE') { ?> selected="selected"<?php } ?>><?php echo $text_capture; ?></option>
                <option value="AUTHORIZE"<?php if ($pp_express_transaction_mode === 'AUTHORIZE') { ?> selected="selected"<?php } ?>><?php echo $text_authorize; ?></option>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-pay-later"><?php echo $entry_pay_later; ?><br /><span class="help"><?php echo $help_pay_later; ?></span></label></td>
              <td><select name="pp_express_pay_later" id="input-pay-later">
                <option value="1"<?php if ($pp_express_pay_later) { ?> selected="selected"<?php } ?>><?php echo $text_enabled; ?></option>
                <option value="0"<?php if (!$pp_express_pay_later) { ?> selected="selected"<?php } ?>><?php echo $text_disabled; ?></option>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-currency"><?php echo $entry_currency; ?><br /><span class="help"><?php echo $help_currency; ?></span></label></td>
              <td><select name="pp_express_currency" id="input-currency">
                <?php foreach ($currencies as $currency) { ?>
                  <option value="<?php echo $currency; ?>"<?php if ($currency === $pp_express_currency) { ?> selected="selected"<?php } ?>><?php echo $currency; ?></option>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-debug"><?php echo $entry_debug; ?><br /><span class="help"><?php echo $help_debug; ?></span></label></td>
              <td><select name="pp_express_debug" id="input-debug">
                <option value="1"<?php if ($pp_express_debug) { ?> selected="selected"<?php } ?>><?php echo $text_enabled; ?></option>
                <option value="0"<?php if (!$pp_express_debug) { ?> selected="selected"<?php } ?>><?php echo $text_disabled; ?></option>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-total"><?php echo $entry_total; ?><br /><span class="help"><?php echo $help_total; ?></span></label></td>
              <td><input type="text" name="pp_express_total" id="input-total" value="<?php echo !empty($pp_express_total) ? $pp_express_total : '0.00'; ?>" /></td>
            </tr>
            <tr>
              <td><label for="input-total-max"><?php echo $entry_total_max; ?><br /><span class="help"><?php echo $help_total_max; ?></span></label></td>
              <td><input type="text" name="pp_express_total_max" id="input-total-max" value="<?php echo !empty($pp_express_total_max) ? $pp_express_total_max : ''; ?>" /></td>
            </tr>
            <tr>
              <td><label for="input-geo-zone"><?php echo $entry_geo_zone; ?></label></td>
              <td><select name="pp_express_geo_zone_id" id="input-geo-zone">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                  <option value="<?php echo $geo_zone['geo_zone_id']; ?>"<?php if ($geo_zone['geo_zone_id'] === $pp_express_geo_zone_id) { ?> selected="selected"<?php } ?>><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td><label for="input-sort-order"><?php echo $entry_sort_order; ?></label></td>
              <td><input type="text" name="pp_express_sort_order" id="input-sort-order" value="<?php echo $pp_express_sort_order; ?>" size="1" /></td>
            </tr>
            <tr class="highlighted">
              <td><label for="input-status"><?php echo $entry_status; ?></label></td>
              <td><select name="pp_express_status" id="input-status">
                <option value="1"<?php if ($pp_express_status) { ?> selected="selected"<?php } ?>><?php echo $text_enabled; ?></option>
                <option value="0"<?php if (!$pp_express_status) { ?> selected="selected"<?php } ?>><?php echo $text_disabled; ?></option>
              </select></td>
            </tr>
          </table>
        </div>
        <!-- ═══════════════════════════════════════════════════════════════════
             TAB: Order Status
        ════════════════════════════════════════════════════════════════════ -->
        <div id="tab-order-status">
          <table class="form">
            <?php
            $status_fields = [
              'completed' => $entry_completed_status,
              'pending'   => $entry_pending_status,
              'failed'    => $entry_failed_status,
              'refunded'  => $entry_refunded_status,
              'voided'    => $entry_voided_status,
              'denied'    => $entry_denied_status,
              'expired'   => $entry_expired_status,
            ];
            foreach ($status_fields as $key => $label) {
              $field = 'pp_express_' . $key . '_status_id';
              ?>
              <tr>
                <td><label for="input-<?php echo $key; ?>-status"><?php echo $label; ?></label></td>
                <td><select name="<?php echo $field; ?>" id="input-<?php echo $key; ?>-status">
                  <?php foreach ($order_statuses as $order_status) { ?>
                    <option value="<?php echo $order_status['order_status_id']; ?>"<?php if ($order_status['order_status_id'] === $field) { ?> selected="selected"<?php } ?>><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
            <?php } ?>
          </table>
        </div>
        <!-- ═══════════════════════════════════════════════════════════════════
             TAB: Debug Log
        ════════════════════════════════════════════════════════════════════ -->
        <?php if ($pp_express_debug) { ?>
        <div id="tab-debug-log">
          <div class="report">
            <div class="left"><img src="view/image/log.png" alt="" /></div>
            <?php if (!empty($debug_log)) { ?>
              <div class="right"><a href="<?php echo $debug_clear; ?>" class="button-filter ripple"><?php echo $button_debug_clear; ?></a></div>
              <div class="right"><a href="<?php echo $debug_download; ?>" class="button-filter ripple"><?php echo $button_debug_download; ?></a></div>
            <?php } ?>
          </div>
          <textarea wrap="off" class="log"><?php echo $debug_log; ?></textarea>
        </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#htabs a').tabs();

function apply() {
  var form = $('#form');
  form.append('<input type="hidden" name="apply" value="1" />');
  form.submit();
}
//--></script>

<?php echo $footer; ?>