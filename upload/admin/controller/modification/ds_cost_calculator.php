<?php
/**
 * Class ControllerModificationDsCostCalculator
 *
 * Admin controller for the DS Cost Calculator modification.
 * Manages global and per-channel cost configuration used by the
 * Dropshipping Profit Report to calculate accurate net profit.
 *
 * Location: admin/controller/modification/ds_cost_calculator.php
 *
 * @package NivoCart
 */
class ControllerModificationDsCostCalculator extends Controller {
    private array $error = [];
    private string $name = 'ds_cost_calculator';

    // =====================================================================
    // INDEX — DB check gate
    // =====================================================================

    public function index(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/ds_cost_calculator');

        if (!$this->model_modification_ds_cost_calculator->tableExists()) {
            $this->document->setTitle($this->language->get('heading_title'));

            $this->data['heading_title'] = $this->language->get('heading_title');

            $this->data['text_install_message'] = $this->language->get('text_install_message');
            $this->data['text_install_btn'] = $this->language->get('text_install_btn');

            $this->data['install_url'] = $this->url->link('modification/ds_cost_calculator/install', 'token=' . $this->session->data['token'], 'SSL');

            $this->data['breadcrumbs'] = [
                [
                    'text'      => $this->language->get('text_home'),
                    'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                    'separator' => false
                ],
                [
                    'text'      => $this->language->get('heading_title'),
                    'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
                    'separator' => ' :: '
                ]
            ];

            $this->template = 'modification/ds_cost_calculator_install.tpl';
            $this->children = ['common/header', 'common/footer'];

            $this->response->setOutput($this->render());
            return;
        }

        $this->dashboard();
    }

    // =====================================================================
    // DASHBOARD
    // =====================================================================

    public function dashboard(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/ds_cost_calculator');

        $this->document->setTitle($this->language->get('heading_title'));

        // Flash messages
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $this->data['error'] = '';
        }

        // Global config (channel_id = 0)
        $global = $this->model_modification_ds_cost_calculator->getConfig(0);

        $this->data['global'] = $global;

        // Active DS channels with their configs
        $channels = $this->model_modification_ds_cost_calculator->getChannels();

        $channel_data = [];

        foreach ($channels as $ch) {
            $channel_data[] = [
                'channel_id' => (int)$ch['channel_id'],
                'name'       => $ch['name'],
                'provider'   => $ch['provider'],
                'config'     => $this->model_modification_ds_cost_calculator->getConfig((int)$ch['channel_id']),
            ];
        }

        $this->data['channels'] = $channel_data;
        $this->data['has_channels'] = !empty($channel_data);

        // Language strings
        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_global_costs'] = $this->language->get('text_global_costs');
        $this->data['text_global_costs_desc'] = $this->language->get('text_global_costs_desc');
        $this->data['text_channel_costs'] = $this->language->get('text_channel_costs');
        $this->data['text_channel_costs_desc'] = $this->language->get('text_channel_costs_desc');
        $this->data['text_no_channels'] = $this->language->get('text_no_channels');
        $this->data['text_loading'] = $this->language->get('text_loading');
        $this->data['text_vat_yes'] = $this->language->get('text_vat_yes');
        $this->data['text_vat_no'] = $this->language->get('text_vat_no');

        $this->data['tab_global'] = $this->language->get('tab_global');
        $this->data['tab_channels'] = $this->language->get('tab_channels');
        $this->data['tab_help'] = $this->language->get('tab_help');

        $this->data['help_intro'] = $this->language->get('help_intro');
        $this->data['help_global'] = $this->language->get('help_global');
        $this->data['help_channels'] = $this->language->get('help_channels');
        $this->data['help_vat'] = $this->language->get('help_vat');
        $this->data['help_proration'] = $this->language->get('help_proration');

        $this->data['entry_hosting_monthly'] = $this->language->get('entry_hosting_monthly');
        $this->data['entry_domain_annual'] = $this->language->get('entry_domain_annual');
        $this->data['entry_tools_annual'] = $this->language->get('entry_tools_annual');
        $this->data['entry_tools_annual_help'] = $this->language->get('entry_tools_annual_help');
        $this->data['entry_chargeback_pct'] = $this->language->get('entry_chargeback_pct');
        $this->data['entry_chargeback_help'] = $this->language->get('entry_chargeback_help');
        $this->data['entry_vat_registered'] = $this->language->get('entry_vat_registered');
        $this->data['entry_other_monthly'] = $this->language->get('entry_other_monthly');
        $this->data['entry_other_description'] = $this->language->get('entry_other_description');
        $this->data['entry_platform_monthly'] = $this->language->get('entry_platform_monthly');
        $this->data['entry_advertising_monthly'] = $this->language->get('entry_advertising_monthly');
        $this->data['entry_gateway_fee_pct'] = $this->language->get('entry_gateway_fee_pct');
        $this->data['entry_gateway_fee_fixed'] = $this->language->get('entry_gateway_fee_fixed');
        $this->data['entry_fx_fee_pct'] = $this->language->get('entry_fx_fee_pct');
        $this->data['entry_fx_fee_help'] = $this->language->get('entry_fx_fee_help');
        $this->data['entry_returns_pct'] = $this->language->get('entry_returns_pct');
        $this->data['entry_returns_help'] = $this->language->get('entry_returns_help');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_close'] = $this->language->get('button_close');

        // Action URLs
        $this->data['save_global_url'] = $this->url->link('modification/ds_cost_calculator/saveGlobal',  'token=' . $this->session->data['token'], 'SSL');
        $this->data['save_channel_url'] = $this->url->link('modification/ds_cost_calculator/saveChannel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['close_url'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['breadcrumbs'] = [
            [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            ],
            [
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            ]
        ];

        $this->template = 'modification/ds_cost_calculator_dashboard.tpl';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    // =====================================================================
    // SAVE GLOBAL COSTS
    // =====================================================================

    /**
     * Save the global cost row (channel_id = 0).
     * Expects POST. Redirects to dashboard on completion.
     */
    public function saveGlobal(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/ds_cost_calculator');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePermission()) {
            try {
                $this->model_modification_ds_cost_calculator->saveConfig(0, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_saved');
            } catch (\Exception $e) {
                $this->session->data['error'] = $e->getMessage();
            }
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    // =====================================================================
    // SAVE CHANNEL COSTS
    // =====================================================================

    /**
     * Save the cost row for a specific channel.
     * Expects POST with channel_id. Redirects to dashboard on completion.
     */
    public function saveChannel(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/ds_cost_calculator');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePermission()) {
            $channel_id = (int)($this->request->post['channel_id'] ?? 0);

            if ($channel_id < 1) {
                $this->session->data['error'] = 'Invalid channel ID.';
                $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
                return;
            }

            try {
                $this->model_modification_ds_cost_calculator->saveConfig($channel_id, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_saved');
            } catch (\Exception $e) {
                $this->session->data['error'] = $e->getMessage();
            }
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    // =====================================================================
    // INSTALL / UNINSTALL
    // =====================================================================

    public function install(): void {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ds_cost_config` (
            `cost_config_id` int NOT NULL AUTO_INCREMENT,
            `channel_id` int NOT NULL DEFAULT '0' COMMENT '0 = global costs row',
            `hosting_monthly` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Web hosting per month',
            `domain_annual` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Domain renewal per year',
            `tools_annual` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Accounting, email tools, etc. per year',
            `platform_monthly` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'DS platform subscription per month',
            `advertising_monthly` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'PPC / marketing spend per month',
            `gateway_fee_pct` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Payment gateway percentage fee e.g. 2.90',
            `gateway_fee_fixed` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Payment gateway fixed fee per transaction e.g. 0.30',
            `fx_fee_pct` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Currency conversion fee % on supplier cost',
            `returns_pct` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Expected returns as % of revenue',
            `chargeback_pct` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Chargeback provision % of revenue (global row)',
            `vat_registered` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = VAT registered; supplier cost is ex-VAT',
            `other_monthly` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Miscellaneous monthly overhead',
            `other_description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Label for other costs',
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`cost_config_id`),
            UNIQUE KEY `uniq_channel` (`channel_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='DS Cost Calculator — cost configuration per channel (0=global)'");

        // Seed the global row so the form is never empty on first visit
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "ds_cost_config` (`channel_id`, `date_modified`) VALUES (0, NOW())");

        // If called from the install notification page, redirect to dashboard
        if (isset($this->request->get['route'])) {
            $this->redirect($this->url->link('modification/ds_cost_calculator', 'token=' . $this->session->data['token'], 'SSL'));
        }
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ds_cost_config`");
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    protected function validatePermission(): bool {
        if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        return true;
    }
}
