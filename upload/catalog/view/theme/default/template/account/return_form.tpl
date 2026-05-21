<?php echo $header; ?>
<?php echo $content_higher; ?>
<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php echo $text_description; ?>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="return-form">
    <!-- PART 1 — Customer details + Order ID lookup -->
    <h2><?php echo $text_order; ?></h2>
    <div class="content">
      <div class="left">
        <span class="required">*</span> <?php echo $entry_firstname; ?><br />
        <input type="text" name="firstname" value="<?php echo $firstname; ?>" size="25" />
        <br />
        <?php if ($error_firstname) { ?>
          <span class="error"><?php echo $error_firstname; ?></span>
        <?php } ?>
        <br />
        <span class="required">*</span> <?php echo $entry_lastname; ?><br />
        <input type="text" name="lastname" value="<?php echo $lastname; ?>" size="25" />
        <br />
        <?php if ($error_lastname) { ?>
          <span class="error"><?php echo $error_lastname; ?></span>
        <?php } ?>
        <br />
        <span class="required">*</span> <?php echo $entry_email; ?><br />
        <input type="text" name="email" value="<?php echo $email; ?>" size="25" />
        <br />
        <?php if ($error_email) { ?>
          <span class="error"><?php echo $error_email; ?></span>
        <?php } ?>
        <br />
        <span class="required">*</span> <?php echo $entry_telephone; ?><br />
        <input type="text" name="telephone" value="<?php echo $telephone; ?>" size="25" />
        <br />
        <?php if ($error_telephone) { ?>
          <span class="error"><?php echo $error_telephone; ?></span>
        <?php } ?>
        <br />
      </div>
      <div class="right">
        <span class="required">*</span> <?php echo $entry_order_id; ?><br />
        <div id="order-lookup-wrap" style="display:flex; align-items:flex-start; gap:6px; flex-wrap:wrap;">
          <div>
            <input type="text" name="order_id" id="order-id-input" value="<?php echo $order_id; ?>" size="25" autocomplete="off" />
            <?php if ($error_order_id) { ?>
              <br /><span class="error"><?php echo $error_order_id; ?></span>
            <?php } ?>
          </div>
          <button type="button" id="btn-lookup-order" class="button" style="white-space:nowrap;"><?php echo $button_lookup_order; ?></button>
        </div>
        <br />
        <!-- Error message returned by AJAX -->
        <div id="order-lookup-error" class="error" style="display:none;"></div>
        <!-- Hidden date field — populated by AJAX, submitted with the form -->
        <input type="hidden" name="date_ordered" id="date-ordered-hidden" value="<?php echo $date_ordered; ?>" />
        <!-- Read-only date display shown after a successful lookup -->
        <div id="date-ordered-display" style="display:<?php echo $date_ordered ? 'block' : 'none'; ?>;">
          <?php echo $entry_date_ordered; ?> &nbsp;
          <strong id="date-ordered-text"><?php echo $date_ordered; ?></strong>
        </div>
      </div>
    </div>
    <!-- PART 2 — Product details (hidden until Order ID verified) -->
    <div id="return-product-section" style="display:<?php echo $order_id ? 'block' : 'none'; ?>;">
      <h2><?php echo $text_product; ?></h2>
      <div id="return-product">
        <div class="content">
          <div class="return-product">
            <div class="return-name">
              <span class="required">*</span> <b><?php echo $entry_product; ?></b><br />
              <!-- Product <select> injected here by JS when order has multiple products -->
              <div id="product-select-wrap"></div>
              <!-- Always-present hidden field — carries product name in POST -->
              <input type="hidden" name="product" id="product-hidden" value="<?php echo $product; ?>" />
              <!-- Read-only display so the customer can see what is selected -->
              <input type="text" id="product-name-display" value="<?php echo $product; ?>" readonly style="background:#f5f5f5;" />
              <!-- Hidden model field — also always present -->
              <input type="hidden" name="model" id="model-hidden" value="<?php echo $model; ?>" />
              <?php if ($error_product) { ?>
                <span class="error"><?php echo $error_product; ?></span>
              <?php } ?>
            </div>
            <!-- Quantity selector: rendered as <select> by JS when ordered qty > 1 -->
            <div class="return-quantity">
              <b><?php echo $entry_quantity; ?></b><br />
              <div id="quantity-field-wrap">
                <input type="text" name="quantity" id="quantity-input-static" value="<?php echo $quantity; ?>" size="5" />
              </div>
            </div>
          </div>
          <div class="return-detail">
            <div class="return-reason">
              <b><?php echo $entry_model; ?></b><br />
              <input type="text" id="model-display" value="<?php echo $model; ?>" readonly style="background:#f5f5f5;" size="25" />
              <?php if ($error_model) { ?>
                <br /><span class="error"><?php echo $error_model; ?></span>
              <?php } ?>
              <br /><br />
              <span class="required">*</span> <b><?php echo $entry_reason; ?></b><br />
              <table>
                <?php foreach ($return_reasons as $return_reason) { ?>
                <tr>
                  <td width="1">
                    <input type="radio" name="return_reason_id" value="<?php echo $return_reason['return_reason_id']; ?>" id="return-reason-id<?php echo $return_reason['return_reason_id']; ?>" <?php if ($return_reason['return_reason_id'] === $return_reason_id) { echo 'checked="checked"'; } ?> />
                  </td>
                  <td>
                    <label for="return-reason-id<?php echo $return_reason['return_reason_id']; ?>">
                      <?php echo $return_reason['name']; ?>
                    </label>
                  </td>
                </tr>
                <?php } ?>
              </table>
              <?php if ($error_reason) { ?>
                <span class="error"><?php echo $error_reason; ?></span>
              <?php } ?>
            </div>
            <div class="return-opened">
              <b><?php echo $entry_opened; ?></b><br />
              <input type="radio" name="opened" value="1" id="opened" <?php if ($opened) { echo 'checked="checked"'; } ?> />
              <label for="opened"><?php echo $text_yes; ?></label>
              &nbsp;
              <input type="radio" name="opened" value="0" id="unopened" <?php if (!$opened) { echo 'checked="checked"'; } ?> />
              <label for="unopened"><?php echo $text_no; ?></label>
              <br /><br />
              <?php echo $entry_fault_detail; ?><br />
              <textarea name="comment" cols="150" rows="6"><?php echo $comment; ?></textarea>
            </div>
          </div>
          <!-- Captcha -->
          <div class="return-captcha">
            <div id="captcha-wrap">
              <div class="captcha-box">
                <div class="captcha-view">
                  <div><b><?php echo $captcha_image; ?></b></div>
                </div>
              </div>
              <div class="captcha-text">
                <label><?php echo $entry_captcha; ?></label>
                <input type="text" name="captcha" id="captcha" value="<?php echo $captcha; ?>" autocomplete="off" />
              </div>
              <div class="captcha-action"><i class="fa fa-repeat"></i></div>
            </div>
            <br />
            <?php if ($error_captcha) { ?>
              <span class="error"><?php echo $error_captcha; ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <!-- Form buttons -->
      <?php if ($text_agree) { ?>
        <div class="buttons">
          <div class="left">
            <a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a>
          </div>
          <div class="right">
            <?php echo $text_agree; ?>
            <input type="checkbox" name="agree" value="1" <?php if ($agree) { echo 'checked="checked"'; } ?> />
            <input type="submit" value="<?php echo $button_continue; ?>" class="button" />
          </div>
        </div>
      <?php } else { ?>
        <div class="buttons">
          <div class="left">
            <a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a>
          </div>
          <div class="right">
            <input type="submit" value="<?php echo $button_continue; ?>" class="button" />
          </div>
        </div>
      <?php } ?>
    </div>
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
(function ($) {
  'use strict';
  // ----------------------------------------------------------------
  // applyProduct — fills the hidden POST fields and visible displays
  // from a product object { product_id, name, model, quantity }
  // ----------------------------------------------------------------
  function applyProduct(product) {
    $('#product-hidden').val(product.name);
    $('#product-name-display').val(product.name);
    $('#model-hidden').val(product.model);
    $('#model-display').val(product.model);

    // Quantity: select if ordered qty > 1, otherwise read-only text
    var orderedQty = parseInt(product.quantity, 10);
    var $qtyWrap = $('#quantity-field-wrap');

    if (orderedQty > 1) {
      var $sel = $('<select>', { name: 'quantity', id: 'quantity-select' });
      for (var i = 1; i <= orderedQty; i++) {
        $sel.append($('<option>', { value: i, text: i }));
      }
      $qtyWrap.html($sel);
    } else {
      $qtyWrap.html(
        $('<input>', { type: 'text', name: 'quantity', value: 1, size: 5, readonly: true, style: 'background:#f5f5f5;' })
      );
    }
  }
  // -------------
  // Order lookup
  // -------------
  $('#btn-lookup-order').on('click', function () {
    var orderId = $.trim($('#order-id-input').val());

    $('#order-lookup-error').hide().text('');
    $('#date-ordered-display').hide();
    $('#return-product-section').hide();

    if (!orderId || isNaN(orderId)) {
      $('#order-lookup-error').text('<?php echo $error_order_id; ?>').show();
      return;
    }

    $('#order-lookup-spinner').show();
    $('#btn-lookup-order').prop('disabled', true);

    $.ajax({
      url: '<?php echo $ajax_order_url; ?>',
      type: 'POST',
      data: { order_id: orderId },
      dataType: 'json',

      success: function (response) {
        if (!response.success) {
          $('#order-lookup-error').text(response.error || 'Unknown error.').show();
          return;
        }

        var data = response.data;
        var products = data.products;

        // Date
        $('#date-ordered-hidden').val(data.date_ordered);
        $('#date-ordered-text').text(data.date_ordered);
        $('#date-ordered-display').show();

        if (products.length > 1) {
          // The hidden #product-hidden field carries the actual POST value.
          var $productSelect = $('<select>', { id: 'product-select-ajax' });
          $.each(products, function (i, p) {
            $productSelect.append(
              $('<option>', { value: p.product_id, text:  p.name + ' (' + p.model + ')' })
            );
          });

          $('#product-select-wrap').html($productSelect);
          // Seed fields with first product
          applyProduct(products[0]);

          // Drive hidden fields from the select
          $productSelect.on('change', function () {
            var pid = $(this).val();
            var match = $.grep(products, function (p) {
              return String(p.product_id) === String(pid);
            })[0];

            if (match) { applyProduct(match); }
          });
        } else {
          // Single product — no select needed
          $('#product-select-wrap').empty();
          applyProduct(products[0]);
        }

        $('#return-product-section').slideDown(250);
      },
      error: function () {
        $('#order-lookup-error').text('A server error occurred. Please try again.').show();
      },
      complete: function () {
        $('#order-lookup-spinner').hide();
        $('#btn-lookup-order').prop('disabled', false);
      }
    });
  });

  // ---------
  // Colorbox
  // ---------
  if ($.fn.colorbox) {
    $('.colorbox').colorbox({
      overlayClose: true, opacity: 0.3, width: 600, height: 480
    });
  }
}(jQuery));
//--></script>

<?php echo $footer; ?>