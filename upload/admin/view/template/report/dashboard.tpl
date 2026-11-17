<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/report.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="location = '<?php echo $close; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content-body">
      <div class="report">
        <div class="left"><i class="fa fa-search gx-report-icon"></i></div>
        <div class="left">
          <span class="gx-tools gx-seg" id="rd-period">
            <button type="button" class="gx-btn<?php echo ($period === 'today') ? ' is-active' : ''; ?>" data-period="today"><?php echo $text_period_today; ?></button>
            <button type="button" class="gx-btn<?php echo ($period === 'week') ? ' is-active' : ''; ?>" data-period="week"><?php echo $text_period_week; ?></button>
            <button type="button" class="gx-btn<?php echo ($period === 'month') ? ' is-active' : ''; ?>" data-period="month"><?php echo $text_period_month; ?></button>
            <button type="button" class="gx-btn<?php echo ($period === 'year') ? ' is-active' : ''; ?>" data-period="year"><?php echo $text_period_year; ?></button>
            <button type="button" class="gx-btn<?php echo ($period === 'custom') ? ' is-active' : ''; ?>" data-period="custom"><?php echo $text_period_custom; ?></button>
          </span>
        </div>
        <div class="left"><em><?php echo $entry_date_start; ?></em> <input type="text" id="rd-start" value="<?php echo $date_start; ?>" size="11" /> <label for="rd-start" class="gx-date-icon"><i class="fa fa-calendar"></i></label></div>
        <div class="left"><em><?php echo $entry_date_end; ?></em> <input type="text" id="rd-end" value="<?php echo $date_end; ?>" size="11" /> <label for="rd-end" class="gx-date-icon"><i class="fa fa-calendar"></i></label></div>
        <div class="left"><a id="rd-apply" class="button-filter ripple"><?php echo $button_apply; ?></a></div>
        <div class="right"><span class="gx-period-label" id="rd-label"><?php echo $period_label; ?></span></div>
      </div>
      <div class="gx-grid" id="rd-dashboard">
      <?php if ($show_kpi) { ?>
        <div class="gx-contents" id="rd-kpi" data-group="kpi"></div>
      <?php } ?>
      <?php if ($show_sales) { ?>
        <div class="gx-panel gx-c8<?php echo ($show_attention || $show_other) ? ' gx-rs2' : ''; ?>">
          <h2 class="gx-panel-title"><?php echo $text_group_sales; ?>
          <?php if ($show_kpi) { ?>
            <ul class="gx-legend gx-tools" id="rd-legend"></ul>
          <?php } ?>
          </h2>
        <?php if ($show_kpi) { ?>
          <div id="rd-chart" class="gx-chart is-short"></div>
        <?php } ?>
          <div class="gx-body" data-group="sales"></div>
        </div>
      <?php } ?>
      <?php if ($show_attention) { ?>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_attention; ?></h2>
          <div class="gx-body" data-group="attention" data-static="1"></div>
        </div>
      <?php } ?>
      <?php if ($show_other) { ?>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_group_other; ?></h2>
          <div class="gx-body" data-group="other"></div>
        </div>
      <?php } ?>
      <?php if ($show_products) { ?>
        <div class="gx-panel <?php echo $show_affiliates ? 'gx-c4' : 'gx-c6'; ?>">
          <h2 class="gx-panel-title"><?php echo $text_group_products; ?></h2>
          <div class="gx-body" data-group="products"></div>
        </div>
      <?php } ?>
      <?php if ($show_customers) { ?>
        <div class="gx-panel <?php echo $show_affiliates ? 'gx-c4' : 'gx-c6'; ?>">
          <h2 class="gx-panel-title"><?php echo $text_group_customers; ?></h2>
          <div class="gx-body" data-group="customers"></div>
        </div>
      <?php } ?>
      <?php if ($show_affiliates) { ?>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_group_affiliates; ?></h2>
          <div class="gx-body" data-group="affiliates"></div>
        </div>
      <?php } ?>
      </div>
    <?php if ($sitemap) { ?>
      <h2 class="gx-sitemap-title"><?php echo $text_sitemap; ?></h2>
      <div class="gx-grid gx-sitemap">
      <?php foreach ($sitemap as $group) { ?>
        <div class="gx-panel gx-c3">
          <h2 class="gx-panel-title"><?php echo $group['title']; ?></h2>
        <?php foreach ($group['sections'] as $section) { ?>
          <?php if ($section['title']) { ?>
          <h3 class="gx-sitemap-sub"><?php echo $section['title']; ?></h3>
          <?php } ?>
          <ul class="gx-sitemap-list">
          <?php foreach ($section['items'] as $item) { ?>
            <li><a href="<?php echo $item['href']; ?>"><i class="fa <?php echo $item['icon']; ?>"></i><span><?php echo $item['text']; ?></span></a></li>
          <?php } ?>
          </ul>
        <?php } ?>
        </div>
      <?php } ?>
      </div>
    <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {
	var css = getComputedStyle(document.body);
	var period = {key: '<?php echo $period; ?>', start: '<?php echo $date_start; ?>', end: '<?php echo $date_end; ?>'};
	var $chart = $('#rd-chart');
	var $tip = $('<div class="gx-tip"></div>').appendTo('body');
	var ticksOf = {};
	var fontsReady = (document.fonts && document.fonts.ready) ? document.fonts.ready : $.Deferred().resolve().promise();

	function token(name) {
		return $.trim(css.getPropertyValue(name));
	}

	function pad(n) {
		return ('0' + n).slice(-2);
	}

	function iso(d) {
		return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
	}

	function params() {
		return '&period=' + period.key + '&start=' + encodeURIComponent(period.start) + '&end=' + encodeURIComponent(period.end);
	}

	function skeleton(group) {
		if (group === 'kpi') {
			var tile = '<div class="gx-panel gx-c3 gx-kpi gx-skel-tile"><i></i><i></i></div>';

			return tile + tile + tile + tile;
		}

		return '<div class="gx-skel"><i></i><i></i><i></i><i></i></div>';
	}

	// Groups are requested a few at a time so the server (and the session lock) is never flooded
	var queue = [];
	var running = 0;
	var MAX_PARALLEL = 3;

	function pump() {
		while (running < MAX_PARALLEL && queue.length) {
			fetchGroup(queue.shift());
		}
	}

	function loadGroup($target) {
		var group = $target.data('group');
		var xhr = $target.data('xhr');

		if (xhr) {
			xhr.abort();
		}

		queue = $.grep(queue, function($item) {
			return $item[0] !== $target[0];
		});

		$target.html(skeleton(group));

		queue.push($target);

		pump();
	}

	function fetchGroup($target) {
		var group = $target.data('group');

		running++;

		$target.data('xhr', $.ajax({
			type: 'get',
			url: 'index.php?route=report/dashboard/group&token=<?php echo $token; ?>&group=' + group + params(),
			dataType: 'html',
			success: function(html) {
				$target.html(html);

				$.nc.init($target);
			},
			error: function(jqXHR, textStatus) {
				if (textStatus === 'abort') {
					return;
				}

				$target.html('<p class="gx-all-clear is-error"><?php echo addslashes($error_group); ?> <a href="#" class="gx-retry"><?php echo addslashes($button_retry); ?></a></p>');

				$target.find('.gx-retry').on('click', function(e) {
					e.preventDefault();

					loadGroup($target);
				});
			},
			complete: function() {
				running--;

				pump();
			}
		}));
	}

	function drawChart() {
		if (!$chart.length) {
			return;
		}

		$.ajax({
			type: 'get',
			url: 'index.php?route=report/dashboard/chart&token=<?php echo $token; ?>' + params(),
			dataType: 'json',
			success: function(json) {
				if (!json['xaxis']) {
					return;
				}

				var step = Math.max(1, Math.ceil(json['xaxis'].length / 10));
				var ticks = [];

				for (var i = 0; i < json['xaxis'].length; i += step) {
					ticks.push(json['xaxis'][i]);
				}

				ticksOf = {};

				$.each(json['xaxis'], function(i, tick) {
					ticksOf[tick[0]] = tick[1];
				});

				var font = {size: 12, family: token('--gx-font-mono') || "'Share Tech Mono', monospace", color: token('--gx-muted')};

				var list = [
					{label: json['revenue']['label'], data: json['revenue']['data'], color: token('--gx-seg-0')},
					{label: json['profit']['label'], data: json['profit']['data'], color: token('--gx-seg-4')}
				];

				$chart.data('money', {prefix: json['prefix'], suffix: json['suffix']});

				fontsReady.then(function() {
					$.plot($chart, list, {
						series: {
							shadowSize: 0,
							lines: {show: true, lineWidth: 2, fill: true, fillColor: {colors: [{opacity: 0.28}, {opacity: 0.02}]}},
							points: {show: json['xaxis'].length <= 40, radius: 2.5, fill: true, fillColor: token('--gx-bg-0'), lineWidth: 1.5}
						},
						legend: {show: false},
						grid: {borderWidth: 0, color: token('--gx-muted'), tickColor: token('--gx-grid') || 'rgba(42, 79, 176, 0.45)', hoverable: true, labelMargin: 10},
						xaxis: {ticks: ticks, tickLength: 0, font: font},
						yaxis: {min: 0, tickDecimals: 0, font: font}
					});

					$chart.removeClass('is-reveal');
					void $chart[0].offsetWidth;
					$chart.addClass('is-reveal');
				});

				$('#rd-legend').html('<li class="nc-seg-0"><i></i>' + $('<div>').text(json['revenue']['label']).html() + '</li><li class="nc-seg-4"><i></i>' + $('<div>').text(json['profit']['label']).html() + '</li>');
			}
		});
	}

	function reloadAll() {
		$('#rd-dashboard .gx-body, #rd-kpi').each(function() {
			if (!$(this).data('static')) {
				loadGroup($(this));
			}
		});

		drawChart();

		$('#rd-start').val(period.start);
		$('#rd-end').val(period.end);
		$('#rd-label').text(period.start + ' - ' + period.end);

		$('#rd-period .gx-btn').removeClass('is-active').filter('[data-period="' + period.key + '"]').addClass('is-active');

		if (window.history && history.replaceState) {
			history.replaceState(null, '', 'index.php?route=report/dashboard&token=<?php echo $token; ?>' + params());
		}
	}

	$chart.on('plothover', function(e, pos, item) {
		if (!item) {
			$tip.hide();
			return;
		}

		var money = $chart.data('money') || {prefix: '', suffix: ''};

		$tip.text(item.series.label + ' ' + ticksOf[item.datapoint[0]] + ': ' + money.prefix + item.datapoint[1].toFixed(2) + money.suffix).css({left: pos.pageX + 14, top: pos.pageY - 12}).show();
	});

	$('#rd-period').on('click', 'button', function() {
		var key = $(this).data('period');
		var today = new Date();
		var start = new Date();

		if (key === 'custom') {
			$('#rd-start').trigger('focus');

			return;
		}

		if (key === 'week') {
			start.setDate(today.getDate() - 6);
		} else if (key === 'month') {
			start = new Date(today.getFullYear(), today.getMonth(), 1);
		} else if (key === 'year') {
			start = new Date(today.getFullYear(), 0, 1);
		}

		period = {key: key, start: iso(start), end: iso(today)};

		reloadAll();
	});

	$('#rd-apply').on('click', function() {
		var start = $('#rd-start').val();
		var end = $('#rd-end').val();

		if (!/^\d{4}-\d{2}-\d{2}$/.test(start) || !/^\d{4}-\d{2}-\d{2}$/.test(end)) {
			alert('<?php echo addslashes($error_date); ?>');

			return;
		}

		period = {key: 'custom', start: start, end: end};

		reloadAll();
	});

	$('#rd-start, #rd-end').datepicker({dateFormat: 'yy-mm-dd'}).on('keydown', function(e) {
		if (e.key === 'Enter') {
			$('#rd-apply').trigger('click');
		}
	});

	reloadAll();

	$('#rd-dashboard .gx-body[data-static]').each(function() {
		loadGroup($(this));
	});
});
//--></script>
<?php echo $footer; ?>