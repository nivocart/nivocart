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
        <a id="button-add-channel" class="button-save ripple"><?php echo $button_add_channel; ?></a>
        <a onclick="location.reload();" class="button-form ripple"><?php echo $button_refresh; ?></a>
        <a onclick="location = '<?php echo $close; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content-body cj-dashboard">
      <?php if ($success) { ?>
      <div class="success"><?php echo $success; ?></div>
      <?php } ?>
      <?php if ($error) { ?>
      <div class="warning"><?php echo $error; ?></div>
      <?php } ?>
      <div id="cj-result" hidden></div>
      <h2><?php echo $tab_channels; ?></h2>
      <?php if ($channels) { ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_channel_name; ?></td>
            <td class="left"><?php echo $column_status; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($channels as $channel) { ?>
          <tr>
            <td class="left"><?php echo htmlspecialchars($channel['name'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="left"><?php if ($channel['status']) { ?>
              <span class="enabled"><?php echo $text_channel_active; ?></span>
              <?php } else { ?>
              <span class="disabled"><?php echo $text_channel_inactive; ?></span>
              <?php } ?></td>
            <td class="right">
              <a class="button-form ripple cj-edit-channel" data-channel-id="<?php echo (int)$channel['channel_id']; ?>"><?php echo $button_edit; ?></a>
              <a class="button-cancel ripple cj-delete-channel" data-channel-id="<?php echo (int)$channel['channel_id']; ?>"><?php echo $button_delete; ?></a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
      <div class="attention"><?php echo $text_no_channels; ?></div>
      <?php } ?>
      <form id="form-channel" method="post" action="<?php echo $channel_add_url; ?>" data-add="<?php echo $channel_add_url; ?>" data-edit="<?php echo $channel_edit_url; ?>" hidden>
        <h3 id="form-channel-title"><?php echo $text_add_channel; ?></h3>
        <input type="hidden" name="channel_id" id="channel-id" value="" />
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_channel_name; ?></td>
            <td><input type="text" name="name" id="channel-name" value="" class="cj-wide" required /></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_api_key; ?><span class="help"><?php echo $help_api_key; ?></span></td>
            <td><input type="text" name="consumer_key" id="channel-consumer-key" value="" class="cj-wide" required /></td>
          </tr>
          <tr>
            <td><?php echo $entry_webhook_secret; ?><span class="help"><?php echo $help_webhook_secret; ?></span></td>
            <td><input type="text" name="webhook_secret" id="channel-webhook-secret" value="" class="cj-wide" />
              <a id="button-generate-secret" class="button-form ripple" title="<?php echo $title_generate_secret; ?>"><i class="fa fa-key"></i></a></td>
          </tr>
          <tr>
            <td><?php echo $entry_location_name; ?></td>
            <td><input type="text" name="location_name" id="channel-location-name" value="Default" class="cj-wide" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_shipping_map; ?><span class="help"><?php echo $help_shipping_map; ?></span></td>
            <td><textarea name="shipping_map" id="channel-shipping-map" rows="4" class="cj-wide cj-mono">{"flat_rate": "CJPacket Ordinary"}</textarea></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="status" id="channel-status" class="cj-wide">
                <option value="1" selected="selected"><?php echo $text_channel_active; ?></option>
                <option value="0"><?php echo $text_channel_inactive; ?></option>
              </select></td>
          </tr>
        </table>
        <div class="cj-actions">
          <a onclick="$('#form-channel').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
          <a id="button-cancel-channel" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
        </div>
      </form>
      <div class="overview">
        <div class="dashboard-heading"><?php echo $button_import; ?></div>
        <div class="dashboard-content">
          <table class="form">
            <tr>
              <td><?php echo $entry_import_channel; ?></td>
              <td><select id="import-channel-id" class="cj-narrow">
                  <?php if ($channels) { ?>
                  <?php foreach ($channels as $channel) { ?>
                  <option value="<?php echo (int)$channel['channel_id']; ?>"><?php echo htmlspecialchars($channel['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_none; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $entry_import_category; ?><span class="help"><?php echo $help_import_category; ?></span></td>
              <td><input type="text" id="import-category-name" value="" placeholder="<?php echo $placeholder_category; ?>" class="cj-wide" autocomplete="off" />
                <input type="hidden" id="import-category-id" value="0" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_import_keyword; ?><span class="help"><?php echo $help_import_keyword; ?></span></td>
              <td><input type="text" id="import-keyword" value="" placeholder="<?php echo $placeholder_keyword; ?>" class="cj-wide" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_import_country; ?></td>
              <td><select id="import-country" class="cj-narrow">
                  <?php foreach ($countries as $code => $country) { ?>
                  <option value="<?php echo $code; ?>"><?php echo $country; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td></td>
              <td><a id="button-import" class="button-form ripple"><?php echo $button_import; ?></a></td>
            </tr>
          </table>
        </div>
      </div>
      <div class="statistic">
        <div class="dashboard-heading"><?php echo $header_set_option; ?></div>
        <div class="dashboard-content">
          <table class="form">
            <tr>
              <td><?php echo $entry_import_channel; ?></td>
              <td><select id="sync-channel-id" class="cj-narrow">
                  <?php if ($channels) { ?>
                  <?php foreach ($channels as $channel) { ?>
                  <option value="<?php echo (int)$channel['channel_id']; ?>"><?php echo htmlspecialchars($channel['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_none; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $button_sync_stock; ?></td>
              <td><a id="button-sync-stock" class="button-form ripple cj-sync"><i class="fa fa-refresh"></i> <?php echo $button_sync_stock; ?></a></td>
            </tr>
            <tr>
              <td><?php echo $button_sync_prices; ?></td>
              <td><a id="button-sync-prices" class="button-form ripple cj-sync"><i class="fa fa-refresh"></i> <?php echo $button_sync_prices; ?></a></td>
            </tr>
            <tr>
              <td><?php echo $button_sync_tracking; ?></td>
              <td><a id="button-sync-tracking" class="button-form ripple cj-sync"><i class="fa fa-refresh"></i> <?php echo $button_sync_tracking; ?></a></td>
            </tr>
            <tr>
              <td><?php echo $button_register_webhooks; ?></td>
              <td><a id="button-register-webhooks" class="button-form ripple"><?php echo $button_register_webhooks; ?></a></td>
            </tr>
            <tr>
              <td><?php echo $button_test_connection; ?></td>
              <td><a id="button-test-connection" class="button-form ripple"><?php echo $button_test_connection; ?></a></td>
            </tr>
          </table>
          <div id="sync-progress" class="cj-progress" hidden>
            <div id="sync-progress-bar" class="progress-bar-blue"></div>
          </div>
          <div id="sync-progress-label" class="cj-progress-label"></div>
        </div>
      </div>
      <h2><?php echo $tab_orders; ?></h2>
      <div class="cj-toolbar">
        <select id="orders-channel-select" class="cj-narrow">
          <?php foreach ($channels as $channel) { ?>
          <option value="<?php echo (int)$channel['channel_id']; ?>"><?php echo htmlspecialchars($channel['name'], ENT_QUOTES, 'UTF-8'); ?></option>
          <?php } ?>
        </select>
        <a id="button-orders-refresh" class="button-form ripple"><i class="fa fa-refresh"></i> <?php echo $button_refresh; ?></a>
      </div>
      <div id="orders-result" hidden></div>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_order_id; ?></td>
            <td class="left"><?php echo $column_customer; ?></td>
            <td class="left"><?php echo $column_date; ?></td>
            <td class="right"><?php echo $column_total; ?></td>
            <td class="left"><?php echo $column_order_status; ?></td>
            <td class="left"><?php echo $column_cjd_status; ?></td>
            <td class="left"><?php echo $column_tracking_number; ?></td>
            <td class="left"><?php echo $column_action; ?></td>
            <td class="left"><?php echo $column_email_tracking; ?></td>
            <td class="left"><?php echo $column_email_sent; ?></td>
          </tr>
        </thead>
        <tbody id="orders-tbody">
          <tr>
            <td class="center" colspan="10"><?php echo $text_orders_select; ?></td>
          </tr>
        </tbody>
      </table>
      <div id="orders-pagination" class="pagination" hidden>
        <div class="links"><a href="#" id="orders-prev"><?php echo $text_prev; ?></a> <a href="#" id="orders-next"><?php echo $text_next; ?></a></div>
        <div class="results" id="orders-page-info"></div>
      </div>
      <h2><?php echo $tab_products; ?></h2>
      <div class="cj-toolbar">
        <select id="products-channel-select" class="cj-narrow">
          <?php foreach ($channels as $channel) { ?>
          <option value="<?php echo (int)$channel['channel_id']; ?>"><?php echo htmlspecialchars($channel['name'], ENT_QUOTES, 'UTF-8'); ?></option>
          <?php } ?>
        </select>
        <a id="button-products-refresh" class="button-form ripple"><i class="fa fa-refresh"></i> <?php echo $button_refresh; ?></a>
      </div>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_product_name; ?></td>
            <td class="left"><?php echo $column_variant_key; ?></td>
            <td class="left"><?php echo $column_sku; ?></td>
            <td class="right"><?php echo $column_stock; ?></td>
            <td class="right"><?php echo $column_supplier_cost; ?></td>
            <td class="left"><?php echo $column_last_stock_sync; ?></td>
            <td class="left"><?php echo $column_last_price_sync; ?></td>
          </tr>
        </thead>
        <tbody id="products-tbody">
          <tr>
            <td class="center" colspan="7"><?php echo $text_loading; ?></td>
          </tr>
        </tbody>
      </table>
      <div id="products-pagination" class="pagination" hidden>
        <div class="links"><a href="#" id="products-prev"><?php echo $text_prev; ?></a> <a href="#" id="products-next"><?php echo $text_next; ?></a></div>
        <div class="results" id="products-page-info"></div>
      </div>
      <h2><?php echo $text_products_maintenance; ?></h2>
      <div id="purge-result" hidden></div>
      <div id="purge-dup-result" hidden></div>
      <table class="form">
        <tr>
          <td><?php echo $text_purge_confirm; ?></td>
          <td><a id="button-purge-stale" class="button-form ripple"><?php echo $button_purge_stale; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $text_purge_dup_confirm; ?></td>
          <td><a id="button-purge-dup" class="button-form ripple"><?php echo $button_purge_duplicates; ?></a></td>
        </tr>
      </table>
      <h2><?php echo $text_note; ?></h2>
      <div class="tooltip"><?php echo $text_cj_payment_note; ?></div>
      <div class="tooltip"><?php echo $text_cj_currency_note; ?></div>
      <a href="https://cjdropshipping.com/home" target="_blank" class="button-form ripple"><?php echo $button_cj_dashboard; ?></a>
      <h2><?php echo $tab_about; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $text_cj_version; ?></td>
          <td><?php echo $cj_version; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_cj_author; ?></td>
          <td><?php echo $cj_author; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_cj_support; ?></td>
          <td><a href="mailto:<?php echo $cj_support; ?>"><?php echo $cj_support; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $text_cj_license; ?></td>
          <td><a class="about" onclick="window.open('http://opensource.org/licenses/gpl-3.0.html');" title=""><?php echo $cj_license; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $text_cj_tables; ?></td>
          <td><?php echo $cj_tables; ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var cj = <?php echo json_encode($cj_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var channelData = <?php echo json_encode(array_column($channels, null, 'channel_id'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

var ordersLimit = 20;
var ordersStart = 0;
var ordersTotal = 0;
var ordersChannel = 0;

var productsLimit = 20;
var productsStart = 0;
var productsTotal = 0;
var productsChannel = 0;

// -----------------------------------------------------------------
// Utilities
// -----------------------------------------------------------------
function htmlEsc(s) {
	return String(s === null || s === undefined ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmt(template) {
	var args = Array.prototype.slice.call(arguments, 1);
	var i = 0;

	return String(template).replace(/%[s%]/g, function(match) {
		if (match === '%%') {
			return '%';
		}

		return (i < args.length) ? args[i++] : '';
	});
}

// type: success | warning | tooltip
function showMessage(id, message, type) {
	$('#' + id).attr('class', type).html(message).prop('hidden', false);
}

function hideMessage(id) {
	$('#' + id).prop('hidden', true);
}

function showResult(message, type) {
	showMessage('cj-result', message, type);

	window.scrollTo(0, 0);
}

function showLoading() {
	showMessage('cj-result', cj.text.text_loading, 'tooltip');
}

function request(url, callback, failId) {
	$.ajax({
		url: url,
		type: 'GET',
		dataType: 'json',
		success: callback,
		error: function() {
			if (failId) {
				showMessage(failId, cj.text.error_ajax, 'warning');
			} else {
				showResult(cj.text.error_sync_failed, 'warning');
			}
		}
	});
}

function channelId(selectId) {
	return parseInt($('#' + selectId).val(), 10) || 0;
}

function withErrors(message, errors) {
	if (errors && errors.length) {
		message += '<br />' + fmt(cj.text.text_sync_errors, errors.join(', '));
	}

	return message;
}

// -----------------------------------------------------------------
// Channel form (add / edit share one inline form)
// -----------------------------------------------------------------
function openChannelForm(channel) {
	var $form = $('#form-channel');

	if (channel) {
		$form.attr('action', $form.data('edit'));
		$('#form-channel-title').text(cj.text.text_edit_channel);
		$('#channel-id').val(channel.channel_id);
		$('#channel-name').val(channel.name);
		$('#channel-consumer-key').val(channel.consumer_key);
		$('#channel-webhook-secret').val(channel.webhook_secret);
		$('#channel-location-name').val(channel.location_name);
		$('#channel-shipping-map').val(channel.shipping_map);
		$('#channel-status').val(channel.status);
	} else {
		$form.attr('action', $form.data('add'));
		$('#form-channel-title').text(cj.text.text_add_channel);
		$('#channel-id').val('');
		$('#channel-name').val('');
		$('#channel-consumer-key').val('');
		$('#channel-webhook-secret').val('');
		$('#channel-location-name').val('Default');
		$('#channel-shipping-map').val('{"flat_rate": "CJPacket Ordinary"}');
		$('#channel-status').val('1');
	}

	$form.prop('hidden', false);

	$('#channel-name').trigger('focus');
}

function closeChannelForm() {
	$('#form-channel').prop('hidden', true);
}

// Cryptographically random webhook secret (32 bytes, base64)
function generateWebhookSecret() {
	var array = new Uint8Array(32);

	window.crypto.getRandomValues(array);

	return btoa(String.fromCharCode.apply(null, array));
}

$('#button-add-channel').on('click', function() {
	if ($('#form-channel').prop('hidden')) {
		openChannelForm(null);
	} else {
		closeChannelForm();
	}
});

$('#button-cancel-channel').on('click', closeChannelForm);

$('#button-generate-secret').on('click', function() {
	$('#channel-webhook-secret').val(generateWebhookSecret());
});

$('.cj-edit-channel').on('click', function() {
	var channel = channelData[$(this).data('channel-id')];

	if (channel) {
		openChannelForm(channel);
	}
});

$('.cj-delete-channel').on('click', function() {
	var id = parseInt($(this).data('channel-id'), 10);

	$.confirm({
		title: cj.text.button_delete,
		content: cj.text.text_confirm_delete,
		type: 'red',
		typeAnimated: true,
		useBootstrap: false,
		boxWidth: 580,
		buttons: {
			confirm: {
				text: cj.text.button_delete,
				btnClass: 'btn-red',
				action: function() {
					location = cj.url.channel_delete + '&channel_id=' + id;
				}
			},
			cancel: {
				text: cj.text.button_cancel
			}
		}
	});
});

// -----------------------------------------------------------------
// Import (auto-paginated — one small page per AJAX call)
// -----------------------------------------------------------------
var importState = null;

function importPage() {
	var s = importState;

	showResult(fmt(cj.text.text_importing, s.page), 'tooltip');

	var url = cj.url.import
		+ '&channel_id=' + encodeURIComponent(s.channelId)
		+ '&category_id=' + encodeURIComponent(s.categoryId)
		+ '&keyword=' + encodeURIComponent(s.keyword)
		+ '&country=' + encodeURIComponent(s.country)
		+ '&page=' + encodeURIComponent(s.page);

	request(url, function(res) {
		if (res.error) {
			showResult(res.error, 'warning');

			importState = null;

			return;
		}

		s.totalImported += (res.imported || 0);
		s.totalSkipped += (res.skipped || 0);

		if (res.errors && res.errors.length) {
			s.allErrors = s.allErrors.concat(res.errors);
		}

		if (res.has_more) {
			s.page++;

			importPage();
		} else {
			showResult(withErrors(fmt(cj.text.text_import_complete, s.totalImported, s.totalSkipped), s.allErrors), s.allErrors.length ? 'warning' : 'success');

			importState = null;
		}
	});
}

$('#button-import').on('click', function() {
	var id = channelId('import-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	importState = {
		channelId: id,
		categoryId: $('#import-category-id').val(),
		keyword: $('#import-keyword').val(),
		country: $('#import-country').val(),
		page: 1,
		totalImported: 0,
		totalSkipped: 0,
		allErrors: []
	};

	importPage();
});

// Category autocomplete
$('#import-category-name').autocomplete({
	delay: 300,
	minLength: 2,
	source: function(request, response) {
		$.ajax({
			url: cj.url.category + '&filter_name=' + encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.name,
						category_id: item.category_id
					};
				}));
			}
		});
	},
	select: function(event, ui) {
		$('#import-category-id').val(ui.item.category_id);
		$('#import-category-name').val(ui.item.label);

		return false;
	},
	focus: function(event, ui) {
		$('#import-category-name').val(ui.item.label);

		return false;
	}
});

// Reset the hidden ID if the name field is cleared manually
$('#import-category-name').on('input', function() {
	if (!$(this).val()) {
		$('#import-category-id').val(0);
	}
});

// -----------------------------------------------------------------
// Sync progress bar
// -----------------------------------------------------------------
function progressInit() {
	$('#sync-progress').stop(true, true).css('display', '').prop('hidden', false);
	$('#sync-progress-bar').css('width', '0%');
	$('#sync-progress-label').text('');
}

function progressUpdate(pct, label) {
	$('#sync-progress-bar').css('width', pct + '%');
	$('#sync-progress-label').text(label);
}

function progressDone() {
	$('#sync-progress-bar').css('width', '100%');
	$('#sync-progress-label').text('100%');

	setTimeout(function() {
		$('#sync-progress').fadeOut(600, function() {
			$(this).prop('hidden', true);

			$('#sync-progress-label').text('');
		});
	}, 1000);
}

function setSyncBusy(busy) {
	$('.cj-sync').toggleClass('cj-busy', busy);
}

// -----------------------------------------------------------------
// Stock sync (auto-paginated — 10 variants per AJAX call)
// -----------------------------------------------------------------
var stockSyncState = null;

function stockSyncPage() {
	var s = stockSyncState;

	request(cj.url.sync_stock + '&channel_id=' + s.channelId + '&offset=' + s.offset, function(res) {
		if (res.error) {
			progressDone();
			showResult(res.error, 'warning');
			setSyncBusy(false);

			stockSyncState = null;

			return;
		}

		s.totalUpdated += (res.updated || 0);

		// Capture the grand total once (it's the same on every batch response)
		if (res.total && res.total > s.grandTotal) {
			s.grandTotal = res.total;
		}

		if (res.errors && res.errors.length) {
			s.allErrors = s.allErrors.concat(res.errors);
		}

		var progress = s.grandTotal > 0 ? fmt(cj.text.text_x_of_y, s.totalUpdated, s.grandTotal) : s.totalUpdated;

		if (res.has_more) {
			s.offset += (res.processed || 10);

			var pct = s.grandTotal > 0 ? Math.round((s.offset / s.grandTotal) * 100) : 0;

			progressUpdate(pct, fmt(cj.text.text_progress_variants, pct, s.offset, s.grandTotal));

			showResult(fmt(cj.text.text_stock_sync_complete, progress) + ' ...', 'tooltip');

			setTimeout(stockSyncPage, 5000); // 5-second pause between AJAX pages
		} else {
			progressDone();

			showResult(withErrors(fmt(cj.text.text_stock_sync_complete, progress), s.allErrors), s.allErrors.length ? 'warning' : 'success');

			setSyncBusy(false);

			stockSyncState = null;
		}
	});
}

$('#button-sync-stock').on('click', function() {
	var id = channelId('sync-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	setSyncBusy(true);

	stockSyncState = { channelId: id, offset: 0, totalUpdated: 0, grandTotal: 0, allErrors: [] };

	progressInit();
	showResult(cj.text.text_loading, 'tooltip');
	stockSyncPage();
});

// -----------------------------------------------------------------
// Price sync (auto-paginated — 5 PIDs per AJAX call)
// -----------------------------------------------------------------
var priceSyncState = null;

function priceSyncPage() {
	var s = priceSyncState;

	request(cj.url.sync_prices + '&channel_id=' + s.channelId + '&offset=' + s.offset, function(res) {
		if (res.error) {
			progressDone();
			showResult(res.error, 'warning');
			setSyncBusy(false);

			priceSyncState = null;

			return;
		}

		s.totalUpdated += (res.updated || 0);

		// Capture the grand total once (same on every batch response)
		if (res.total && res.total > s.grandTotal) {
			s.grandTotal = res.total;
		}

		if (res.errors && res.errors.length) {
			s.allErrors = s.allErrors.concat(res.errors);
		}

		var progress = fmt(cj.text.text_prices_updated, s.totalUpdated, s.grandTotal);

		if (res.has_more) {
			s.offset += (res.processed || 5);

			var pct = s.grandTotal > 0 ? Math.round((s.offset / s.grandTotal) * 100) : 0;

			progressUpdate(pct, fmt(cj.text.text_progress_pids, pct, s.offset, s.grandTotal));

			showResult(fmt(cj.text.text_price_sync_complete, progress) + ' ...', 'tooltip');

			setTimeout(priceSyncPage, 3500); // 3.5-second pause between AJAX pages
		} else {
			progressDone();

			showResult(withErrors(fmt(cj.text.text_price_sync_complete, progress), s.allErrors), s.allErrors.length ? 'warning' : 'success');

			setSyncBusy(false);

			priceSyncState = null;
		}
	});
}

$('#button-sync-prices').on('click', function() {
	var id = channelId('sync-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	setSyncBusy(true);

	priceSyncState = { channelId: id, offset: 0, totalUpdated: 0, grandTotal: 0, allErrors: [] };

	progressInit();
	showResult(cj.text.text_loading, 'tooltip');
	priceSyncPage();
});

// -----------------------------------------------------------------
// Tracking sync
// -----------------------------------------------------------------
$('#button-sync-tracking').on('click', function() {
	var id = channelId('sync-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	setSyncBusy(true);
	showLoading();

	$.ajax({
		url: cj.url.sync_tracking + '&channel_id=' + id,
		type: 'GET',
		dataType: 'json',
		success: function(res) {
			setSyncBusy(false);

			if (res.error) {
				showResult(res.error, 'warning');
			} else {
				showResult(withErrors(fmt(cj.text.text_tracking_sync_complete, res.updated), res.errors), (res.errors && res.errors.length) ? 'warning' : 'success');
			}
		},
		error: function() {
			setSyncBusy(false);

			showResult(cj.text.error_sync_failed, 'warning');
		}
	});
});

// -----------------------------------------------------------------
// Register webhooks / test connection
// -----------------------------------------------------------------
$('#button-register-webhooks').on('click', function() {
	var id = channelId('sync-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	showLoading();

	request(cj.url.register + '&channel_id=' + id, function(res) {
		showResult(res.error ? res.error : (res.message || cj.text.text_webhooks_registered), res.error ? 'warning' : 'success');
	});
});

$('#button-test-connection').on('click', function() {
	var id = channelId('sync-channel-id');

	if (id < 1) {
		showResult(cj.text.error_channel_id, 'warning');

		return;
	}

	showLoading();

	request(cj.url.test + '&channel_id=' + id, function(res) {
		if (res.error) {
			showResult(htmlEsc(cj.text.button_test_connection + ': ' + res.error), 'warning');

			return;
		}

		var msg = cj.text.text_test_auth + ': ' + htmlEsc(res.auth || '?') + ' | ' + cj.text.text_test_api + ': ' + htmlEsc(res.product_api || '?');

		if (res.token_prefix) {
			msg += ' | ' + cj.text.text_test_token + ': ' + htmlEsc(res.token_prefix);
		}

		if (res.token_expires) {
			msg += ' | ' + cj.text.text_test_expires + ': ' + htmlEsc(res.token_expires);
		}

		showResult(msg, res.product_api === 'OK' ? 'success' : 'warning');
	});
});

// -----------------------------------------------------------------
// Mapped products list (paginated)
// -----------------------------------------------------------------
function loadVariantList(id, start) {
	id = parseInt(id, 10) || 0;
	start = parseInt(start, 10) || 0;

	var $tbody = $('#products-tbody');
	var $pagination = $('#products-pagination');

	if (id < 1) {
		$tbody.html('<tr><td class="center" colspan="7">' + cj.text.text_no_channels + '</td></tr>');
		$pagination.prop('hidden', true);

		return;
	}

	productsChannel = id;
	productsStart = start;

	request(cj.url.variants + '&channel_id=' + id + '&start=' + start + '&limit=' + productsLimit, function(res) {
		if (res.error || !res.rows || res.rows.length === 0) {
			$tbody.html('<tr><td class="center" colspan="7">' + (res.error ? htmlEsc(res.error) : cj.text.text_none) + '</td></tr>');
			$pagination.prop('hidden', true);

			return;
		}

		productsTotal = parseInt(res.total, 10) || 0;

		var html = '';

		for (var i = 0; i < res.rows.length; i++) {
			var r = res.rows[i];

			html += '<tr>';
			html += '<td class="left">' + (htmlEsc(r.product_name) || '&mdash;') + '</td>';
			html += '<td class="left">' + (htmlEsc(r.variant_key) || '&mdash;') + '</td>';
			html += '<td class="left">' + (htmlEsc(r.supplier_sku) || '&mdash;') + '</td>';
			html += '<td class="right">' + (r.quantity !== undefined ? htmlEsc(r.quantity) : '&mdash;') + '</td>';
			html += '<td class="right">' + (r.supplier_cost !== undefined ? htmlEsc(r.supplier_cost) : '&mdash;') + '</td>';
			html += '<td class="left">' + (htmlEsc(r.last_stock_sync) || '&mdash;') + '</td>';
			html += '<td class="left">' + (htmlEsc(r.last_price_sync) || '&mdash;') + '</td>';
			html += '</tr>';
		}

		$tbody.html(html);

		var page = Math.floor(productsStart / productsLimit) + 1;
		var totalPages = Math.ceil(productsTotal / productsLimit);

		$('#products-page-info').text(fmt(cj.text.text_page_variants, page, totalPages, productsTotal));
		$('#products-prev').css('visibility', (productsStart > 0) ? 'visible' : 'hidden');
		$('#products-next').css('visibility', ((productsStart + productsLimit) < productsTotal) ? 'visible' : 'hidden');

		$pagination.prop('hidden', productsTotal <= productsLimit);
	});
}

$('#products-prev').on('click', function(e) {
	e.preventDefault();

	if (productsStart > 0) {
		loadVariantList(productsChannel, Math.max(0, productsStart - productsLimit));
	}
});

$('#products-next').on('click', function(e) {
	e.preventDefault();

	if ((productsStart + productsLimit) < productsTotal) {
		loadVariantList(productsChannel, productsStart + productsLimit);
	}
});

$('#button-products-refresh').on('click', function() {
	loadVariantList($('#products-channel-select').val(), 0);
});

// -----------------------------------------------------------------
// Purge stale / duplicate products (count first, then confirm)
// -----------------------------------------------------------------
function purgeFlow(opts) {
	var $result = $('#' + opts.resultId);

	$result.prop('hidden', true);

	request(opts.countUrl, function(res) {
		if (res.error) {
			showMessage(opts.resultId, htmlEsc(res.error), 'warning');

			return;
		}

		var count = parseInt(res.count, 10) || 0;

		if (count === 0) {
			showMessage(opts.resultId, opts.textNone, 'tooltip');

			return;
		}

		var content = fmt(opts.textFound, count);

		if (res.sample && res.sample.length) {
			content += '<br /><small><b>' + fmt(cj.text.text_purge_dup_sample, res.sample.length) + '</b><ul>';

			for (var i = 0; i < res.sample.length; i++) {
				var item = res.sample[i];

				content += '<li>#' + htmlEsc(item.product_id) + ' &mdash; ' + htmlEsc(item.product_name || item.supplier_sku) + ' (VID: ' + htmlEsc(item.supplier_vid) + ')</li>';
			}

			content += '</ul></small>';
		}

		content += '<br />' + opts.textConfirm;

		$.confirm({
			title: opts.title,
			content: content,
			type: 'red',
			typeAnimated: true,
			useBootstrap: false,
			boxWidth: 580,
			animation: 'zoom',
			closeAnimation: 'scale',
			opacity: 0.1,
			buttons: {
				confirm: {
					text: cj.text.button_purge_confirm,
					btnClass: 'btn-red',
					action: function() {
						request(opts.purgeUrl, function(res2) {
							if (res2.error) {
								showMessage(opts.resultId, htmlEsc(res2.error), 'warning');
							} else {
								showMessage(opts.resultId, fmt(opts.textComplete, res2.deleted), 'success');
							}
						}, opts.resultId);
					}
				},
				cancel: {
					text: cj.text.button_cancel
				}
			}
		});
	}, opts.resultId);
}

$('#button-purge-stale').on('click', function() {
	purgeFlow({
		resultId: 'purge-result',
		countUrl: cj.url.count_orphan,
		purgeUrl: cj.url.purge_stale,
		title: cj.text.button_purge_stale,
		textNone: cj.text.text_purge_none,
		textFound: cj.text.text_purge_found,
		textConfirm: cj.text.text_purge_confirm,
		textComplete: cj.text.text_purge_complete
	});
});

$('#button-purge-dup').on('click', function() {
	purgeFlow({
		resultId: 'purge-dup-result',
		countUrl: cj.url.count_dup,
		purgeUrl: cj.url.purge_dup,
		title: cj.text.button_purge_duplicates,
		textNone: cj.text.text_purge_dup_none,
		textFound: cj.text.text_purge_dup_found,
		textConfirm: cj.text.text_purge_dup_confirm,
		textComplete: cj.text.text_purge_dup_complete
	});
});

// -----------------------------------------------------------------
// Orders / dispatch
// -----------------------------------------------------------------
function loadOrdersList(id, start) {
	id = parseInt(id, 10) || 0;
	start = parseInt(start, 10) || 0;

	if (!id) {
		return;
	}

	ordersChannel = id;
	ordersStart = start;

	var $tbody = $('#orders-tbody');

	hideMessage('orders-result');

	$('#orders-pagination').prop('hidden', true);

	$tbody.html('<tr><td class="center" colspan="10">' + cj.text.text_loading + '</td></tr>');

	request(cj.url.orders + '&channel_id=' + id + '&start=' + start + '&limit=' + ordersLimit, function(res) {
		if (res.error) {
			showMessage('orders-result', htmlEsc(res.error), 'warning');

			$tbody.html('<tr><td class="center" colspan="10">&mdash;</td></tr>');

			return;
		}

		var rows = res.rows || [];

		ordersTotal = parseInt(res.total, 10) || 0;

		if (!rows.length) {
			$tbody.html('<tr><td class="center" colspan="10">' + cj.text.text_no_orders + '</td></tr>');

			return;
		}

		var html = '';

		$.each(rows, function(i, r) {
			var dispatched = parseInt(r.dispatched_items, 10) || 0;
			var failed = parseInt(r.failed_items, 10) || 0;
			var total = parseInt(r.cjd_items, 10) || 0;
			var badge = 'cj-badge-pending';
			var label = cj.text.text_status_pending;

			if (dispatched >= total && total > 0) {
				badge = 'cj-badge-ok';
				label = cj.text.text_status_dispatched;
			} else if (failed > 0) {
				badge = 'cj-badge-error';
				label = cj.text.text_status_error;
			}

			// Tracking: link to 17track for each number
			var trackingCell = '&mdash;';

			if (r.tracking && $.trim(r.tracking)) {
				trackingCell = $.map(r.tracking.split(', '), function(t) {
					t = $.trim(t);

					return '<a href="https://t.17track.net/en#nums=' + encodeURIComponent(t) + '" target="_blank" title="' + htmlEsc(cj.text.text_track_17track) + '">' + htmlEsc(t) + '</a>';
				}).join('<br />');
			}

			var dispatchBtn = (dispatched < total) ? '<a class="button-form ripple cj-dispatch" data-order-id="' + parseInt(r.order_id, 10) + '">' + cj.text.button_dispatch + '</a>' : '&mdash;';

			var emailBtn = (r.tracking && $.trim(r.tracking)) ? '<a class="button-form ripple cj-email" data-order-id="' + parseInt(r.order_id, 10) + '">' + cj.text.button_email_tracking + '</a>' : '';

			var emailSent = r.tracking_email_sent ? htmlEsc(r.tracking_email_sent) : '&mdash;';

			html += '<tr>';
			html += '<td class="left">#' + parseInt(r.order_id, 10) + '</td>';
			html += '<td class="left">' + htmlEsc(r.customer) + '</td>';
			html += '<td class="left cj-nowrap">' + (r.date_added ? htmlEsc(String(r.date_added).substring(0, 10)) : '&mdash;') + '</td>';
			html += '<td class="right cj-nowrap">' + htmlEsc(r.currency_code) + ' ' + (parseFloat(r.total) || 0).toFixed(2) + '</td>';
			html += '<td class="left">' + htmlEsc(r.order_status) + '</td>';
			html += '<td class="left cj-nowrap"><span class="cj-badge ' + badge + '">' + dispatched + '/' + total + ' ' + htmlEsc(label) + '</span></td>';
			html += '<td class="left">' + trackingCell + '</td>';
			html += '<td class="left">' + dispatchBtn + '</td>';
			html += '<td class="left">' + emailBtn + '</td>';
			html += '<td class="left cj-nowrap" id="email-sent-' + parseInt(r.order_id, 10) + '">' + emailSent + '</td>';
			html += '</tr>';
		});

		$tbody.html(html);

		if (ordersTotal > ordersLimit) {
			var page = Math.floor(ordersStart / ordersLimit) + 1;
			var totalPages = Math.ceil(ordersTotal / ordersLimit);

			$('#orders-page-info').text(fmt(cj.text.text_page_orders, page, totalPages, ordersTotal));
			$('#orders-prev').css('visibility', (ordersStart > 0) ? 'visible' : 'hidden');
			$('#orders-next').css('visibility', (ordersStart + ordersLimit < ordersTotal) ? 'visible' : 'hidden');
			$('#orders-pagination').prop('hidden', false);
		}
	}, 'orders-result');
}

$('#orders-prev').on('click', function(e) {
	e.preventDefault();

	loadOrdersList(ordersChannel, Math.max(0, ordersStart - ordersLimit));
});

$('#orders-next').on('click', function(e) {
	e.preventDefault();

	if (ordersStart + ordersLimit < ordersTotal) {
		loadOrdersList(ordersChannel, ordersStart + ordersLimit);
	}
});

$('#button-orders-refresh').on('click', function() {
	loadOrdersList($('#orders-channel-select').val(), 0);
});

$('#orders-tbody').on('click', 'a.cj-email', function() {
	var $btn = $(this);
	var orderId = parseInt($btn.data('order-id'), 10);

	hideMessage('orders-result');

	$btn.addClass('cj-busy');

	$.ajax({
		url: cj.url.tracking_email + '&order_id=' + orderId + '&channel_id=' + ordersChannel,
		type: 'GET',
		dataType: 'json',
		success: function(res) {
			$btn.removeClass('cj-busy');

			if (res.error) {
				showMessage('orders-result', htmlEsc(res.error), 'warning');

				return;
			}

			$('#email-sent-' + orderId).text(res.sent_at);

			showMessage('orders-result', cj.text.text_tracking_email_sent, 'success');
		},
		error: function() {
			$btn.removeClass('cj-busy');

			showMessage('orders-result', cj.text.error_ajax, 'warning');
		}
	});
});

$('#orders-tbody').on('click', 'a.cj-dispatch', function() {
	var $btn = $(this);
	var orderId = parseInt($btn.data('order-id'), 10);
	var original = $btn.html();

	hideMessage('orders-result');

	$btn.addClass('cj-busy').text(cj.text.text_sending);

	$.ajax({
		url: cj.url.dispatch + '&order_id=' + orderId,
		type: 'GET',
		dataType: 'json',
		success: function(res) {
			$btn.removeClass('cj-busy').html(original);

			if (res.error) {
				showMessage('orders-result', fmt(cj.text.text_order_result, orderId, htmlEsc(res.error)), 'warning');
			} else {
				loadOrdersList(ordersChannel, ordersStart);

				// loadOrdersList() clears the message, so show the result afterwards
				showMessage('orders-result', fmt(cj.text.text_order_result, orderId, fmt(cj.text.text_dispatch_complete, res.dispatched || 0)), 'success');
			}
		},
		error: function() {
			$btn.removeClass('cj-busy').html(original);

			showMessage('orders-result', cj.text.error_ajax, 'warning');
		}
	});
});

// -----------------------------------------------------------------
// Initial load for the first channel
// -----------------------------------------------------------------
$(function() {
	var keys = Object.keys(channelData);

	if (keys.length) {
		$('#orders-channel-select').val(keys[0]);
		$('#products-channel-select').val(keys[0]);

		loadOrdersList(keys[0], 0);
		loadVariantList(keys[0], 0);
	} else {
		$('#products-tbody').html('<tr><td class="center" colspan="7">' + cj.text.text_no_channels + '</td></tr>');
	}
});
//--></script>
<?php echo $footer; ?>