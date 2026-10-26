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
        <?php if ($cj_channels) { ?>
        <a id="mc-sync-shipping" class="button-save ripple"><?php echo $button_sync_shipping; ?></a>
        <?php } ?>
      </p>
      <?php if ($cj_channels) { ?>
      <div id="mc-sync-progress" class="cj-progress" hidden>
        <div id="mc-sync-progress-bar" class="progress-bar-blue"></div>
      </div>
      <div id="mc-sync-progress-label" class="cj-progress-label"></div>
      <?php } ?>
      <?php if ($products) { ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_product; ?></td>
            <td class="left"><?php echo $column_sku; ?></td>
            <td class="right"><?php echo $column_cost_original; ?></td>
            <td class="right"><?php echo $column_shipping_cost; ?></td>
            <td class="right"><?php echo $column_total_cost; ?></td>
            <td class="right"><?php echo $column_total_cost_default; ?></td>
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
            $m = ($product['margin_pct'] === null) ? null : (float)$product['margin_pct'];

            if ($m === null) {
              $badge = 'mc-badge-low';
              $label = $status_no_rate;
            } elseif ($m < 0) {
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
            <td class="right"<?php if ($product['original_currency'] !== $default_currency && $product['exchange_rate'] !== null) { ?> title="<?php echo htmlspecialchars(sprintf($text_rate_title, $default_currency, rtrim(rtrim(number_format((float)$product['exchange_rate'], 6, '.', ''), '0'), '.'), $product['original_currency']), ENT_QUOTES, 'UTF-8'); ?>"<?php } ?>><?php echo htmlspecialchars($product['original_currency'], ENT_QUOTES, 'UTF-8'); ?> <?php echo number_format((float)$product['original_cost'], 2); ?></td>
            <td class="right"><?php echo htmlspecialchars($product['original_currency'], ENT_QUOTES, 'UTF-8'); ?> <input type="number" step="any" min="0" class="mc-price-input" id="shipping-input-<?php echo (int)$product['product_id']; ?>" data-product-id="<?php echo (int)$product['product_id']; ?>" value="<?php echo number_format((float)$product['shipping_cost'], 2, '.', ''); ?>" /></td>
            <td class="right" id="total-original-<?php echo (int)$product['product_id']; ?>" data-currency="<?php echo htmlspecialchars($product['original_currency'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($product['original_currency'], ENT_QUOTES, 'UTF-8'); ?> <?php echo number_format((float)$product['total_cost_original'], 2); ?></td>
            <td class="right" id="total-<?php echo (int)$product['product_id']; ?>"><?php echo ($m === null) ? '—' : number_format((float)$product['total_cost'], 2); ?></td>
            <td class="right"><input type="number" step="any" min="0" class="mc-price-input" id="price-input-<?php echo (int)$product['product_id']; ?>" data-product-id="<?php echo (int)$product['product_id']; ?>" value="<?php echo number_format((float)$product['price'], 2, '.', ''); ?>" /></td>
            <td class="right mc-margin" id="margin-<?php echo (int)$product['product_id']; ?>"><?php echo ($m === null) ? '—' : number_format($m, 2) . '%'; ?></td>
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
var mcSyncShippingUrl = <?php echo json_encode($sync_shipping_url); ?>;
var mcCjChannels = <?php echo json_encode($cj_channels); ?>;
var mcThreshold = <?php echo (float)$threshold; ?>;
var mcText = <?php echo json_encode([
  'error_price'     => $error_price,
  'sync_running'    => $text_sync_running,
  'sync_done'       => $text_sync_done,
  'sync_progress'   => $text_sync_progress,
  'refresh'         => $button_refresh,
  'sync_retry'      => $text_sync_retry,
  'sync_no_freight' => $text_sync_no_freight,
  'sync_ranged'     => $text_sync_ranged,
  'error_shipping'  => $error_shipping,
  'error_server'    => $error_server,
  'status_good'     => $status_good,
  'status_low'      => $status_low,
  'status_negative' => $status_negative,
  'status_no_rate'  => $status_no_rate
]); ?>;

function mcShowResult(message, isError) {
  $('#mc-result').attr('class', isError ? 'warning' : 'success').text(message).prop('hidden', false);

  window.scrollTo(0, 0);
}

function mcUpdatePrice(productId) {
  var $input = $('#price-input-' + productId);
  var $shipping = $('#shipping-input-' + productId);
  var newPrice = parseFloat($input.val());
  var newShipping = parseFloat($shipping.val() === '' ? '0' : $shipping.val());

  if (isNaN(newPrice) || newPrice < 0) {
    mcShowResult(mcText.error_price, true);
    return;
  }

  if (isNaN(newShipping) || newShipping < 0) {
    mcShowResult(mcText.error_shipping, true);
    return;
  }

  $.ajax({
    url: mcUpdatePriceUrl,
    type: 'POST',
    dataType: 'json',
    data: {product_id: productId, price: newPrice.toFixed(2), shipping_cost: newShipping.toFixed(2)},
    success: function(res) {
      if (res.error) {
        mcShowResult(res.error, true);
        return;
      }

      var marginPct = (res.margin_pct === null) ? null : parseFloat(res.margin_pct);
      var badgeClass = 'mc-badge-good';
      var badgeLabel = mcText.status_good;

      if (marginPct === null) {
        badgeClass = 'mc-badge-low';
        badgeLabel = mcText.status_no_rate;
      } else if (marginPct < 0) {
        badgeClass = 'mc-badge-negative';
        badgeLabel = mcText.status_negative;
      } else if (marginPct < mcThreshold) {
        badgeClass = 'mc-badge-low';
        badgeLabel = mcText.status_low;
      }

      var $totalOriginal = $('#total-original-' + productId);

      $input.val(newPrice.toFixed(2));
      $shipping.val(newShipping.toFixed(2));
      $totalOriginal.text($totalOriginal.data('currency') + ' ' + parseFloat(res.total_cost_original).toFixed(2));
      $('#total-' + productId).text((res.total_cost === null) ? '—' : parseFloat(res.total_cost).toFixed(2));
      $('#margin-' + productId).text((marginPct === null) ? '—' : marginPct.toFixed(2) + '%');
      $('#badge-' + productId).attr('class', 'mc-badge ' + badgeClass).text(badgeLabel);

      mcShowResult(res.message, false);
    },
    error: function(xhr) {
      mcShowResult(mcText.error_server + ' ' + xhr.status, true);
    }
  });
}

// Shipping sync: one CJ page per request with a pause in between, and a retry when CJ rate-limits us
var MC_SYNC_PAUSE = 3500;
var MC_SYNC_RETRY_PAUSE = 30000;
var MC_SYNC_MAX_RETRIES = 3;

function mcProgressInit() {
  $('#mc-sync-progress').stop(true, true).css('display', '').prop('hidden', false);
  $('#mc-sync-progress-bar').css('width', '0%');
  $('#mc-sync-progress-label').text('');
}

function mcProgressUpdate(pct, label) {
  $('#mc-sync-progress-bar').css('width', pct + '%');
  $('#mc-sync-progress-label').text(label);
}

function mcProgressDone() {
  $('#mc-sync-progress-bar').css('width', '100%');
  $('#mc-sync-progress-label').text('100%');

  setTimeout(function() {
    $('#mc-sync-progress').fadeOut(600, function() {
      $(this).prop('hidden', true);

      $('#mc-sync-progress-label').text('');
    });
  }, 1000);
}

function mcSyncFinish(isError) {
  $('#mc-sync-shipping').removeClass('mc-busy');

  mcProgressDone();

  // The result message stays on screen until the page is refreshed by hand
  if (!isError) {
    $('<a id="mc-sync-refresh" class="button-form ripple"></a>').text(mcText.refresh).on('click', function() {
      window.location.reload();
    }).insertAfter('#mc-sync-shipping');
  }
}

function mcSyncShipping(channelIndex, page, totals, attempt) {
  if (channelIndex >= mcCjChannels.length) {
    var doneText = mcText.sync_done.replace('%s', totals.updated).replace('%s', totals.zero).replace('%s', totals.uk).replace('%s', totals.notImported).replace('%s', totals.unseen).replace('%s', totals.unseenSample.length ? ' (first PIDs: ' + totals.unseenSample.join(', ') + ')' : '');

    if (totals.ranged > 0) {
      doneText += mcText.sync_ranged.replace('%s', totals.ranged);
    }

    if (totals.noFreight > 0) {
      doneText += mcText.sync_no_freight.replace('%s', totals.noFreight).replace('%s', totals.noFreightSample.join('; '));
    }

    mcShowResult(doneText, totals.noFreight > 0);
    mcSyncFinish(false);
    return;
  }

  $.ajax({
    url: mcSyncShippingUrl,
    type: 'POST',
    dataType: 'json',
    data: {channel_id: mcCjChannels[channelIndex], page: page},
    success: function(res) {
      if (res.error) {
        // CJ rate limit: wait and retry the same page a few times before giving up
        if (/429|rate limit/i.test(res.error) && attempt < MC_SYNC_MAX_RETRIES) {
          mcShowResult(mcText.sync_retry, false);

          setTimeout(function() { mcSyncShipping(channelIndex, page, totals, attempt + 1); }, MC_SYNC_RETRY_PAUSE);
          return;
        }

        mcShowResult(res.error, true);
        mcSyncFinish(true);
        return;
      }

      totals.updated += res.updated;
      totals.zero += res.zero_freight;
      totals.uk += res.uk_skipped;
      totals.notImported += res.not_imported;
      totals.unseen += res.unseen_rows;
      totals.noFreight += res.no_freight;
      totals.ranged += res.ranged;

      if (res.no_freight_sample && res.no_freight_sample.length && totals.noFreightSample.length < 3) {
        totals.noFreightSample = totals.noFreightSample.concat(res.no_freight_sample).slice(0, 3);
      }

      if (res.unseen_sample && res.unseen_sample.length) {
        totals.unseenSample = res.unseen_sample;
      }

      var pages = Math.max(1, Math.ceil(res.total / res.page_size));
      var pct = Math.min(100, Math.round((page / pages) * 100));

      mcProgressUpdate(pct, mcText.sync_progress.replace('%s', pct).replace('%s', page).replace('%s', pages));

      if (res.has_more) {
        mcShowResult(mcText.sync_running.replace('%s', page + 1).replace('%s', pages), false);

        setTimeout(function() { mcSyncShipping(channelIndex, page + 1, totals, 0); }, MC_SYNC_PAUSE);
      } else {
        setTimeout(function() { mcSyncShipping(channelIndex + 1, 1, totals, 0); }, MC_SYNC_PAUSE);
      }
    },
    error: function(xhr) {
      mcShowResult(mcText.error_server + ' ' + xhr.status, true);
      mcSyncFinish(true);
    }
  });
}

$('#mc-sync-shipping').on('click', function() {
  if ($(this).hasClass('mc-busy')) {
    return;
  }

  $(this).addClass('mc-busy');

  mcProgressInit();
  mcShowResult(mcText.sync_running.replace('%s', 1).replace('%s', '?'), false);
  $('#mc-sync-refresh').remove();
  mcSyncShipping(0, 1, {updated: 0, zero: 0, uk: 0, notImported: 0, unseen: 0, unseenSample: [], noFreight: 0, noFreightSample: [], ranged: 0}, 0);
});

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