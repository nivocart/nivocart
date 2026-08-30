<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($age_warning) { ?>
  <div class="warning"><?php echo $age_warning; ?></div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php echo $text_description; ?>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="return-guest-form">
    <!-- PART 1 — Order summary + customer details -->
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
        <?php echo $entry_email; ?><br />
        <input type="text" value="<?php echo $email; ?>" size="25" readonly style="background:#f5f5f5;" />
        <br /><br />
        <span class="required">*</span> <?php echo $entry_telephone; ?><br />
        <input type="text" name="telephone" value="<?php echo $telephone; ?>" size="25" />
        <br />
        <?php if ($error_telephone) { ?>
          <span class="error"><?php echo $error_telephone; ?></span>
        <?php } ?>
        <br />
      </div>
      <div class="right">
        <?php echo $entry_order_id; ?><br />
        <input type="text" value="<?php echo $order_id; ?>" size="15" readonly style="background:#f5f5f5;" />
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
        <br /><br />
        <?php echo $entry_date_ordered; ?><br />
        <input type="text" value="<?php echo $date_ordered; ?>" size="15" readonly style="background:#f5f5f5;" />
        <input type="hidden" name="date_ordered" value="<?php echo $date_ordered; ?>" />
        <br />
      </div>
      <div class="buttons">
        <div class="left">
          <a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a>
        </div>
      </div>
    </div>
    <!-- PART 2 — Product details -->
    <h2><?php echo $text_product; ?></h2>
    <div id="return-product">
      <div class="content">
        <div class="return-product">
          <div class="return-name">
            <span class="required">*</span> <b><?php echo $entry_product; ?></b><br />
            <?php if (count($products) > 1) { ?>
              <select id="guest-product-select" onchange="guestUpdateProduct(this)" style="margin-bottom:4px;">
              <?php foreach ($products as $p) { ?>
                <option value="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>" data-model="<?php echo htmlspecialchars($p['model'], ENT_QUOTES); ?>" data-qty="<?php echo (int)$p['quantity']; ?>" <?php if ($p['name'] === $product) { echo 'selected'; } ?>>
                  <?php echo $p['name']; ?> (<?php echo $p['model']; ?>)
                </option>
              <?php } ?>
              </select><br />
              <input type="hidden" name="product" id="product-hidden" value="<?php echo htmlspecialchars($product, ENT_QUOTES); ?>" />
              <input type="hidden" name="model" id="model-hidden" value="<?php echo htmlspecialchars($model, ENT_QUOTES); ?>" />
            <?php } else { ?>
              <input type="hidden" name="product" value="<?php echo htmlspecialchars($products[0]['name'], ENT_QUOTES); ?>" />
              <input type="hidden" name="model" value="<?php echo htmlspecialchars($products[0]['model'], ENT_QUOTES); ?>" />
              <input type="text" value="<?php echo $products[0]['name']; ?>" readonly style="background:#f5f5f5;" size="35" /><br />
            <?php } ?>
            <?php if ($error_product) { ?>
              <span class="error"><?php echo $error_product; ?></span>
            <?php } ?>
          </div>
          <!-- Quantity -->
          <div class="return-quantity">
            <b><?php echo $entry_quantity; ?></b><br />
            <input type="number" name="quantity" id="quantity-input" value="<?php echo (int)$quantity; ?>" min="1" max="<?php echo count($products) === 1 ? (int)$products[0]['quantity'] : 999; ?>" size="5" style="width:60px;" />
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
                  <input type="radio" name="return_reason_id" value="<?php echo $return_reason['return_reason_id']; ?>" id="return-reason-id<?php echo $return_reason['return_reason_id']; ?>" <?php if ($return_reason['return_reason_id'] == $return_reason_id) { echo 'checked="checked"'; } ?> />
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
          <div class="return-action">
            <b><?php echo $entry_action_requested; ?></b><br />
            <table>
              <?php foreach ($return_actions as $return_action) { ?>
              <tr>
                <td width="1">
                  <input type="radio" name="return_action_id" value="<?php echo $return_action['return_action_id']; ?>" id="return-action-id<?php echo $return_action['return_action_id']; ?>" <?php if ($return_action['return_action_id'] == $return_action_id) { echo 'checked="checked"'; } ?> />
                </td>
                <td>
                  <label for="return-action-id<?php echo $return_action['return_action_id']; ?>">
                    <?php echo $return_action['name']; ?>
                  </label>
                </td>
              </tr>
              <?php } ?>
            </table>
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
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
function guestUpdateProduct(sel) {
  var opt = sel.options[sel.selectedIndex];
  var model = opt.getAttribute('data-model');
  var qty = parseInt(opt.getAttribute('data-qty'), 10) || 1;

  document.getElementById('product-hidden').value = opt.value;
  document.getElementById('model-hidden').value = model;
  document.getElementById('model-display').value = model;

  var qtyInput = document.getElementById('quantity-input');
  if (qtyInput) {
    qtyInput.max = qty;
    if (parseInt(qtyInput.value, 10) > qty) { qtyInput.value = qty; }
  }
}

(function ($) {
  if ($.fn.colorbox) {
    $('.colorbox').colorbox({ overlayClose: true, opacity: 0.3, width: 600, height: 480 });
  }
}(jQuery));
//--></script>

<?php echo $footer; ?>