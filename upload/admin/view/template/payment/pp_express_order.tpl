<h2><?php echo $text_payment_info; ?></h2>
<div id="pp-feedback"></div>
<!-- ── Order summary ──────────────────────────────────────────────────────── -->
<table class="form" id="table-payment-info">
  <tr>
    <td><?php echo $text_pp_order_id; ?></td>
    <td><?php echo $paypal_order['pp_order_id']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_intent; ?></td>
    <td><?php echo $paypal_order['intent']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_status; ?></td>
    <td id="pp-status"><?php echo $paypal_order['status']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_capture_id; ?></td>
    <td><?php echo $paypal_order['capture_id'] ?: '—'; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_amount_authorised; ?></td>
    <td><?php echo $paypal_order['currency_code']; ?> <?php echo $paypal_order['total']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_amount_captured; ?></td>
    <td id="pp-captured"><?php echo $paypal_order['currency_code']; ?> <?php echo $paypal_order['captured']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_amount_refunded; ?></td>
    <td id="pp-refunded"><?php echo $paypal_order['currency_code']; ?> <?php echo $paypal_order['refunded']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_amount_remaining; ?></td>
    <td id="pp-remaining"><?php echo $paypal_order['currency_code']; ?> <span id="pp-remaining-value"><?php echo $paypal_order['remaining']; ?></span></td>
  </tr>
  <?php if ($paypal_order['intent'] === 'AUTHORIZE' && $paypal_order['status'] !== 'COMPLETED' && $paypal_order['status'] !== 'VOIDED') { ?>
  <!-- ── Partial capture row ─────────────────────────────────────────────── -->
  <tr id="row-capture">
    <td><label for="pp-capture-amount"><?php echo $entry_capture_amount; ?></label></td>
    <td>
      <input type="text" size="10" id="pp-capture-amount" value="<?php echo $paypal_order['remaining']; ?>" />
      <input type="text" size="30" id="pp-capture-note" placeholder="<?php echo $entry_capture_note; ?>" />
      &nbsp;
      <a class="button-save" id="button-capture"><?php echo $button_capture; ?></a>
      <a class="button-save" id="button-capture-full"><?php echo $button_capture_full; ?></a>
      &nbsp;
      <a class="button-delete" id="button-void"><?php echo $button_void; ?></a>
    </td>
  </tr>
  <?php } ?>
</table>
<!-- ── Transaction table ─────────────────────────────────────────────────── -->
<h3><?php echo $text_transactions; ?></h3>
<?php if (!empty($transactions)) { ?>
<table class="list">
  <thead>
    <tr>
      <td><?php echo $column_type; ?></td>
      <td><?php echo $column_capture_id; ?></td>
      <td><?php echo $column_amount; ?></td>
      <td><?php echo $column_currency; ?></td>
      <td><?php echo $column_status; ?></td>
      <td><?php echo $column_note; ?></td>
      <td><?php echo $column_created; ?></td>
      <td><?php echo $column_actions; ?></td>
    </tr>
  </thead>
  <tbody id="pp-transactions">
  <?php foreach ($transactions as $t) { ?>
    <tr>
      <td><?php echo $t['transaction_type']; ?></td>
      <td><?php echo $t['capture_id'] ?: '—'; ?></td>
      <td><?php echo $t['amount']; ?></td>
      <td><?php echo $t['currency_code']; ?></td>
      <td><?php echo $t['status']; ?></td>
      <td><?php echo $t['note'] ?: '—'; ?></td>
      <td><?php echo $t['created']; ?></td>
      <td>
        <?php if (!empty($t['refund'])) { ?>
          <a href="<?php echo $t['refund']; ?>" class="button"><?php echo $button_refund; ?></a>
        <?php } ?>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>
<?php } else { ?>
  <p><?php echo $text_no_results; ?></p>
<?php } ?>

<!-- ── JS ────────────────────────────────────────────────────────────────── -->
<script type="text/javascript"><!--

function ppFeedback(type, message) {
  $('#pp-feedback').html('<div class="' + type + '">' + message + '</div>').find('div').hide().fadeIn('slow');
}

// ── Partial capture ───────────────────────────────────────────────────────────
$('#button-capture').on('click', function() {
  var amt = parseFloat($('#pp-capture-amount').val());

  if (!amt || amt <= 0) {
    alert('<?php echo addslashes($error_capture_amt); ?>');
    return;
  }

  var $btn = $(this);

  $.ajax({
    url: '<?php echo $url_capture; ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      order_id: <?php echo $order_id; ?>,
      amount: amt,
      note: $('#pp-capture-note').val()
    },
    beforeSend: function() {
      $('#pp-feedback').empty();
      $btn.prop('disabled', true).after('<img src="view/image/loading.gif" class="loading" />');
    }
  })
  .done(function(json) {
    if (json.error) { ppFeedback('warning', json.error); }
    if (json.success) {
      ppFeedback('success', json.success);
      $('#pp-captured').text('<?php echo $paypal_order['currency_code']; ?> ' + json.captured);
      $('#pp-refunded').text('<?php echo $paypal_order['currency_code']; ?> ' + json.refunded);
      $('#pp-remaining-value').text(json.remaining);
      $('#pp-capture-amount').val(json.remaining);
      $('#pp-status').text(json.status);
      if (parseFloat(json.remaining) <= 0) { $('#row-capture').remove(); }
    }
  })
  .fail(function() { ppFeedback('warning', '<?php echo addslashes($error_timeout); ?>'); })
  .always(function() { $('.loading').remove(); $btn.prop('disabled', false); });
});

// ── Full capture ──────────────────────────────────────────────────────────────
$('#button-capture-full').on('click', function() {
  var $btn = $(this);

  $.ajax({
    url: '<?php echo $url_capture_full; ?>',
    type: 'POST',
    dataType: 'json',
    data: { order_id: <?php echo $order_id; ?> },
    beforeSend: function() {
      $('#pp-feedback').empty();
      $btn.prop('disabled', true).after('<img src="view/image/loading.gif" class="loading" />');
    }
  })
  .done(function(json) {
    if (json.error) { ppFeedback('warning', json.error); }
    if (json.success) {
      ppFeedback('success', json.success);
      $('#pp-captured').text('<?php echo $paypal_order['currency_code']; ?> ' + json.captured);
      $('#pp-remaining-value').text('0.00');
      $('#pp-status').text(json.status);
      $('#row-capture').remove();
    }
  })
  .fail(function() { ppFeedback('warning', '<?php echo addslashes($error_timeout); ?>'); })
  .always(function() { $('.loading').remove(); $btn.prop('disabled', false); });
});

// ── Void ──────────────────────────────────────────────────────────────────────
$('#button-void').on('click', function() {
  if (!confirm('<?php echo addslashes($text_confirm_void); ?>')) { return; }

  var $btn = $(this);

  $.ajax({
    url: '<?php echo $url_void; ?>',
    type: 'POST',
    dataType: 'json',
    data: { order_id: <?php echo $order_id; ?> },
    beforeSend: function() {
      $('#pp-feedback').empty();
      $btn.prop('disabled', true).after('<img src="view/image/loading.gif" class="loading" />');
    }
  })
  .done(function(json) {
    if (json.error) { ppFeedback('warning', json.error); }
    if (json.success) {
      ppFeedback('success', json.success);
      $('#pp-status').text(json.status);
      $('#row-capture').remove();
    }
  })
  .fail(function() { ppFeedback('warning', '<?php echo addslashes($error_timeout); ?>'); })
  .always(function() { $('.loading').remove(); $btn.prop('disabled', false); });
});

//--></script>
