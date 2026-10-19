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
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/report.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="location='<?php echo $close; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content-body cp-report">
      <div class="attention"><?php echo $text_ean_required; ?></div>
      <div id="cp-result" hidden></div>
      <div id="tabs" class="htabs">
        <a href="#tab-scan"><?php echo $tab_scan_report; ?></a>
        <a href="#tab-settings"><?php echo $tab_settings; ?></a>
      </div>
      <div id="tab-scan">
      <?php if (!$installed) { ?>
      <div class="tooltip"><?php echo $text_install_message; ?></div>
      <div class="buttons">
        <a href="<?php echo $action_install; ?>" class="button-form ripple"><?php echo $button_install; ?></a>
      </div>
      <?php } else { ?>
      <div class="tooltip"><?php echo $text_info; ?></div>
      <h2><?php echo $text_stats; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $entry_country; ?></td>
          <td><select id="cp-country" class="cp-narrow">
              <?php foreach ($countries as $code => $name) { ?>
              <option value="<?php echo $code; ?>"<?php echo ($code === $country) ? ' selected="selected"' : ''; ?>><?php echo $name; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_limit; ?></td>
          <td><input type="text" id="cp-limit" value="<?php echo (int)$limit; ?>" size="5" class="cp-narrow" />
            <a id="button-show" class="button-form button-align ripple"><?php echo $button_show; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $text_stat_enabled; ?></td>
          <td><?php echo (int)$stats['enabled']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_stat_no_ean; ?></td>
          <td><?php echo (int)$stats['no_ean']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_stat_invalid; ?></td>
          <td><?php echo (int)$stats['invalid_ean']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_stat_checked; ?></td>
          <td><?php echo (int)$stats['checked']; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_stat_cheaper; ?></td>
          <td><?php echo (int)$total_cheaper; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_scan; ?><span class="help"><?php echo $text_scan_help; ?></span></td>
          <td><a id="button-scan" class="button-form ripple"><i class="fa fa-refresh"></i> <?php echo $button_scan; ?></a>
            <a id="button-clear" class="button-form ripple"><?php echo $button_clear_cache; ?></a></td>
        </tr>
      </table>
      <div id="cp-progress" class="cp-progress" hidden>
        <div id="cp-progress-bar" class="progress-bar-blue"></div>
      </div>
      <div id="cp-progress-label" class="cp-progress-label"></div>
      <h2><?php echo $heading_title; ?></h2>
      <div class="tooltip"><?php echo $text_special_note; ?></div>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_product; ?></td>
            <td class="left"><?php echo $column_ean; ?></td>
            <td class="right"><?php echo $column_price; ?></td>
            <td class="right"><?php echo $column_competitor; ?></td>
            <td class="left"><?php echo $column_seller; ?></td>
            <td class="right"><?php echo $column_gap; ?></td>
            <td class="right"><?php echo $column_floor; ?></td>
            <td class="center"><?php echo $column_match; ?></td>
            <td class="left"><?php echo $column_checked; ?></td>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows) { ?>
          <?php foreach ($rows as $row) { ?>
          <tr>
            <td class="left"><a href="<?php echo $row['href']; ?>"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></a></td>
            <td class="left cp-mono"><?php echo htmlspecialchars($row['ean'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="right cp-nowrap"><?php if ($row['has_special']) { ?>
              <span class="cp-special"><?php echo $row['own_price']; ?></span>
              <?php } else { ?>
              <?php echo $row['own_price']; ?>
              <?php } ?></td>
            <td class="right cp-nowrap"><?php echo $row['competitor']; ?></td>
            <td class="left"><?php if ($row['url'] && strpos($row['url'], 'https://') === 0) { ?>
              <a href="<?php echo htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($row['seller'], ENT_QUOTES, 'UTF-8'); ?></a>
              <?php } else { ?>
              <?php echo htmlspecialchars($row['seller'], ENT_QUOTES, 'UTF-8'); ?>
              <?php } ?></td>
            <td class="right cp-nowrap"><?php echo $row['gap']; ?> (<?php echo $row['gap_percent']; ?>%)</td>
            <td class="right cp-nowrap"><?php echo $row['floor']; ?></td>
            <td class="center"><?php if ($row['can_match'] === null) { ?>
              <span class="cp-badge cp-badge-unknown"><?php echo $text_unknown; ?></span>
              <?php } elseif ($row['can_match']) { ?>
              <span class="cp-badge cp-badge-ok"><?php echo $text_yes; ?></span>
              <?php } else { ?>
              <span class="cp-badge cp-badge-no"><?php echo $text_no; ?></span>
              <?php } ?></td>
            <td class="left cp-nowrap"><?php echo $row['date_checked']; ?></td>
          </tr>
          <?php } ?>
          <?php } else { ?>
          <tr>
            <td class="center" colspan="9"><?php echo ($stats['checked'] > 0) ? $text_no_results : $text_never_scanned; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <p class="cp-showing"><?php echo $text_showing; ?></p>
      <?php } ?>
      </div>
      <div id="tab-settings">
      <form action="<?php echo $action_settings; ?>" method="post" enctype="multipart/form-data" id="form-settings">
        <table class="form">
          <tr>
            <td><?php echo $entry_client_id; ?><span class="help"><?php echo $help_client_id; ?></span></td>
            <td><input type="text" name="client_id" value="<?php echo htmlspecialchars($setting_client_id, ENT_QUOTES, 'UTF-8'); ?>" class="cp-wide" autocomplete="off" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_client_secret; ?><span class="help"><?php echo $help_client_secret; ?><?php echo $setting_has_secret ? ' ' . $text_secret_saved : ''; ?></span></td>
            <td><input type="password" name="client_secret" value="" class="cp-wide" autocomplete="new-password" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_default_country; ?></td>
            <td><select name="country" class="cp-narrow">
                <?php foreach ($countries as $code => $name) { ?>
                <option value="<?php echo $code; ?>"<?php echo ($code === $setting_country) ? ' selected="selected"' : ''; ?>><?php echo $name; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_results_limit; ?><span class="help"><?php echo $help_results_limit; ?></span></td>
            <td><input type="text" name="results_limit" value="<?php echo (int)$setting_results_limit; ?>" size="5" class="cp-narrow" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_recheck_days; ?><span class="help"><?php echo $help_recheck_days; ?></span></td>
            <td><input type="text" name="recheck_days" value="<?php echo (int)$setting_recheck_days; ?>" size="5" class="cp-narrow" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_include_postage; ?><span class="help"><?php echo $help_include_postage; ?></span></td>
            <td><input type="checkbox" name="include_postage" value="1"<?php echo $setting_include_postage ? ' checked="checked"' : ''; ?> /></td>
          </tr>
          <tr>
            <td><?php echo $entry_ignore_sellers; ?><span class="help"><?php echo $help_ignore_sellers; ?></span></td>
            <td><input type="text" name="ignore_sellers" value="<?php echo htmlspecialchars($setting_ignore_sellers, ENT_QUOTES, 'UTF-8'); ?>" class="cp-wide" /></td>
          </tr>
          <tr>
            <td></td>
            <td><a onclick="$('#form-settings').submit();" class="button-form ripple"><?php echo $button_save; ?></a>
              <a id="button-test" class="button-form ripple"><?php echo $button_test; ?></a></td>
          </tr>
        </table>
      </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$('#tabs a').tabs();

var cp = <?php echo json_encode($cp_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function fmt(text) {
	var args = Array.prototype.slice.call(arguments, 1);
	var i = 0;

	return text.replace(/%s/g, function() {
		return (i < args.length) ? args[i++] : '';
	});
}

function showResult(message, type) {
	$('#cp-result').attr('class', type).text(message).prop('hidden', false);
}

function pageUrl() {
	return cp.url.page + '&country=' + encodeURIComponent($('#cp-country').val()) + '&limit=' + encodeURIComponent($('#cp-limit').val());
}

$('#button-show').on('click', function() {
	location = pageUrl();
});

$('#cp-limit').on('keydown', function(event) {
	if (event.key === 'Enter') {
		event.preventDefault();

		location = pageUrl();
	}
});

// Test the eBay credentials
$('#button-test').on('click', function() {
	showResult(cp.text.text_scanning, 'tooltip');

	$.ajax({
		url: cp.url.test,
		dataType: 'json',
		success: function(res) {
			if (res.error) {
				showResult(res.error, 'warning');
			} else {
				showResult(cp.text.text_test_ok, 'success');
			}
		},
		error: function() {
			showResult(cp.text.error_ajax, 'warning');
		}
	});
});

// Clear the stored prices of the chosen country
$('#button-clear').on('click', function() {
	$.confirm({
		title: cp.text.button_clear_cache,
		content: cp.text.text_confirm_clear,
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				location = cp.url.clear + '&country=' + encodeURIComponent($('#cp-country').val());
			},
			cancel: function() { }
		}
	});
});

// Scan: one batch per request until the results limit is reached or nothing is left
var scanState = null;

function scanBatch() {
	var s = scanState;

	$.ajax({
		url: cp.url.scan + '&country=' + encodeURIComponent(s.country) + '&after_id=' + s.afterId,
		dataType: 'json',
		success: function(res) {
			if (res.error) {
				scanEnd(res.error, 'warning', false);

				return;
			}

			s.checked += res.processed;
			s.errors += res.errors.length;
			s.afterId = res.last_id;
			s.cheaper = res.cheaper_total;

			if (res.total > 0) {
				s.total = res.total;
			}

			var pct = (s.total > 0) ? Math.min(100, Math.round((s.checked / s.total) * 100)) : 0;

			$('#cp-progress-bar').css('width', pct + '%');
			$('#cp-progress-label').text(pct + '%');

			showResult(fmt(cp.text.text_scan_progress, s.checked, s.cheaper, s.limit), 'tooltip');

			if (s.cheaper >= s.limit) {
				scanEnd(fmt(cp.text.text_scan_limit, s.checked), 'success', true);
			} else if (res.has_more) {
				setTimeout(scanBatch, 500);
			} else if (s.checked === 0) {
				scanEnd(cp.text.text_scan_nothing, 'success', false);
			} else {
				scanEnd(fmt(cp.text.text_scan_complete, s.checked, s.cheaper), 'success', true);
			}
		},
		error: function() {
			scanEnd(cp.text.error_ajax, 'warning', false);
		}
	});
}

function scanEnd(message, type, reload) {
	if (scanState && scanState.errors > 0) {
		message += ' ' + fmt(cp.text.text_scan_errors, scanState.errors);
	}

	showResult(message, type);

	$('#button-scan').removeClass('cp-busy');

	scanState = null;

	if (reload) {
		setTimeout(function() {
			location = pageUrl();
		}, 2500);
	}
}

$('#button-scan').on('click', function() {
	if (scanState) {
		return;
	}

	$('#button-scan').addClass('cp-busy');

	scanState = {
		country: $('#cp-country').val(),
		limit: Math.max(1, parseInt($('#cp-limit').val(), 10) || cp.limit),
		afterId: 0,
		checked: 0,
		errors: 0,
		total: 0,
		cheaper: 0
	};

	$('#cp-progress').prop('hidden', false);
	$('#cp-progress-bar').css('width', '0%');
	$('#cp-progress-label').text('');

	showResult(cp.text.text_scanning, 'tooltip');

	scanBatch();
});
//--></script>
<?php echo $footer; ?>