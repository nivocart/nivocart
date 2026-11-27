/*!
 * NivoCart dashboard widget kit (nc-widgets.js)
 * Requires: jQuery 3.x and jQuery UI 1.12+ (widget factory + easing). No other dependencies.
 *
 * Widgets (all configured with data- attributes, initialised with $.nc.init()):
 *   ncCounter  - animated number            <span data-nc="counter" data-value="1284" data-prefix="£" data-decimals="2">
 *   ncGauge    - SVG radial gauge           <div data-nc="gauge" data-value="92" data-label="Health" data-unit="%">
 *   ncDonut    - SVG segmented donut        <div data-nc="donut" data-segments='[{"label":"UK","value":62}]'>
 *   ncBars     - bar / equaliser / sparkline <div data-nc="bars" data-values="[1,2,3]" data-compact="true">
 *   ncProgress - segmented progress bar     <div data-nc="progress" data-value="81" data-label="Sales target">
 *
 * Status colours (ok / warn / bad / info) are set as classes, never inline, so every theme
 * stylesheet decides how they look. Animations stop when body has .gx-calm or the visitor
 * prefers reduced motion.
 */
(function($) {
	'use strict';

	var SVGNS = 'http://www.w3.org/2000/svg';
	var motion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

	function calm() {
		return (motion && motion.matches) || $('body').hasClass('gx-calm');
	}

	function svgEl(tag, attrs) {
		var el = document.createElementNS(SVGNS, tag);
		$.each(attrs, function(key, value) {
			el.setAttribute(key, value);
		});
		return el;
	}

	function clamp(n, lo, hi) {
		return Math.min(hi, Math.max(lo, n));
	}

	// Reads data- attributes for every option the widget declares.
	function dataOptions($el, defaults) {
		var out = {};
		$.each(defaults, function(key) {
			var value = $el.data(key);
			if (value !== undefined) {
				out[key] = value;
			}
		});
		return out;
	}

	function fmt(n, o) {
		var text = Number(n).toLocaleString('en-GB', {
			minimumFractionDigits: o.decimals,
			maximumFractionDigits: o.decimals
		});
		return (o.prefix || '') + text + (o.suffix || '');
	}

	// Tween 0 -> 1 using jQuery's animation clock (requestAnimationFrame, pauses in hidden tabs).
	function tween(duration, delay, step) {
		var state = {p: 0};
		if (calm() || !duration) {
			step(1);
			return $(state);
		}
		step(0);
		return $(state).delay(delay || 0).animate({p: 1}, {
			duration: duration,
			easing: 'easeOutCubic',
			step: function() {
				step(state.p);
			},
			complete: function() {
				step(1);
			}
		});
	}

	// ok / warn / bad / info from a value. "invert" means a higher value is worse (errors, pending).
	function statusOf(o) {
		if (o.status && o.status !== 'auto') {
			return o.status;
		}
		var v = o.max ? o.value / o.max * 100 : 0;
		var warn = (o.warn !== null) ? o.warn : (o.invert ? 25 : 80);
		var bad = (o.bad !== null) ? o.bad : (o.invert ? 60 : 50);
		if (o.invert) {
			return (v <= warn) ? 'ok' : ((v <= bad) ? 'warn' : 'bad');
		}
		return (v >= warn) ? 'ok' : ((v >= bad) ? 'warn' : 'bad');
	}

	function setStatus($el, status) {
		$el.removeClass('is-ok is-warn is-bad is-info').addClass('is-' + status);
	}

	/* ---------- ncCounter ---------- */
	$.widget('nc.ncCounter', {
		options: {value: 0, decimals: 0, prefix: '', suffix: '', duration: 1400, delay: 0},
		_getCreateOptions: function() {
			return dataOptions(this.element, this.options);
		},
		_create: function() {
			this.element.addClass('nc-counter');
			this.current = 0;
			this._run();
		},
		_run: function() {
			var self = this;
			var o = this.options;
			var from = this.current;
			var to = Number(o.value);
			if (this.tw) {
				this.tw.stop(true);
			}
			this.tw = tween(o.duration, o.delay, function(p) {
				self.current = from + (to - from) * p;
				self.element.text(fmt(self.current, o));
			});
		},
		_setOption: function(key, value) {
			this._super(key, value);
			if (key === 'value') {
				this.options.delay = 0;
				this._run();
			}
		},
		_destroy: function() {
			if (this.tw) {
				this.tw.stop(true);
			}
			this.element.removeClass('nc-counter').empty();
		}
	});

	/* ---------- ncGauge ---------- */
	$.widget('nc.ncGauge', {
		options: {
			value: 0, max: 100, decimals: 0, unit: '', label: '',
			status: 'auto', invert: false, warn: null, bad: null, duration: 1500, delay: 0
		},
		_getCreateOptions: function() {
			return dataOptions(this.element, this.options);
		},
		_create: function() {
			var svg = svgEl('svg', {'class': 'nc-gauge-svg', viewBox: '0 0 120 120', 'aria-hidden': 'true', focusable: 'false'});
			this.fill = svgEl('circle', {
				'class': 'nc-gauge-fill', cx: 60, cy: 60, r: 48, pathLength: 100,
				'stroke-dasharray': '0 100', transform: 'rotate(-90 60 60)'
			});
			svg.appendChild(svgEl('circle', {'class': 'nc-gauge-ticks', cx: 60, cy: 60, r: 57, pathLength: 100}));
			svg.appendChild(svgEl('circle', {'class': 'nc-gauge-track', cx: 60, cy: 60, r: 48, pathLength: 100}));
			svg.appendChild(svgEl('circle', {'class': 'nc-gauge-inner', cx: 60, cy: 60, r: 39, pathLength: 100}));
			svg.appendChild(this.fill);

			this.$value = $('<b class="nc-gauge-value"></b>');
			this.$unit = $('<i class="nc-gauge-unit"></i>');
			this.$label = $('<span class="nc-gauge-label"></span>');

			this.element.addClass('nc-gauge').attr('role', 'img')
				.append(svg)
				.append($('<div class="nc-gauge-text"></div>')
					.append($('<div class="nc-gauge-main"></div>').append(this.$value, this.$unit), this.$label));
			this.cur = 0;
			this._render();
		},
		_render: function() {
			var self = this;
			var o = this.options;
			var from = this.cur;
			var to = Number(o.value);
			setStatus(this.element, statusOf(o));
			this.$unit.text(o.unit);
			this.$label.text(o.label);
			this.element.attr('aria-label', o.label + ' ' + to + o.unit);
			if (this.tw) {
				this.tw.stop(true);
			}
			this.tw = tween(o.duration, o.delay, function(p) {
				var v = from + (to - from) * p;
				var pc = clamp(o.max ? v / o.max * 100 : 0, 0, 100);
				self.cur = v;
				self.fill.setAttribute('stroke-dasharray', pc + ' ' + (100 - pc));
				self.$value.text(fmt(v, {decimals: o.decimals}));
			});
		},
		_setOption: function(key, value) {
			this._super(key, value);
			this.options.delay = 0;
			this._render();
		},
		_destroy: function() {
			if (this.tw) {
				this.tw.stop(true);
			}
			this.element.removeClass('nc-gauge is-ok is-warn is-bad is-info').removeAttr('role aria-label').empty();
		}
	});

	/* ---------- ncDonut ---------- */
	$.widget('nc.ncDonut', {
		options: {segments: [], duration: 1600, delay: 0, gap: 0.8},
		_getCreateOptions: function() {
			return dataOptions(this.element, this.options);
		},
		_create: function() {
			var self = this;
			var o = this.options;
			var list = $.isArray(o.segments) ? o.segments : [];
			var total = 0;
			$.each(list, function(i, s) {
				total += Number(s.value) || 0;
			});

			this.circles = [];
			this.parts = [];
			var svg = svgEl('svg', {'class': 'nc-donut-svg', viewBox: '0 0 120 120', 'aria-hidden': 'true', focusable: 'false'});
			svg.appendChild(svgEl('circle', {'class': 'nc-donut-track', cx: 60, cy: 60, r: 44, pathLength: 100}));

			var $legend = $('<ul class="nc-donut-legend"></ul>');
			var start = 0;
			$.each(list, function(i, s) {
				var len = total ? (Number(s.value) || 0) / total * 100 : 0;
				var circle = svgEl('circle', {
					'class': 'nc-donut-seg nc-seg-' + (i % 8), cx: 60, cy: 60, r: 44, pathLength: 100,
					'stroke-dasharray': '0 100', 'stroke-dashoffset': -(start + o.gap / 2), transform: 'rotate(-90 60 60)'
				});
				svg.appendChild(circle);
				self.circles.push(circle);
				self.parts.push({label: String(s.label), pct: len, start: start, len: len});

				$legend.append($('<li class="nc-seg-' + (i % 8) + '"></li>').attr('data-i', i)
					.append('<i class="nc-donut-sw"></i>')
					.append($('<span class="nc-donut-lbl"></span>').text(s.label))
					.append($('<b class="nc-donut-pc"></b>').text((Math.round(len * 10) / 10) + '%')));
				start += len;
			});

			this.$pct = $('<b class="nc-donut-pct"></b>');
			this.$name = $('<span class="nc-donut-name"></span>');
			var $chart = $('<div class="nc-donut-chart"></div>').append(svg)
				.append($('<div class="nc-donut-center"></div>').append(this.$pct, this.$name));

			this.element.addClass('nc-donut').append($chart, $legend);
			this._hl(null);

			$(this.circles).on('mouseenter.ncDonut', function() {
				self._hl($.inArray(this, self.circles));
			}).on('mouseleave.ncDonut', function() {
				self._hl(null);
			});
			$legend.on('mouseenter.ncDonut', 'li', function() {
				self._hl($(this).data('i'));
			}).on('mouseleave.ncDonut', function() {
				self._hl(null);
			});

			this.tw = tween(o.duration, o.delay, function(p) {
				var drawn = p * 100;
				$.each(self.parts, function(i, part) {
					var d = clamp(drawn - part.start, 0, part.len);
					d = d > 0 ? Math.max(0, d - o.gap) : 0;
					self.circles[i].setAttribute('stroke-dasharray', d + ' 100');
				});
			});
		},
		// Highlight one segment (or none) and show its value in the centre.
		_hl: function(index) {
			var part = this.parts[index === null ? 0 : index];
			this.element.toggleClass('is-dim', index !== null);
			$.each(this.circles, function(i, circle) {
				circle.classList.toggle('is-hl', i === index);
			});
			this.element.find('.nc-donut-legend li').each(function(i) {
				$(this).toggleClass('is-hl', i === index);
			});
			if (part) {
				this.$pct.text((Math.round(part.pct * 10) / 10) + '%');
				this.$name.text(part.label);
			}
		},
		_destroy: function() {
			if (this.tw) {
				this.tw.stop(true);
			}
			$(this.circles).off('.ncDonut');
			this.element.removeClass('nc-donut is-dim').empty();
		}
	});

	/* ---------- ncBars ---------- */
	$.widget('nc.ncBars', {
		options: {
			values: [], labels: [], max: null, status: 'info', current: -1, compact: false,
			numbered: false, from: 0, labelEvery: 1, live: false, interval: 4000, duration: 800, delay: 0
		},
		_getCreateOptions: function() {
			return dataOptions(this.element, this.options);
		},
		_create: function() {
			var self = this;
			var o = this.options;
			this.vals = $.makeArray(o.values);
			this.fills = [];
			this.titles = [];

			var $plot = $('<div class="nc-bars-plot"></div>');
			$.each(this.vals, function(i) {
				var $fill = $('<div class="nc-bar-fill"></div>');
				var $bar = $('<div class="nc-bar"></div>').append($fill);
				if (i === o.current) {
					$bar.addClass('is-current');
				}
				$plot.append($bar);
				self.fills.push($fill);
				self.titles.push($bar);
			});
			this.element.addClass('nc-bars').toggleClass('is-compact', !!o.compact).append($plot);
			setStatus(this.element, o.status);

			if (!o.compact && (o.numbered || $.isArray(o.labels) && o.labels.length)) {
				var $labels = $('<div class="nc-bars-labels"></div>');
				$.each(this.vals, function(i) {
					var text = '';
					if (i % o.labelEvery === 0) {
						text = o.numbered ? ('0' + (i + o.from)).slice(-2) : (o.labels[i] || '');
					}
					$labels.append($('<span></span>').text(text));
				});
				this.element.append($labels);
			}

			var animated = !calm();
			$.each(this.fills, function(i, $fill) {
				$fill.css('transition-delay', animated ? (o.delay + i * 35) + 'ms' : '0ms');
			});
			$plot[0].offsetHeight; // force layout so the height transition runs
			this._paint();

			window.setTimeout(function() {
				$.each(self.fills, function(i, $fill) {
					$fill.css('transition-delay', '0ms');
				});
			}, o.delay + this.vals.length * 35 + o.duration);

			if (o.live) {
				this.timer = window.setInterval(function() {
					if (calm() || document.hidden) {
						return;
					}
					var i = o.current >= 0 ? o.current : self.vals.length - 1;
					self.vals[i] += 1 + Math.floor(Math.random() * 2);
					self._paint();
				}, o.interval);
			}
		},
		_paint: function() {
			var self = this;
			var o = this.options;
			var max = o.max || (Math.max.apply(null, this.vals) * 1.1) || 1;
			$.each(this.fills, function(i, $fill) {
				var pc = clamp(self.vals[i] / max * 100, 0, 100);
				$fill.css('height', (self.vals[i] > 0 ? Math.max(pc, 3) : 0) + '%');
				var label = o.numbered ? ('0' + (i + o.from)).slice(-2) : (o.labels[i] || '');
				self.titles[i].attr('title', (label ? label + ': ' : '') + self.vals[i]);
			});
		},
		_destroy: function() {
			if (this.timer) {
				window.clearInterval(this.timer);
			}
			this.element.removeClass('nc-bars is-compact is-ok is-warn is-bad is-info').empty();
		}
	});

	/* ---------- ncProgress ---------- */
	$.widget('nc.ncProgress', {
		options: {
			value: 0, max: 100, label: '', valueText: '', status: 'auto',
			invert: false, warn: null, bad: null, duration: 1400, delay: 0
		},
		_getCreateOptions: function() {
			return dataOptions(this.element, this.options);
		},
		_create: function() {
			var self = this;
			var o = this.options;
			var pct = clamp(o.max ? o.value / o.max * 100 : 0, 0, 100);
			this.$val = $('<b class="nc-progress-val"></b>');
			this.$fill = $('<div class="nc-progress-fill"></div>');
			this.$track = $('<div class="nc-progress-track" role="progressbar"></div>')
				.attr({'aria-valuemin': 0, 'aria-valuemax': o.max, 'aria-valuenow': o.value, 'aria-label': o.label})
				.append(this.$fill);

			this.element.addClass('nc-progress')
				.append($('<div class="nc-progress-head"></div>')
					.append($('<span class="nc-progress-label"></span>').text(o.label), this.$val))
				.append(this.$track);
			setStatus(this.element, statusOf(o));

			this.tw = tween(o.duration, o.delay, function(p) {
				self.$fill.css('width', (pct * p) + '%');
				self.$val.text(o.valueText || (Math.round(pct * p) + '%'));
			});
		},
		_destroy: function() {
			if (this.tw) {
				this.tw.stop(true);
			}
			this.element.removeClass('nc-progress is-ok is-warn is-bad is-info').empty();
		}
	});

	/* ---------- helpers ---------- */
	$.nc = {
		calm: calm,
		// Initialise every [data-nc="counter|gauge|donut|bars|progress"] inside a context.
		init: function(context) {
			$(context || document).find('[data-nc]').each(function() {
				var type = String($(this).data('nc'));
				var name = 'nc' + type.charAt(0).toUpperCase() + type.slice(1);
				if ($.fn[name]) {
					$(this)[name]();
				}
			});
		}
	};
}(jQuery));
