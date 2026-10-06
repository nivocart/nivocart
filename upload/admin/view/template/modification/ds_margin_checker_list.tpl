<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/offer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $dashboard_url; ?>" class="button-cancel ripple"><?php echo $button_dashboard; ?></a>
        <a href="<?php echo $settings_url; ?>" class="button-form ripple"><?php echo $button_settings; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="mc-result" hidden></div>
      <p>
        <a href="<?php echo $filter_all_url; ?>" class="<?php echo ($filter === 'all') ? 'button-save' : 'button-form'; ?> ripple"><?php echo $filter_all; ?></a>
        <a href="<?php echo $filter_below_url; ?>" class="<?php echo ($filter === 'below') ? 'button-save' : 'button-form'; ?> ripple"><?php echo $filter_below; ?> (&lt; <?php echo number_format($threshold, 1); ?>%)</a>
        <a href="<?php echo $filter_negative_url; ?>" class="<?php echo ($filter === 'negative') ? 'button-save' : 'button-form'; ?> ripple"><?php echo $filter_negative; ?></a>
      </p>
      <?php if ($products) { ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_product; ?></td>
            <td class="left"><?php echo $column_sku; ?></td>
            <td class="right"><?php echo $column_cost_ex_vat; ?></td>
            <td class="right"><?php echo $column_cost_inc_vat; ?></td>
            <td class="right"><?php echo $column_sale_price; ?></td>
            <td class="right"><?php echo $column_margin_pct; ?></td>
            <td class="left"><?php echo $column_status; ?></td>
            <td class="left"><?php echo $column_channel; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product) { ?>
          <?php
            $m = (float)$product['margin_pct'];

            if ($m < 0) {
              $badge = 'mc-badge-negative';
              $label = $status_negative;
            } elseif ($m < $threshold) {
              $badge = 'mc-badge-low';
              $label = $status_low;
            } else {
              $badge = 'mc-badge-good';
              $label = $status_good;
            }
          ?>
          <tr>
            <td class="left"><?php echo htmlspecialchars($product['product_name'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="left"><?php echo htmlspecialchars($product['model'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="right"><?php echo number_format((float)$product['supplier_cost'], 2); ?></td>
            <td class="right"><?php echo number_format((float)$product['cost_inc_vat'], 2); ?></td>
            <td class="right"><input type="number" step="any" min="0" class="mc-price-input" id="price-input-<?php echo (int)$product['product_id']; ?>" data-product-id="<?php echo (int)$product['product_id']; ?>" value="<?php echo number_format((float)$product['price'], 2, '.', ''); ?>" /></td>
            <td class="right mc-margin" id="margin-<?php echo (int)$product['product_id']; ?>"><?php echo number_format($m, 2); ?>%</td>
            <td class="left"><span class="mc-badge <?php echo $badge; ?>" id="badge-<?php echo (int)$product['product_id']; ?>"><?php echo $label; ?></span></td>
            <td class="left"><?php echo htmlspecialchars($product['channel_name'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="right"><a class="button-form ripple mc-update-price" data-product-id="<?php echo (int)$product['product_id']; ?>"><?php echo $button_update_price; ?></a></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
      <div class="attention"><?php echo $text_no_products; ?></div>
      <?php } ?>
      <?php if ($pagination) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
      <?php } ?>
    </div>
  </div>
</div>
<script>
var mcUpdatePriceUrl = <?php echo json_encode($update_price_url); ?>;
var mcThreshold = <?php echo (float)$threshold; ?>;
var mcText = <?php echo json_encode([
  'error_price'     => $error_price,
  'error_server'    => $error_server,
  'status_good'     => $status_good,
  'status_low'      => $status_low,
  'status_negative' => $status_negative
]); ?>;

function mcShowResult(message, isError) {
  $('#mc-result').attr('class', isError ? 'warning' : 'success').text(message).prop('hidden', false);

  window.scrollTo(0, 0);
}

function mcUpdatePrice(productId) {
  var $input = $('#price-input-' + productId);
  var newPrice = parseFloat($input.val());

  if (isNaN(newPrice) || newPrice < 0) {
    mcShowResult(mcText.error_price, true);
    return;
  }

  $.ajax({
    url: mcUpdatePriceUrl,
    type: 'POST',
    dataType: 'json',
    data: {product_id: productId, price: newPrice.toFixed(2)},
    success: function(res) {
      if (res.error) {
        mcShowResult(res.error, true);
        return;
      }

      var marginPct = parseFloat(res.margin_pct);
      var badgeClass = 'mc-badge-good';
      var badgeLabel = mcText.status_good;

      if (marginPct < 0) {
        badgeClass = 'mc-badge-negative';
        badgeLabel = mcText.status_negative;
      } else if (marginPct < mcThreshold) {
        badgeClass = 'mc-badge-low';
        badgeLabel = mcText.status_low;
      }

      $input.val(newPrice.toFixed(2));
      $('#margin-' + productId).text(marginPct.toFixed(2) + '%');
      $('#badge-' + productId).attr('class', 'mc-badge ' + badgeClass).text(badgeLabel);

      mcShowResult(res.message, false);
    },
    error: function(xhr) {
      mcShowResult(mcText.error_server + ' ' + xhr.status, true);
    }
  });
}

$('.mc-update-price').on('click', function() {
  mcUpdatePrice($(this).data('product-id'));
});

$('.mc-price-input').on('keydown', function(e) {
  if (e.key === 'Enter') {
    mcUpdatePrice($(this).data('product-id'));
  }
});
</script>
<?php echo $footer; ?>