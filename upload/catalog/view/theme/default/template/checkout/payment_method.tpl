<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($payment_methods) { ?>
  <p><?php echo $text_payment_method; ?></p>
  <table class="radio">
  <?php foreach ($payment_methods as $payment_method) { ?>
    <?php $apply_paypal_fee = ((substr($payment_method['code'], 0, 3) === "pp_") || ($payment_method['code'] === "paypal_email")) ? true : false; ?>
    <tr class="highlight">
      <td>
      <?php if ($payment_method['code'] === $code || !$code) { ?>
        <?php $code = $payment_method['code']; ?>
        <input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" id="<?php echo $payment_method['code']; ?>" checked="checked" />
      <?php } else { ?>
        <input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" id="<?php echo $payment_method['code']; ?>" />
      <?php } ?>
      </td>
      <td>
      <?php if ($payment_images) { ?>
        <?php foreach ($payment_images as $payment_image) { ?>
          <?php if ($payment_image['payment'] === strtolower($payment_method['code'])) { ?>
            <?php if ($payment_image['status']) { ?>
              <label for="<?php echo $payment_method['code']; ?>"><img src="<?php echo $payment_image['image']; ?>" title="<?php echo $payment_method['title']; ?>" alt="<?php echo $payment_method['title']; ?>" />
              <?php if ($paypal_fee && $apply_paypal_fee) { ?>
                <span> + <?php echo $paypal_fee; ?></span>
              <?php } ?>
              </label>
            <?php } else { ?>
              <label for="<?php echo $payment_method['code']; ?>"><?php echo $payment_method['title']; ?>
              <?php if ($paypal_fee && $apply_paypal_fee) { ?>
                <span> + <?php echo $paypal_fee; ?></span>
              <?php } ?>
              </label>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } else { ?>
        <label for="<?php echo $payment_method['code']; ?>"><?php echo $payment_method['title']; ?>
        <?php if ($paypal_fee && $apply_paypal_fee) { ?>
          <span> + <?php echo $paypal_fee; ?></span>
        <?php } ?>
        </label>
      <?php } ?>
      </td>
    </tr>
  <?php } ?>
  </table>
  <br />
<?php } ?>
<b><?php echo $text_comments; ?></b>
<textarea name="comment" rows="5" style="width:98%;"><?php echo $comment; ?></textarea>
<br />
<br />
<?php if ($text_agree) { ?>
  <div class="buttons">
    <div class="right"><?php echo $text_agree; ?>
    <?php if ($agree) { ?>
      <input type="checkbox" name="agree" value="1" checked="checked" />
    <?php } else { ?>
      <input type="checkbox" name="agree" value="1" />
    <?php } ?>
      <input type="button" value="<?php echo $button_continue; ?>" id="button-payment-method" class="button" />
    </div>
  </div>
<?php } else { ?>
  <div class="buttons">
    <div class="right">
      <input type="button" value="<?php echo $button_continue; ?>" id="button-payment-method" class="button" />
    </div>
  </div>
<?php } ?>

<script type="text/javascript"><!--
$('.colorbox').colorbox({
	width: 640,
	height: 480
});
//--></script>

<script type="text/javascript"><!--
$('#button-payment-method').on('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/payment_method/validate',
        type: 'post',
        data: $('input[name="payment_method"]:checked').serialize()
			+ '&comment=' + encodeURIComponent($('textarea[name="comment"]').val())
			+ ($('input[name="agree"]').is(':checked') ? '&agree=1' : ''),
        dataType: 'json',
        beforeSend: function() {
            $('#button-payment-method').attr('disabled', true);
        },
        complete: function() {
            $('#button-payment-method').attr('disabled', false);
        },
        success: function(json) {
            $('.warning').remove();

            if (json['error']) {
                if (json['error']['warning']) {
                    $('#button-payment-method').before('<div class="warning">' + json['error']['warning'] + '</div>');
                }
            }

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (!json['error'] && !json['redirect']) {
                location = '<?php echo $this->url->link('checkout/confirm', '', 'SSL'); ?>';
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});
//--></script>