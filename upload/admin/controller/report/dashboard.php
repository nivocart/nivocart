<?php
/**
 * Class ControllerReportDashboard
 *
 * Reports Dashboard: the centre point of all report pages. The page itself is a shell;
 * every block is loaded on its own through group() / chart() so a slow query never
 * holds the page up. Each block only previews the full report it links to.
 *
 * @package NivoCart
 */
class ControllerReportDashboard extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->language->load('report/dashboard');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('view/javascript/jquery/flot/jquery.flot.min.js');
		$this->document->addScript('view/javascript/jquery/flot/jquery.flot.resize.min.js');
		$this->document->addScript('view/javascript/nova/nc-nova.js');

		foreach (['heading_title', 'button_close', 'button_apply', 'button_retry', 'text_period_today', 'text_period_week', 'text_period_month', 'text_period_year', 'text_period_custom', 'text_vs_previous', 'text_group_sales', 'text_group_products', 'text_group_customers', 'text_group_affiliates', 'text_group_other', 'text_sitemap', 'text_attention', 'text_trend', 'entry_date_start', 'entry_date_end', 'error_date', 'error_group'] as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$period = $this->getPeriod();

		$this->data['period'] = $period['key'];
		$this->data['date_start'] = $period['start'];
		$this->data['date_end'] = $period['end'];
		$this->data['period_label'] = sprintf($this->language->get('text_period_range'), date('d M Y', strtotime($period['start'])), date('d M Y', strtotime($period['end'])));

		$this->data['show_kpi'] = $this->allowed('report/sale_order');
		$this->data['show_sales'] = (bool)$this->visibleRoutes('sales');
		$this->data['show_products'] = (bool)$this->visibleRoutes('products');
		$this->data['show_customers'] = (bool)$this->visibleRoutes('customers');
		$this->data['show_affiliates'] = (bool)$this->visibleRoutes('affiliates');
		$this->data['show_other'] = (bool)$this->visibleRoutes('other');
		$this->data['show_attention'] = $this->data['show_products'] || $this->data['show_other'] || $this->allowed('report/sale_return');

		$this->data['sitemap'] = $this->sitemap();

		$this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->template = 'report/dashboard.tpl';

		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * AJAX: one block of the dashboard as an HTML fragment.
	 */
	public function group() {
		$this->releaseSession();

		$group_started = microtime(true);

		$this->language->load('report/dashboard');

		$this->load->model('report/dashboard');

		$group = $this->request->get['group'] ?? '';
		$period = $this->getPeriod();

		$this->data['mode'] = 'rows';
		$this->data['rows'] = [];
		$this->data['text_no_data'] = $this->language->get('text_no_data');

		switch ($group) {
			case 'kpi':
				if (!$this->allowed('report/sale_order')) {
					return $this->deny();
				}

				$this->data['mode'] = 'kpi';
				$this->data['tiles'] = $this->kpiTiles($period);
				break;
			case 'sales':
				$this->data['rows'] = $this->salesRows($period);
				$this->data['columns'] = 2;
				break;
			case 'products':
				$this->data['rows'] = $this->productRows($period);
				break;
			case 'customers':
				$this->data['rows'] = $this->customerRows($period);
				break;
			case 'affiliates':
				$this->data['rows'] = $this->affiliateRows($period);
				break;
			case 'other':
				$this->data['rows'] = $this->otherRows($period);
				break;
			case 'attention':
				$this->data['mode'] = 'attention';
				$this->data['alerts'] = $this->attentionAlerts();
				$this->data['text_all_clear'] = $this->language->get('text_all_clear');
				break;
			default:
				return $this->deny();
		}

		$this->template = 'report/dashboard_group.tpl';

		$this->response->setOutput($this->render());

		$this->logSlow('group ' . $group, $group_started);
	}

	/**
	 * AJAX: revenue and profit series for the Sales Trend chart.
	 */
	public function chart() {
		$this->releaseSession();

		$json = [];

		if ($this->allowed('report/sale_order')) {
			$this->load->model('report/dashboard');

			$this->language->load('report/dashboard');

			$period = $this->getPeriod();

			$daily = $this->model_report_dashboard->getDailySales($period['start'], $period['end']);

			$mode = ($period['days'] <= 62) ? 'day' : (($period['days'] <= 400) ? 'week' : 'month');

			$buckets = [];
			$labels = [];

			for ($time = strtotime($period['start']); $time <= strtotime($period['end']); $time = strtotime('+1 day', $time)) {
				$day = date('Y-m-d', $time);

				if ($mode === 'day') {
					$key = $day;
					$label = date('d M', $time);
				} elseif ($mode === 'week') {
					$key = date('Y-m-d', strtotime('monday this week', $time));
					$label = date('d M', strtotime($key));
				} else {
					$key = date('Y-m', $time);
					$label = date('M y', $time);
				}

				if (!isset($buckets[$key])) {
					$buckets[$key] = ['revenue' => 0.0, 'profit' => 0.0];
					$labels[$key] = $label;
				}

				$buckets[$key]['revenue'] += $daily['revenue'][$day] ?? 0;
				$buckets[$key]['profit'] += $daily['profit'][$day] ?? 0;
			}

			$json['xaxis'] = [];
			$json['revenue'] = ['label' => $this->language->get('text_kpi_revenue'), 'data' => []];
			$json['profit'] = ['label' => $this->language->get('text_kpi_profit'), 'data' => []];

			$i = 0;

			foreach ($buckets as $key => $bucket) {
				$json['xaxis'][] = [$i, $labels[$key]];
				$json['revenue']['data'][] = [$i, round($bucket['revenue'], 2)];
				$json['profit']['data'][] = [$i, round($bucket['profit'], 2)];

				$i++;
			}

			$currency = $this->config->get('config_currency');

			$json['prefix'] = $this->currency->getSymbolLeft($currency);
			$json['suffix'] = $this->currency->getSymbolRight($currency);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Period handling
	 */
	private function getPeriod(): array {
		$key = $this->request->get['period'] ?? 'month';

		if (!in_array($key, ['today', 'week', 'month', 'year', 'custom'])) {
			$key = 'month';
		}

		$today = date('Y-m-d');

		switch ($key) {
			case 'today':
				$start = $today;
				$end = $today;
				break;
			case 'week':
				$start = date('Y-m-d', strtotime('-6 days'));
				$end = $today;
				break;
			case 'year':
				$start = date('Y-01-01');
				$end = $today;
				break;
			case 'custom':
				$start = $this->request->get['start'] ?? '';
				$end = $this->request->get['end'] ?? '';

				if (!$this->isDate($start) || !$this->isDate($end)) {
					$key = 'month';
					$start = date('Y-m-01');
					$end = $today;
				} elseif ($start > $end) {
					[$start, $end] = [$end, $start];
				}

				break;
			default:
				$start = date('Y-m-01');
				$end = $today;
				break;
		}

		$days = (int)((strtotime($end) - strtotime($start)) / 86400) + 1;

		if ($days > 1100) {
			$start = date('Y-m-d', strtotime('-1099 days', strtotime($end)));
			$days = 1100;
		}

		$prev_end = date('Y-m-d', strtotime('-1 day', strtotime($start)));
		$prev_start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days', strtotime($prev_end)));

		return [
			'key'        => $key,
			'start'      => $start,
			'end'        => $end,
			'days'       => $days,
			'prev_start' => $prev_start,
			'prev_end'   => $prev_end
		];
	}

	private function isDate(string $date): bool {
		$check = DateTime::createFromFormat('Y-m-d', $date);

		return $check && $check->format('Y-m-d') === $date;
	}

	/**
	 * Permissions and feature switches
	 */
	private function allowed(string $route): bool {
		return $this->user->hasPermission('access', $route);
	}

	/**
	 * Report index: a plain list of links to every report, by group
	 *
	 * Add, remove or reorder a report here. Each entry is [route, language key, icon, visible].
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function sitemap(): array {
		$this->load->model('report/dropship');
		$this->load->model('blog/status');

		$dropship = $this->model_report_dropship->dsTablesExist();
		$returns = !$this->config->get('config_return_disable');
		$online = (bool)$this->config->get('config_customer_online');
		$robots = (bool)$this->config->get('config_robots_online');
		$blog = (bool)$this->model_blog_status->checkBlog();
		$affiliates = !$this->config->get('config_affiliate_disable');

		$groups = [
			[
				'title'    => $this->language->get('text_group_sales'),
				'sections' => [
					['title' => '', 'reports' => [
						['report/sale_order', 'text_sale_order', 'fa-shopping-basket', true],
						['report/sale_profit', 'text_sale_profit', 'fa-line-chart', true],
						['report/sale_dropship_profit', 'text_sale_dropship', 'fa-briefcase', $dropship],
						['report/sale_tax', 'text_sale_tax', 'fa-cloud-download', true],
						['report/sale_shipping', 'text_sale_shipping', 'fa-truck', true],
						['report/sale_return', 'text_sale_return', 'fa-history', $returns],
						['report/sale_coupon', 'text_sale_coupon', 'fa-tags', true],
						['report/sale_voucher', 'text_sale_voucher', 'fa-ticket', true]
					]]
				]
			],
			[
				'title'    => $this->language->get('text_group_products'),
				'sections' => [
					['title' => '', 'reports' => [
						['report/product_viewed', 'text_product_viewed', 'fa-eye', true],
						['report/product_purchased', 'text_product_purchased', 'fa-dollar', true],
						['report/product_quantity', 'text_product_quantity', 'fa-cubes', true],
						['report/product_markup', 'text_product_markup', 'fa-bar-chart', true],
						['report/product_label', 'text_product_label', 'fa-leaf', true]
					]]
				]
			],
			[
				'title'    => $this->language->get('text_group_customers'),
				'sections' => [
					['title' => '', 'reports' => [
						['report/customer_order', 'text_customer_order', 'fa-shopping-bag', true],
						['report/customer_wishlist', 'text_customer_wishlist', 'fa-heart', true],
						['report/customer_reward', 'text_customer_reward', 'fa-gift', true],
						['report/customer_credit', 'text_customer_credit', 'fa-money', true],
						['report/customer_country', 'text_customer_country', 'fa-globe', true],
						['report/customer_deleted', 'text_customer_deleted', 'fa-eraser', true],
						['report/customer_online', 'text_customer_online', 'fa-wifi', $online]
					]]
				]
			],
			[
				'title'    => $this->language->get('text_group_other'),
				'sections' => [
					['title' => '', 'reports' => [
						['report/abandoned_carts', 'text_abandoned', 'fa-paper-plane', true],
						['report/banner_clicked', 'text_banner', 'fa-photo', true],
						['blog/report', 'text_blog', 'fa-eye', $blog],
						['report/robot_online', 'text_robot', 'fa-feed', $robots]
					]],
					['title' => $this->language->get('text_group_affiliates'), 'reports' => [
						['report/affiliate_activity', 'text_affiliate_activity', 'fa-spinner', $affiliates],
						['report/affiliate_commission', 'text_affiliate_commission', 'fa-percent', $affiliates]
					]]
				]
			]
		];

		$result = [];

		foreach ($groups as $group) {
			$sections = [];

			foreach ($group['sections'] as $section) {
				$items = [];

				foreach ($section['reports'] as $report) {
					if ($report[3] && $this->allowed($report[0])) {
						$items[] = [
							'icon' => $report[2],
							'text' => $this->language->get($report[1]),
							'href' => $this->link($report[0])
						];
					}
				}

				if ($items) {
					$sections[] = ['title' => $section['title'], 'items' => $items];
				}
			}

			if ($sections) {
				$result[] = ['title' => $group['title'], 'sections' => $sections];
			}
		}

		return $result;
	}

	private function visibleRoutes(string $group): array {
		$routes = [];

		switch ($group) {
			case 'sales':
				$routes = ['report/sale_order', 'report/sale_profit', 'report/sale_dropship_profit', 'report/sale_tax', 'report/sale_shipping', 'report/sale_return', 'report/sale_coupon', 'report/sale_voucher'];
				break;
			case 'products':
				$routes = ['report/product_viewed', 'report/product_purchased', 'report/product_quantity', 'report/product_markup', 'report/product_label'];
				break;
			case 'customers':
				$routes = ['report/customer_order', 'report/customer_wishlist', 'report/customer_reward', 'report/customer_credit', 'report/customer_country', 'report/customer_deleted', 'report/customer_online'];
				break;
			case 'affiliates':
				if ($this->config->get('config_affiliate_disable')) {
					return [];
				}

				$routes = ['report/affiliate_activity', 'report/affiliate_commission'];
				break;
			case 'other':
				$routes = ['report/abandoned_carts', 'report/banner_clicked', 'blog/report', 'report/robot_online'];
				break;
		}

		return array_values(array_filter($routes, fn($route) => $this->allowed($route)));
	}

	/**
	 * Log slow dashboard work (2 seconds or more) to the error log
	 */
	private function logSlow(string $what, float $started): void {
		$seconds = microtime(true) - $started;

		if ($seconds >= 2) {
			$this->log->write('Reports Dashboard: ' . $what . ' took ' . round($seconds, 1) . 's');
		}
	}

	/**
	 * Release the PHP session lock
	 *
	 * The AJAX endpoints only read the session. Without this every request waits for the
	 * previous one to finish (file session locking), so a single slow request blocks the page.
	 */
	private function releaseSession() {
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	private function deny() {
		$this->response->addHeader('HTTP/1.1 403 Forbidden');
		$this->response->setOutput('');
	}

	/**
	 * Helpers
	 */
	private function link(string $route, ?array $period = null): string {
		$url = '';

		if ($period) {
			$url = '&filter_date_start=' . $period['start'] . '&filter_date_end=' . $period['end'];
		}

		return $this->url->link($route, 'token=' . $this->session->data['token'] . $url, 'SSL');
	}

	private function fmt($value): string {
		return $this->currency->format((float)$value, $this->config->get('config_currency'));
	}

	private function num($value): string {
		return number_format((float)$value);
	}

	private function esc($text): string {
		return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8', false);
	}

	private function counter($value, bool $money = false): array {
		if ($money) {
			$currency = $this->config->get('config_currency');

			return [
				'value'    => round((float)$value, 2),
				'decimals' => (int)$this->currency->getDecimalPlace($currency),
				'prefix'   => $this->currency->getSymbolLeft($currency),
				'suffix'   => $this->currency->getSymbolRight($currency)
			];
		}

		return ['value' => (float)$value, 'decimals' => 0, 'prefix' => '', 'suffix' => ''];
	}

	private function delta($current, $previous): array {
		if ($previous <= 0) {
			return ['show' => false, 'class' => '', 'icon' => '', 'text' => ''];
		}

		$percent = (int)round(($current - $previous) / $previous * 100);

		return [
			'show'  => true,
			'class' => ($percent >= 0) ? 'is-up' : 'is-down',
			'icon'  => ($percent >= 0) ? 'fa-caret-up' : 'fa-caret-down',
			'text'  => abs($percent) . '%'
		];
	}

	private function row(array $row): array {
		return array_merge([
			'icon'    => 'fa-circle',
			'title'   => '',
			'href'    => '',
			'note'    => '',
			'counter' => null,
			'text'    => '',
			'status'  => '',
			'delta'   => null,
			'spark'   => [],
			'items'   => [],
			'donut'   => ''
		], $row);
	}

	private function spark(array $series, array $period): array {
		$values = [];

		for ($time = strtotime($period['start']); $time <= strtotime($period['end']); $time = strtotime('+1 day', $time)) {
			$values[] = (float)($series[date('Y-m-d', $time)] ?? 0);
		}

		return array_slice($values, -30);
	}

	private function build(array $builders): array {
		$rows = [];

		foreach ($builders as $route => $builder) {
			if (!$this->allowed($route)) {
				continue;
			}

			$started = microtime(true);

			try {
				$row = $builder();
			} catch (\Throwable $e) {
				$row = null;
			}

			$this->logSlow($route, $started);

			if ($row) {
				$rows[] = $this->row($row);
			}
		}

		return $rows;
	}

	/**
	 * KPI tiles
	 */
	private function kpiTiles(array $period): array {
		$now = $this->model_report_dashboard->getDailySales($period['start'], $period['end']);
		$before = $this->model_report_dashboard->getDailySales($period['prev_start'], $period['prev_end']);

		$revenue = array_sum($now['revenue']);
		$orders = array_sum($now['orders']);
		$profit = array_sum($now['profit']);

		$revenue_before = array_sum($before['revenue']);
		$orders_before = array_sum($before['orders']);
		$profit_before = array_sum($before['profit']);

		$average = $orders ? $revenue / $orders : 0;
		$average_before = $orders_before ? $revenue_before / $orders_before : 0;

		$tiles = [];

		$tiles[] = ['icon' => 'fa-line-chart', 'title' => $this->language->get('text_kpi_revenue'), 'href' => $this->link('report/sale_order', $period), 'counter' => $this->counter($revenue, true), 'delta' => $this->delta($revenue, $revenue_before), 'delay' => 0];
		$tiles[] = ['icon' => 'fa-bar-chart', 'title' => $this->language->get('text_kpi_profit'), 'href' => $this->allowed('report/sale_profit') ? $this->link('report/sale_profit', $period) : $this->link('report/sale_order', $period), 'counter' => $this->counter($profit, true), 'delta' => $this->delta($profit, $profit_before), 'note' => $revenue ? sprintf($this->language->get('text_margin'), number_format($profit / $revenue * 100, 1)) : '', 'delay' => 120];
		$tiles[] = ['icon' => 'fa-shopping-cart', 'title' => $this->language->get('text_kpi_orders'), 'href' => $this->link('report/sale_order', $period), 'counter' => $this->counter($orders), 'delta' => $this->delta($orders, $orders_before), 'delay' => 240];
		$tiles[] = ['icon' => 'fa-balance-scale', 'title' => $this->language->get('text_kpi_average'), 'href' => $this->link('report/sale_order', $period), 'counter' => $this->counter($average, true), 'delta' => $this->delta($average, $average_before), 'delay' => 360];

		foreach ($tiles as &$tile) {
			$tile['note'] = $tile['note'] ?? '';
			$tile['vs'] = $this->language->get('text_vs_previous');
		}

		unset($tile);

		return $tiles;
	}

	/**
	 * Sales
	 */
	private function salesRows(array $period): array {
		$model = $this->model_report_dashboard;

		$daily = $model->getDailySales($period['start'], $period['end']);
		$before = $model->getDailySales($period['prev_start'], $period['prev_end']);

		$revenue = array_sum($daily['revenue']);
		$orders = array_sum($daily['orders']);
		$profit = array_sum($daily['profit']);
		$margin = $revenue ? $profit / $revenue * 100 : 0;

		$builders = [];

		$builders['report/sale_order'] = fn() => [
			'icon'    => 'fa-shopping-basket',
			'title'   => $this->language->get('text_sale_order'),
			'href'    => $this->link('report/sale_order', $period),
			'counter' => $this->counter($orders),
			'note'    => sprintf($this->language->get('text_sale_order_note'), $this->num($orders), $this->fmt($revenue)),
			'delta'   => $this->delta($orders, array_sum($before['orders'])),
			'spark'   => $this->spark($daily['orders'], $period)
		];

		$builders['report/sale_profit'] = fn() => [
			'icon'    => 'fa-line-chart',
			'title'   => $this->language->get('text_sale_profit'),
			'href'    => $this->link('report/sale_profit', $period),
			'counter' => $this->counter($profit, true),
			'note'    => sprintf($this->language->get('text_margin'), number_format($margin, 1)),
			'status'  => ($profit < 0) ? 'bad' : (($margin < 10 && $revenue > 0) ? 'warn' : ''),
			'delta'   => $this->delta($profit, array_sum($before['profit'])),
			'spark'   => $this->spark($daily['profit'], $period)
		];

		$builders['report/sale_dropship_profit'] = function () use ($period) {
			$this->load->model('report/dropship');

			if (!$this->model_report_dropship->dsTablesExist()) {
				return null;
			}

			$data = [
				'filter_group'      => 'year',
				'filter_date_start' => $period['start'],
				'filter_date_end'   => $period['end']
			];

			$ds_revenue = 0.0;
			$ds_profit = 0.0;

			foreach ($this->model_report_dropship->getDropshipProfit($data) as $result) {
				$ds_revenue += $result['revenue'];
				$ds_profit += $result['gross_profit'];
			}

			$overheads = $this->model_report_dropship->getOverheadSummary($data);

			return [
				'icon'    => 'fa-cloud-download',
				'title'   => $this->language->get('text_sale_dropship'),
				'href'    => $this->link('report/sale_dropship_profit', $period),
				'counter' => $this->counter($ds_profit, true),
				'note'    => sprintf($this->language->get('text_sale_dropship_note'), number_format($ds_revenue ? $ds_profit / $ds_revenue * 100 : 0, 1), $this->fmt($overheads['total'] ?? 0)),
				'status'  => ($ds_profit < 0) ? 'bad' : ''
			];
		};

		$builders['report/sale_tax'] = fn() => [
			'icon'    => 'fa-briefcase',
			'title'   => $this->language->get('text_sale_tax'),
			'href'    => $this->link('report/sale_tax', $period),
			'counter' => $this->counter($model->getOrderTotalByCode('tax', $period['start'], $period['end']), true),
			'note'    => $this->language->get('text_sale_tax_note')
		];

		$builders['report/sale_shipping'] = fn() => [
			'icon'    => 'fa-truck',
			'title'   => $this->language->get('text_sale_shipping'),
			'href'    => $this->link('report/sale_shipping', $period),
			'counter' => $this->counter($model->getOrderTotalByCode('shipping', $period['start'], $period['end']), true),
			'note'    => $this->language->get('text_sale_shipping_note')
		];

		if (!$this->config->get('config_return_disable')) {
			$builders['report/sale_return'] = function () use ($model, $period) {
				$returns = $model->getReturnTotals($period['start'], $period['end']);

				return [
					'icon'    => 'fa-history',
					'title'   => $this->language->get('text_sale_return'),
					'href'    => $this->link('report/sale_return', $period),
					'counter' => $this->counter($returns['total']),
					'note'    => sprintf($this->language->get('text_sale_return_note'), $this->num($returns['pending'])),
					'status'  => $returns['pending'] ? 'warn' : ''
				];
			};
		}

		$builders['report/sale_coupon'] = function () use ($model, $period) {
			$coupons = $model->getCouponTotals($period['start'], $period['end']);

			return [
				'icon'    => 'fa-tags',
				'title'   => $this->language->get('text_sale_coupon'),
				'href'    => $this->link('report/sale_coupon', $period),
				'counter' => $this->counter($coupons['orders']),
				'note'    => sprintf($this->language->get('text_sale_coupon_note'), $this->num($coupons['orders']), $this->fmt($coupons['total']))
			];
		};

		$builders['report/sale_voucher'] = function () use ($model, $period) {
			$vouchers = $model->getVoucherTotals($period['start'], $period['end']);

			return [
				'icon'    => 'fa-ticket',
				'title'   => $this->language->get('text_sale_voucher'),
				'href'    => $this->link('report/sale_voucher', $period),
				'counter' => $this->counter($vouchers['amount'], true),
				'note'    => sprintf($this->language->get('text_sale_voucher_note'), $this->num($vouchers['count']), $this->fmt($vouchers['outstanding']))
			];
		};

		return $this->build($builders);
	}

	/**
	 * Products
	 */
	private function productRows(array $period): array {
		$model = $this->model_report_dashboard;

		$builders = [];

		$builders['report/product_viewed'] = function () {
			$this->load->model('report/product');

			$items = [];

			foreach ($this->model_report_product->getProductsViewed(['start' => 0, 'limit' => 3]) as $result) {
				$items[] = ['name' => $this->esc($result['name']), 'value' => $this->num($result['viewed'])];
			}

			return [
				'icon'    => 'fa-eye',
				'title'   => $this->language->get('text_product_viewed'),
				'href'    => $this->link('report/product_viewed'),
				'counter' => $this->counter($this->model_report_product->getTotalProductViews()),
				'note'    => $this->language->get('text_product_viewed_note') . ' (' . $this->language->get('text_all_time') . ')',
				'items'   => $items
			];
		};

		$builders['report/product_purchased'] = function () use ($model, $period) {
			$this->load->model('report/product');

			$items = [];

			$results = $this->model_report_product->getPurchased([
				'filter_date_start' => $period['start'],
				'filter_date_end'   => $period['end'],
				'start'             => 0,
				'limit'             => 3
			]);

			foreach ($results as $result) {
				$items[] = ['name' => $this->esc($result['name']), 'value' => $this->num($result['quantity'])];
			}

			return [
				'icon'    => 'fa-dollar',
				'title'   => $this->language->get('text_product_purchased'),
				'href'    => $this->link('report/product_purchased', $period),
				'counter' => $this->counter($model->getUnitsSold($period['start'], $period['end'])),
				'note'    => $this->language->get('text_product_purchased_note'),
				'items'   => $items
			];
		};

		$builders['report/product_quantity'] = function () {
			$this->load->model('catalog/product');

			$out = $this->model_catalog_product->getTotalProducts(['filter_quantity' => 0, 'filter_status' => 1]);
			$low = $this->model_catalog_product->getTotalLowStockProducts();

			return [
				'icon'    => 'fa-cubes',
				'title'   => $this->language->get('text_product_quantity'),
				'href'    => $this->url->link('report/product_quantity', 'token=' . $this->session->data['token'] . '&filter_quantity=0&filter_status=1', 'SSL'),
				'counter' => $this->counter($out),
				'note'    => $this->language->get('text_out_of_stock') . ', ' . sprintf($this->language->get('text_product_quantity_note'), $this->num($low)),
				'status'  => $out ? 'bad' : ($low ? 'warn' : '')
			];
		};

		$builders['report/product_markup'] = function () use ($model) {
			$margins = $model->getMarginSummary();

			return [
				'icon'    => 'fa-bar-chart',
				'title'   => $this->language->get('text_product_markup'),
				'href'    => $this->link('report/product_markup'),
				'counter' => ['value' => round($margins['average'], 1), 'decimals' => 1, 'prefix' => '', 'suffix' => '%'],
				'note'    => sprintf($this->language->get('text_product_markup_note'), $this->num($margins['below']), $this->num($margins['low'])),
				'status'  => $margins['below'] ? 'bad' : ($margins['low'] ? 'warn' : '')
			];
		};

		$builders['report/product_label'] = function () use ($model) {
			$labels = $model->getLabelSummary();

			return [
				'icon'    => 'fa-leaf',
				'title'   => $this->language->get('text_product_label'),
				'href'    => $this->link('report/product_label'),
				'counter' => $this->counter($labels['labelled']),
				'note'    => sprintf($this->language->get('text_product_label_note'), $this->num($labels['total']))
			];
		};

		return $this->build($builders);
	}

	/**
	 * Customers
	 */
	private function customerRows(array $period): array {
		$model = $this->model_report_dashboard;

		$builders = [];

		$builders['report/customer_order'] = function () use ($period) {
			$this->load->model('report/customer');

			$data = [
				'filter_date_start' => $period['start'],
				'filter_date_end'   => $period['end']
			];

			$items = [];

			foreach ($this->model_report_customer->getOrders($data + ['start' => 0, 'limit' => 3]) as $result) {
				$items[] = ['name' => $this->esc($result['customer']), 'value' => $this->fmt($result['total'])];
			}

			return [
				'icon'    => 'fa-shopping-bag',
				'title'   => $this->language->get('text_customer_order'),
				'href'    => $this->link('report/customer_order', $period),
				'counter' => $this->counter($this->model_report_customer->getTotalOrders($data)),
				'note'    => $this->language->get('text_customer_order_note'),
				'items'   => $items
			];
		};

		$builders['report/customer_wishlist'] = function () {
			$this->load->model('report/customer');

			$items = [];

			foreach ($this->model_report_customer->getWishlist(['start' => 0, 'limit' => 3]) as $result) {
				$items[] = ['name' => $this->esc($result['name']), 'value' => $this->num($result['total_wishlisted'])];
			}

			return [
				'icon'    => 'fa-heart',
				'title'   => $this->language->get('text_customer_wishlist'),
				'href'    => $this->link('report/customer_wishlist'),
				'counter' => $this->counter($this->model_report_customer->getTotalWishlist()),
				'note'    => $this->language->get('text_customer_wishlist_note'),
				'items'   => $items
			];
		};

		$builders['report/customer_reward'] = function () use ($model, $period) {
			$rewards = $model->getRewardSummary($period['start'], $period['end']);

			return [
				'icon'    => 'fa-gift',
				'title'   => $this->language->get('text_customer_reward'),
				'href'    => $this->link('report/customer_reward'),
				'counter' => $this->counter($rewards['outstanding']),
				'note'    => sprintf($this->language->get('text_customer_reward_note'), $this->num($rewards['earned']))
			];
		};

		$builders['report/customer_credit'] = fn() => [
			'icon'    => 'fa-money',
			'title'   => $this->language->get('text_customer_credit'),
			'href'    => $this->link('report/customer_credit'),
			'counter' => $this->counter($model->getCreditBalance(), true),
			'note'    => $this->language->get('text_customer_credit_note')
		];

		$builders['report/customer_country'] = function () use ($model, $period) {
			$segments = [];

			foreach ($model->getTopCountries($period['start'], $period['end']) as $result) {
				if (!$result['name']) {
					continue;
				}

				$segments[] = ['label' => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'), 'value' => round((float)$result['amount'], 2)];
			}

			return [
				'icon'    => 'fa-globe',
				'title'   => $this->language->get('text_customer_country'),
				'href'    => $this->link('report/customer_country', $period),
				'text'    => $segments ? '' : '-',
				'note'    => $this->language->get('text_customer_country_note'),
				'donut'   => $segments ? htmlspecialchars(json_encode($segments), ENT_QUOTES, 'UTF-8') : ''
			];
		};

		$builders['report/customer_deleted'] = fn() => [
			'icon'    => 'fa-eraser',
			'title'   => $this->language->get('text_customer_deleted'),
			'href'    => $this->link('report/customer_deleted'),
			'counter' => $this->counter($model->getTotalCustomersDeleted()),
			'note'    => $this->language->get('text_customer_deleted_note') . ' (' . $this->language->get('text_all_time') . ')'
		];

		if ($this->config->get('config_customer_online')) {
			$builders['report/customer_online'] = function () {
				$this->load->model('report/customer');

				return [
					'icon'    => 'fa-feed',
					'title'   => $this->language->get('text_customer_online'),
					'href'    => $this->link('report/customer_online'),
					'counter' => $this->counter($this->model_report_customer->getTotalCustomersOnline()),
					'note'    => $this->language->get('text_customer_online_note') . ' (' . $this->language->get('text_now') . ')'
				];
			};
		}

		return $this->build($builders);
	}

	/**
	 * Affiliates (the whole block is hidden when the feature is switched off in Settings)
	 */
	private function affiliateRows(array $period): array {
		if ($this->config->get('config_affiliate_disable')) {
			return [];
		}

		$model = $this->model_report_dashboard;

		$builders = [];

		$builders['report/affiliate_activity'] = fn() => [
			'icon'    => 'fa-spinner',
			'title'   => $this->language->get('text_affiliate_activity'),
			'href'    => $this->link('report/affiliate_activity', $period),
			'counter' => $this->counter($model->getAffiliateActivityTotal($period['start'], $period['end'])),
			'note'    => $this->language->get('text_affiliate_activity_note')
		];

		$builders['report/affiliate_commission'] = function () use ($model, $period) {
			$commission = $model->getAffiliateCommission($period['start'], $period['end']);

			$items = [];

			foreach ($commission['top'] as $result) {
				$items[] = ['name' => $this->esc($result['name']), 'value' => $this->fmt($result['amount'])];
			}

			return [
				'icon'    => 'fa-percent',
				'title'   => $this->language->get('text_affiliate_commission'),
				'href'    => $this->link('report/affiliate_commission', $period),
				'counter' => $this->counter($commission['total'], true),
				'note'    => $this->language->get('text_affiliate_commission_note'),
				'items'   => $items
			];
		};

		return $this->build($builders);
	}

	/**
	 * Other (abandoned carts, banners, blog, robots)
	 */
	private function otherRows(array $period): array {
		$model = $this->model_report_dashboard;

		$builders = [];

		$builders['report/abandoned_carts'] = function () use ($model) {
			$abandoned = $model->getAbandonedSummary();

			return [
				'icon'    => 'fa-paper-plane',
				'title'   => $this->language->get('text_abandoned'),
				'href'    => $this->link('report/abandoned_carts'),
				'counter' => $this->counter($abandoned['total']),
				'note'    => sprintf($this->language->get('text_abandoned_note'), $this->fmt($abandoned['amount'])),
				'status'  => $abandoned['total'] ? 'warn' : ''
			];
		};

		$builders['report/banner_clicked'] = function () use ($model) {
			$items = [];
			$total = 0;

			foreach ($model->getTopBanners() as $result) {
				$items[] = ['name' => $this->esc($result['title']), 'value' => $this->num($result['clicked'])];
			}

			$this->load->model('design/banner');

			$total = (int)$this->model_design_banner->getTotalImagesClicks();

			return [
				'icon'    => 'fa-photo',
				'title'   => $this->language->get('text_banner'),
				'href'    => $this->link('report/banner_clicked'),
				'counter' => $this->counter($total),
				'note'    => $this->language->get('text_banner_note') . ' (' . $this->language->get('text_all_time') . ')',
				'items'   => $items
			];
		};

		$this->load->model('blog/status');

		if ($this->model_blog_status->checkBlog()) {
			$builders['blog/report'] = function () use ($period) {
				$this->load->model('blog/report');

				$data = [
					'filter_date_start' => $period['start'],
					'filter_date_end'   => $period['end']
				];

				$items = [];

				foreach ($this->model_blog_report->getBlogViewed($data + ['start' => 0, 'limit' => 3]) as $result) {
					$items[] = ['name' => $this->esc($result['article_title']), 'value' => $this->num($result['view'])];
				}

				return [
					'icon'    => 'fa-eye',
					'title'   => $this->language->get('text_blog'),
					'href'    => $this->link('blog/report', $period),
					'counter' => $this->counter($this->model_blog_report->getTotalBlogViews($data)),
					'note'    => $this->language->get('text_blog_note'),
					'items'   => $items
				];
			};
		}

		if ($this->config->get('config_robots_online')) {
			$builders['report/robot_online'] = function () {
				$this->load->model('report/online');

				return [
					'icon'    => 'fa-wifi',
					'title'   => $this->language->get('text_robot'),
					'href'    => $this->link('report/robot_online'),
					'counter' => $this->counter($this->model_report_online->getTotalRobotsOnline()),
					'note'    => $this->language->get('text_robot_note') . ' (' . $this->language->get('text_now') . ')'
				];
			};
		}

		return $this->build($builders);
	}

	/**
	 * Needs attention: things that are worth acting on right now, whatever the chosen period
	 */
	private function attentionAlerts(): array {
		$model = $this->model_report_dashboard;

		$alerts = [];

		if ($this->allowed('report/product_quantity')) {
			$this->load->model('catalog/product');

			$out = $this->model_catalog_product->getTotalProducts(['filter_quantity' => 0, 'filter_status' => 1]);
			$low = $this->model_catalog_product->getTotalLowStockProducts();

			if ($out) {
				$alerts[] = ['status' => 'bad', 'icon' => 'fa-cubes', 'text' => $this->language->get('text_alert_outstock'), 'count' => $this->num($out), 'href' => $this->url->link('report/product_quantity', 'token=' . $this->session->data['token'] . '&filter_quantity=0&filter_status=1', 'SSL')];
			}

			if ($low) {
				$alerts[] = ['status' => 'warn', 'icon' => 'fa-battery-quarter', 'text' => $this->language->get('text_alert_lowstock'), 'count' => $this->num($low), 'href' => $this->link('report/product_quantity')];
			}
		}

		if ($this->allowed('report/product_markup') || $this->allowed('modification/ds_margin_checker')) {
			$margins = $model->getMarginSummary();

			if ($margins['standard_below'] && $this->allowed('report/product_markup')) {
				$alerts[] = ['status' => 'bad', 'icon' => 'fa-bar-chart', 'text' => $this->language->get('text_alert_below_cost'), 'count' => $this->num($margins['standard_below']), 'href' => $this->link('report/product_markup')];
			}

			if ($margins['dropship'] && $this->allowed('modification/ds_margin_checker')) {
				$margin_checker = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=negative', 'SSL');

				if ($margins['dropship_below']) {
					$alerts[] = ['status' => 'bad', 'icon' => 'fa-bar-chart', 'text' => $this->language->get('text_alert_ds_below_cost'), 'count' => $this->num($margins['dropship_below']), 'href' => $margin_checker];
				}

				if ($margins['dropship_no_rate']) {
					$alerts[] = ['status' => 'warn', 'icon' => 'fa-usd', 'text' => $this->language->get('text_alert_ds_no_rate'), 'count' => $this->num($margins['dropship_no_rate']), 'href' => $margin_checker];
				}
			}
		}

		if ($this->allowed('report/abandoned_carts')) {
			$abandoned = $model->getAbandonedSummary();

			if ($abandoned['total']) {
				$alerts[] = ['status' => 'warn', 'icon' => 'fa-paper-plane', 'text' => $this->language->get('text_alert_abandoned'), 'count' => $this->num($abandoned['total']), 'href' => $this->link('report/abandoned_carts')];
			}
		}

		if ($this->allowed('report/sale_return') && !$this->config->get('config_return_disable')) {
			$pending = $model->getTotalPendingReturns();

			if ($pending) {
				$alerts[] = ['status' => 'warn', 'icon' => 'fa-history', 'text' => $this->language->get('text_alert_returns'), 'count' => $this->num($pending), 'href' => $this->link('report/sale_return')];
			}
		}

		return $alerts;
	}
}
