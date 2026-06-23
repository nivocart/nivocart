<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if (!empty($error)) { ?>
    <div class="warning"><?php echo $error; ?></div>
  <?php } ?>
  <div id="pp-feedback"></div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <table class="form">
        <input type="hidden" id="pp-order-id" value="<?php echo $order_id; ?>" />
        <input type="hidden" id="pp-capture-id" value="<?php echo $capture_id; ?>" />
        <input type="hidden" id="pp-currency" value="<?php echo $currency_code; ?>" />
        <tr>
          <td><?php echo $entry_capture_id; ?></td>
          <td><code><?php echo $capture_id; ?></code></td>
        </tr>
        <tr>
          <td><?php echo $entry_amount; ?></td>
          <td><?php echo $currency_code; ?> <?php echo $amount_original; ?></td>
        </tr>
        <?php if ((float)$already_refunded > 0) { ?>
        <tr>
          <td><?php echo $entry_refund_full; ?> (<?php echo $currency_code; ?> <?php echo $already_refunded; ?> <?php echo $this->language->get('text_already_refunded') ?? 'already refunded'; ?>)</td>
          <td><?php echo $currency_code; ?> <?php echo $refund_available; ?></td>
        </tr>
        <?php } ?>
        <tr>
          <td><label for="input-refund-full"><?php echo $entry_refund_full; ?></label></td>
          <td><input type="checkbox" id="input-refund-full" value="1" checked="checked" /></td>
        </tr>
        <tr id="row-partial" style="display:none;">
          <td><label for="input-refund-amount"><?php echo $entry_amount; ?></label></td>
          <td>
            <input type="text" id="input-refund-amount" value="<?php echo $refund_available; ?>" size="10" />
            &nbsp;<?php echo $currency_code; ?>
          </td>
        </tr>
        <tr>
          <td><label for="input-refund-note"><?php echo $entry_note; ?></label></td>
          <td><textarea id="input-refund-note" cols="40" rows="4"></textarea></td>
        </tr>
      </table>
      <a class="button-save ripple" id="button-refund" style="float:right;"><?php echo $button_refund; ?></a>
    </div>
  </div>
</div>

<script type="text/javascript"><!--

$('#input-refund-full').on('change', function() {
  $('#row-partial').toggle(!this.checked);
});

$('#button-refund').on('click', function() {
  var full = $('#input-refund-full').prop('checked') ? 1 : 0;
  var amt = parseFloat($('#input-refund-amount').val());
  var $btn = $(this);

  if (!full && (!amt || amt <= 0)) {
    alert('<?php echo addslashes($error_partial_amt); ?>');
    return;
  }

  $.ajax({
    url: '<?php echo $action; ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      order_id: $('#pp-order-id').val(),
      capture_id: $('#pp-capture-id').val(),
      refund_full: full,
      amount: amt,
      note: $('#input-refund-note').val()
    },
    beforeSend: function() {
      $('#pp-feedback').empty();
      $btn.prop('disabled', true).after('<img src="view/image/loading.gif" class="loading" style="float:right;" />');
    }
  })
  .done(function(json) {
    if (json.error) {
      $('#pp-feedback').html('<div class="warning">' + json.error + '</div>').find('div').hide().fadeIn('slow');
    }
    if (json.success) {
      $('#pp-feedback').html('<div class="success">' + json.success + '</div>').find('div').hide().fadeIn('slow');
      setTimeout(function() { window.location = '<?php echo addslashes($cancel); ?>'; }, 1500);
    }
  })
  .fail(function() {
    $('#pp-feedback').html('<div class="warning"><?php echo addslashes($error_partial_amt); ?></div>');
  })
  .always(function() {
    $('.loading').remove();
    $btn.prop('disabled', false);
  });
});

//--></script>

<?php echo $footer; ?>