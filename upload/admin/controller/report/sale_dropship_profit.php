<?php
/**
 * Class ControllerReportSaleDropshipProfit
 *
 * Admin controller for the Dropshipping Profit Report.
 * Mirrors the style and filter pattern of sale_order / sale_profit.
 * Depends on: nc_dropship_order, nc_dropship_channel, nc_product_dropship.
 * Optionally uses: nc_ds_cost_config (DS Cost Calculator modification).
 *
 * Location: admin/controller/report/sale_dropship_profit.php
 *
 * @package NivoCart
 */
class ControllerReportSaleDropshipProfit extends Controller {
	/* Error Array Placeholder */

    public function index(): void {
        $this->language->load('report/sale_dropship_profit');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('report/dropship');

        // ---------------------------------------------------------------
        // Dependency check — show friendly message if DS not installed
        // ---------------------------------------------------------------
        if (!$this->model_report_dropship->dsTablesExist()) {
            $this->data['heading_title'] = $this->language->get('heading_title');

            $this->data['text_no_ds_tables'] = $this->language->get('text_no_ds_tables');

            $this->data['button_close'] = $this->language->get('button_close');

            $this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

            $this->data['token'] = $this->session->data['token'];

            $this->data['breadcrumbs'] = $this->buildBreadcrumbs('');

            $this->template = 'report/sale_dropship_profit.tpl';
            $this->children = ['common/header', 'common/footer'];

            $this->response->setOutput($this->render());
            return;
        }

        // ---------------------------------------------------------------
        // Filters
        // ---------------------------------------------------------------
        // Default: current week (Monday → today)
        $filter_date_start = $this->request->get['filter_date_start'] ?? date('Y-m-d', strtotime('monday this week'));
        $filter_date_end = $this->request->get['filter_date_end'] ?? date('Y-m-d');
        $filter_group = $this->request->get['filter_group'] ?? 'week';
        $filter_order_status_id = (int)($this->request->get['filter_order_status_id'] ?? 0);
        $filter_channel_id = (int)($this->request->get['filter_channel_id'] ?? 0);

        $page = max(1, (int)($this->request->get['page'] ?? 1));

        // Build URL fragment for pagination links
        $url = '';
        $url .= '&filter_date_start=' . urlencode($filter_date_start);
        $url .= '&filter_date_end=' . urlencode($filter_date_end);
        $url .= '&filter_group=' . urlencode($filter_group);
        $url .= '&filter_order_status_id=' . $filter_order_status_id;
        $url .= '&filter_channel_id=' . $filter_channel_id;

        // ---------------------------------------------------------------
        // VAT registration flag (from cost config)
        // ---------------------------------------------------------------
        $vat_registered = $this->model_report_dropship->isVatRegistered();

        // ---------------------------------------------------------------
        // Query
        // ---------------------------------------------------------------
        $query_data = [
            'filter_date_start'       => $filter_date_start,
            'filter_date_end'         => $filter_date_end,
            'filter_group'            => $filter_group,
            'filter_order_status_id'  => $filter_order_status_id,
            'filter_channel_id'       => $filter_channel_id,
            'vat_registered'          => $vat_registered,
            'start'                   => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit'                   => $this->config->get('config_admin_limit'),
        ];

        $total_rows = $this->model_report_dropship->getTotalDropshipProfit($query_data);
        $results = $this->model_report_dropship->getDropshipProfit($query_data);
        $overhead = $this->model_report_dropship->getOverheadSummary($query_data);

        // ---------------------------------------------------------------
        // Build display rows + running totals
        // ---------------------------------------------------------------
        $rows = [];
        $total_revenue = 0.0;
        $total_prod_cost = 0.0;
        $total_gw_fees = 0.0;
        $total_returns = 0.0;
        $total_gross = 0.0;

        $currency = $this->config->get('config_currency');

        foreach ($results as $r) {
            $rows[] = [
                'date_start'        => date($this->language->get('date_format_short'), strtotime($r['date_start'])),
                'date_end'          => date($this->language->get('date_format_short'), strtotime($r['date_end'])),
                'channel_name'      => $r['channel_name'],
                'orders'            => $r['orders'],
                'revenue'           => $this->currency->format($r['revenue'], $currency),
                'product_cost'      => $this->currency->format($r['product_cost'], $currency),
                'gateway_fees'      => $this->currency->format($r['gateway_fees'], $currency),
                'returns_provision' => $this->currency->format($r['returns_provision'], $currency),
                'gross_profit'      => $this->currency->format($r['gross_profit'], $currency),
                'gross_margin_pct'  => number_format($r['gross_margin_pct'], 2) . '%',
                'gross_profit_raw'  => $r['gross_profit'],
            ];

            $total_revenue += $r['revenue'];
            $total_prod_cost += $r['product_cost'];
            $total_gw_fees += $r['gateway_fees'];
            $total_returns += $r['returns_provision'];
            $total_gross += $r['gross_profit'];
        }

        // Net profit = gross profit total − all overhead
        $net_profit = $total_gross - $overhead['total'];
        $net_margin_pct = ($total_revenue > 0) ? ($net_profit / $total_revenue) * 100 : 0;

        // ---------------------------------------------------------------
        // Template data
        // ---------------------------------------------------------------
        $this->data['rows'] = $rows;
        $this->data['has_results'] = !empty($rows);

        // Totals
        $this->data['total_revenue'] = $this->currency->format($total_revenue, $currency);
        $this->data['total_product_cost'] = $this->currency->format($total_prod_cost, $currency);
        $this->data['total_gateway_fees'] = $this->currency->format($total_gw_fees, $currency);
        $this->data['total_returns'] = $this->currency->format($total_returns, $currency);
        $this->data['total_gross_profit'] = $this->currency->format($total_gross, $currency);
        $this->data['total_gross_margin'] = ($total_revenue > 0) ? number_format(($total_gross / $total_revenue) * 100, 2) . '%' : '0.00%';

        // Overhead
        $this->data['overhead'] = [
            'hosting'     => $this->currency->format($overhead['hosting'], $currency),
            'domain'      => $this->currency->format($overhead['domain'], $currency),
            'tools'       => $this->currency->format($overhead['tools'], $currency),
            'platform'    => $this->currency->format($overhead['platform'], $currency),
            'advertising' => $this->currency->format($overhead['advertising'], $currency),
            'chargeback'  => $this->currency->format($overhead['chargeback'], $currency),
            'other'       => $this->currency->format($overhead['other'], $currency),
            'total'       => $this->currency->format($overhead['total'], $currency),
        ];

        // Net
        $this->data['net_profit'] = $this->currency->format($net_profit, $currency);
        $this->data['net_margin_pct'] = number_format($net_margin_pct, 2) . '%';
        $this->data['net_profit_raw'] = $net_profit;

        // Cost config presence
        $has_cost_config = $this->model_report_dropship->costConfigExists();

        $this->data['has_cost_config'] = $has_cost_config;

        if (!$has_cost_config) {
            $config_url = $this->url->link('modification/ds_cost_calculator', 'token=' . $this->session->data['token'], 'SSL');

            $this->data['text_no_cost_config'] = sprintf($this->language->get('text_no_cost_config'), $config_url);
        }

        // Channels for filter
        $this->data['channels'] = $this->model_report_dropship->getChannelsForFilter();

        // Order statuses for filter
        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

        // Group options (matching sale_order pattern)
        $this->data['groups'] = [
            ['text' => $this->language->get('text_day'), 'value' => 'day'],
            ['text' => $this->language->get('text_week'), 'value' => 'week'],
            ['text' => $this->language->get('text_month'), 'value' => 'month'],
            ['text' => $this->language->get('text_year'), 'value' => 'year'],
        ];

        // Current filter values (for form re-population)
        $this->data['filter_date_start'] = $filter_date_start;
        $this->data['filter_date_end'] = $filter_date_end;
        $this->data['filter_group'] = $filter_group;
        $this->data['filter_order_status_id'] = $filter_order_status_id;
        $this->data['filter_channel_id'] = $filter_channel_id;

        // Pagination
        $this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
        $this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

        $pagination = new Pagination();
        $pagination->total = $total_rows;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('report/sale_dropship_profit', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        // Language strings — heading + columns
        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_all_status'] = $this->language->get('text_all_status');
        $this->data['text_all_channels'] = $this->language->get('text_all_channels');
        $this->data['text_total'] = $this->language->get('text_total');
        $this->data['text_overhead_summary'] = $this->language->get('text_overhead_summary');
        $this->data['text_hosting_share'] = $this->language->get('text_hosting_share');
        $this->data['text_domain_share'] = $this->language->get('text_domain_share');
        $this->data['text_tools_share'] = $this->language->get('text_tools_share');
        $this->data['text_platform_share'] = $this->language->get('text_platform_share');
        $this->data['text_advertising_share'] = $this->language->get('text_advertising_share');
        $this->data['text_chargeback_share'] = $this->language->get('text_chargeback_share');
        $this->data['text_other_share'] = $this->language->get('text_other_share');
        $this->data['text_total_overhead'] = $this->language->get('text_total_overhead');
        $this->data['text_net_profit'] = $this->language->get('text_net_profit');
        $this->data['text_net_margin'] = $this->language->get('text_net_margin');

        $this->data['column_date_start'] = $this->language->get('column_date_start');
        $this->data['column_date_end'] = $this->language->get('column_date_end');
        $this->data['column_channel'] = $this->language->get('column_channel');
        $this->data['column_orders'] = $this->language->get('column_orders');
        $this->data['column_revenue'] = $this->language->get('column_revenue');
        $this->data['column_product_cost'] = $this->language->get('column_product_cost');
        $this->data['column_gateway_fees'] = $this->language->get('column_gateway_fees');
        $this->data['column_returns_provision'] = $this->language->get('column_returns_provision');
        $this->data['column_gross_profit'] = $this->language->get('column_gross_profit');
        $this->data['column_gross_margin'] = $this->language->get('column_gross_margin');

        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_group'] = $this->language->get('entry_group');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_channel'] = $this->language->get('entry_channel');

        $this->data['button_close'] = $this->language->get('button_close');
        $this->data['button_filter'] = $this->language->get('button_filter');

        $this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['token'] = $this->session->data['token'];

        $this->data['breadcrumbs'] = $this->buildBreadcrumbs($url);

        $this->template = 'report/sale_dropship_profit.tpl';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    private function buildBreadcrumbs(string $url): array {
        return [
            [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            ],
            [
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('report/sale_dropship_profit', 'token=' . $this->session->data['token'] . $url, 'SSL'),
                'separator' => ' :: '
            ],
        ];
    }
}
