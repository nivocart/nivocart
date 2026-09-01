<?php
/**
 * Class ControllerModificationHmrcMtd
 *
 * Admin controller for the HMRC Making Tax Digital modification.
 * Provides tabbed settings (Core / VAT MTD), OAuth 2.0 connect/disconnect,
 * VAT obligation fetching, and VAT return preparation & submission.
 *
 * @package NivoCart
 */
class ControllerModificationHmrcMtd extends Controller {
    private array $error = [];
    private string $name = 'hmrc_mtd';

    // -----------------------------------------------------------------------
    // Public actions
    // -----------------------------------------------------------------------

    /**
     * Main entry point — bootstraps tables, renders the tabbed settings page.
     */
    public function index(): void {
        $this->language->load('modification/' . $this->name);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('modification/' . $this->name);

        $this->model_modification_hmrc_mtd->checkHmrcMtd();

        $this->getSettings();
    }

    /**
     * Save Core + VAT settings from form POST.
     */
    public function save(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateSettings()) {
            $store_id = (int)($this->request->post['store_id'] ?? 0);

            $this->model_modification_hmrc_mtd->saveSettings($store_id, [
                'client_id'        => trim($this->request->post['client_id'] ?? ''),
                'client_secret'    => trim($this->request->post['client_secret'] ?? ''),
                'sandbox'          => (int)!empty($this->request->post['sandbox']),
                'vat_enabled'      => (int)!empty($this->request->post['vat_enabled']),
                'vrn'              => trim($this->request->post['vrn'] ?? ''),
                'itsa_enabled'     => (int)!empty($this->request->post['itsa_enabled']),
                'nino'             => strtoupper(trim($this->request->post['nino'] ?? '')),
                'itsa_business_id' => trim($this->request->post['itsa_business_id'] ?? ''),
            ]);

            $this->session->data['success'] = $this->language->get('text_success_save');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Initiate OAuth 2.0 — generate state, store it, redirect to HMRC auth page.
     */
    public function connect(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        if (empty($settings['client_id']) || empty($settings['client_secret'])) {
            $this->session->data['error'] = $this->language->get('error_credentials');
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        // Generate and store a state nonce (CSRF protection)
        $state = bin2hex(random_bytes(16));

        $this->model_modification_hmrc_mtd->saveSetting($store_id, 'oauth_state', $state);

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = new HmrcMtd($settings['client_id'], $settings['client_secret'], (bool)(int)($settings['sandbox'] ?? 1));

        // Build OAuth scope from enabled components
        $scopes = [];

        if (!empty($settings['vat_enabled'])) {
			$scopes[] = 'write:vat read:vat';
		}

        if (!empty($settings['itsa_enabled'])) {
			$scopes[] = 'write:self-assessment read:self-assessment';
		}

        if (!$scopes) { $scopes[] = 'write:vat read:vat'; } // default fallback

        $scope = implode(' ', $scopes);

        $redirect_uri = $this->getRedirectUri();

        $this->redirect($hmrc->getAuthorisationUrl($scope, $state, $redirect_uri));
    }

    /**
     * Clear stored OAuth tokens — disconnect from HMRC.
     */
    public function disconnect(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        if ($this->validatePermission()) {
            $this->model_modification_hmrc_mtd->deleteTokens(0);

            $this->session->data['success'] = $this->language->get('text_success_disconnect');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Fetch VAT obligations from HMRC API and update local records.
     */
    public function obligations(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;

        if (!$this->validatePermission()) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $vrn = $settings['vrn'] ?? '';

        if (!preg_match('/^\d{9}$/', $vrn)) {
            $this->session->data['error'] = $this->language->get('error_vrn_required');
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        // Fetch obligations for current + next year window
        $from = date('Y-m-d', strtotime('-1 year'));
        $to = date('Y-m-d', strtotime('+3 months'));

        $response = $hmrc->get('/organisations/vat/' . rawurlencode($vrn) . '/obligations', $tokens['access_token'], $this->user->getUserName(), ['from' => $from, 'to' => $to]);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
        } elseif (!empty($response['obligations'])) {
            $this->model_modification_hmrc_mtd->saveObligations($store_id, $response['obligations']);
            $this->session->data['success'] = $this->language->get('text_success_obligations');
        } else {
            $this->session->data['success'] = $this->language->get('text_success_obligations');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Show the VAT return preparation form for a given period.
     */
    public function prepare(): void {
        $this->language->load('modification/' . $this->name);

        $this->document->setTitle($this->language->get('heading_prepare'));

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $period_key = $this->request->get['period_key'] ?? '';

        $obligation = $this->model_modification_hmrc_mtd->getObligation($store_id, $period_key);

        if (!$obligation) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        // Use existing draft if present, otherwise calculate from orders
        $draft = $this->model_modification_hmrc_mtd->getVatReturn($store_id, $period_key);

        if ($draft) {
            $figures = $draft;
        } else {
            $figures = $this->model_modification_hmrc_mtd->calculateVatFigures($store_id, $obligation['start'], $obligation['end']);
        }

        $this->getVatPrepare($obligation, $figures);
    }

    /**
     * Submit a VAT return to HMRC.
     */
    public function submit(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $period_key = $this->request->post['period_key'] ?? '';

        if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->validateSubmit()) {
            if ($period_key) {
                $this->redirect($this->url->link('modification/' . $this->name . '/prepare', 'token=' . $this->session->data['token'] . '&period_key=' . urlencode($period_key), 'SSL'));
            } else {
                $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            }

            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $vrn = $settings['vrn'] ?? '';

        $data = [
            'vat_due_sales'         => (float)$this->request->post['vat_due_sales'],
            'vat_due_acquisitions'  => (float)$this->request->post['vat_due_acquisitions'],
            'total_vat_due'         => (float)$this->request->post['total_vat_due'],
            'vat_reclaimed'         => (float)$this->request->post['vat_reclaimed'],
            'net_vat_due'           => (float)$this->request->post['net_vat_due'],
            'total_value_sales'     => (float)$this->request->post['total_value_sales'],
            'total_value_purchases' => (float)$this->request->post['total_value_purchases'],
            'total_goods_supplied'  => (float)$this->request->post['total_goods_supplied'],
            'total_acquisitions'    => (float)$this->request->post['total_acquisitions'],
            'finalised'             => 1,
        ];

        // Save draft before submitting (so we have a record even if HMRC call fails)
        $this->model_modification_hmrc_mtd->saveVatReturn($store_id, $period_key, $data);

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        $payload = [
            'periodKey'                    => $period_key,
            'vatDueSales'                  => $data['vat_due_sales'],
            'vatDueAcquisitions'           => $data['vat_due_acquisitions'],
            'totalVatDue'                  => $data['total_vat_due'],
            'vatReclaimedCurrPeriod'       => $data['vat_reclaimed'],
            'netVatDue'                    => abs($data['net_vat_due']),
            'totalValueSalesExVAT'         => (int)$data['total_value_sales'],
            'totalValuePurchasesExVAT'     => (int)$data['total_value_purchases'],
            'totalValueGoodsSuppliedExVAT' => (int)$data['total_goods_supplied'],
            'totalAcquisitionsExVAT'       => (int)$data['total_acquisitions'],
            'finalised'                    => true,
        ];

        $response = $hmrc->post('/organisations/vat/' . rawurlencode($vrn) . '/returns', $tokens['access_token'], $this->user->getUserName(), $payload);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
            $this->redirect($this->url->link('modification/' . $this->name . '/prepare', 'token=' . $this->session->data['token'] . '&period_key=' . urlencode($period_key), 'SSL'));
            return;
        }

        $this->model_modification_hmrc_mtd->markVatReturnSubmitted($store_id, $period_key, json_encode($response));

        $this->session->data['success'] = $this->language->get('text_success_submit');

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Fetch ITSA quarterly periods and business details from HMRC, store locally.
     * Also discovers the selfEmploymentId if not yet stored in settings.
     */
    public function itsaPeriods(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;

        if (!$this->validatePermission()) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $nino = strtoupper(trim($settings['nino'] ?? ''));

        if (!preg_match('/^[A-Z]{2}\d{6}[A-D]$/i', $nino)) {
            $this->session->data['error'] = $this->language->get('error_nino_required');
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        $username = $this->user->getUserName();

        // Step 1 — discover business ID if not already stored
        $business_id = $settings['itsa_business_id'] ?? '';

        if (!$business_id) {
            $biz_response = $hmrc->get('/individuals/business/details/' . rawurlencode($nino) . '/list', $tokens['access_token'], $username);

            if (isset($biz_response['error'])) {
                $this->session->data['error'] = sprintf($this->language->get('error_api'), $biz_response['error']);
                $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
                return;
            }

            // Use the first self-employment business found
            foreach ($biz_response['businessDetails'] ?? [] as $biz) {
                if (($biz['typeOfBusiness'] ?? '') === 'self-employment') {
                    $business_id = $biz['businessId'] ?? '';
                    break;
                }
            }

            if ($business_id) {
                $this->model_modification_hmrc_mtd->saveSetting($store_id, 'itsa_business_id', $business_id);
            } else {
                $this->session->data['error'] = $this->language->get('error_business_id');
                $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
                return;
            }
        }

        // Step 2 — fetch quarterly obligations for the current tax year window
        $from = date('Y-m-d', strtotime('-18 months'));
        $to = date('Y-m-d', strtotime('+6 months'));

        $response = $hmrc->get('/obligations/details/' . rawurlencode($nino) . '/income-and-expenditure', $tokens['access_token'], $username, ['from' => $from, 'to' => $to, 'status' => 'Open']);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        // Parse HMRC obligation response into normalised period rows
        $periods = [];

        foreach ($response['obligations'] ?? [] as $obligation) {
            $ref = $obligation['identification']['referenceNumber'] ?? $business_id;

            foreach ($obligation['obligationDetails'] ?? [] as $detail) {
                $period_start = $detail['inboundCorrespondenceFromDate'] ?? '';
                $period_end = $detail['inboundCorrespondenceToDate'] ?? '';
                $due = $detail['inboundCorrespondenceDueDate'] ?? '';
                $status = $detail['status'] ?? 'O';

                if (!$period_start || !$period_end) { continue; }

                // Derive tax year from period start (tax year starts 6 Apr)
                $year = (int)date('Y', strtotime($period_start));
                $month = (int)date('n', strtotime($period_start));

                $tax_year = ($month >= 4) ? $year . '-' . substr($year + 1, -2) : ($year - 1) . '-' . substr($year, -2);

                $periods[] = [
                    'business_id'  => $ref,
                    'tax_year'     => $tax_year,
                    'period_start' => $period_start,
                    'period_end'   => $period_end,
                    'due'          => $due,
                    'status'       => $status,
                ];
            }
        }

        if ($periods) {
            $this->model_modification_hmrc_mtd->saveItsaPeriods($store_id, $periods);
            $this->session->data['success'] = $this->language->get('text_success_itsa_periods');
        } else {
            $this->session->data['success'] = $this->language->get('text_success_itsa_periods');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Show the ITSA quarterly update preparation form for a given period.
     */
    public function itsaPrepare(): void {
        $this->language->load('modification/' . $this->name);

        $this->document->setTitle($this->language->get('heading_itsa_prepare'));

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $business_id = $this->request->get['business_id'] ?? '';
        $period_start = $this->request->get['period_start'] ?? '';

        $period = $this->model_modification_hmrc_mtd->getItsaPeriod($store_id, $business_id, $period_start);

        if (!$period) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        // Use existing draft if present, otherwise calculate from orders
        $draft = $this->model_modification_hmrc_mtd->getItsaSubmission($store_id, $business_id, $period_start);

        $figures = $draft ?: $this->model_modification_hmrc_mtd->calculateItsaIncome($store_id, $period['period_start'], $period['period_end']);

        $this->getItsaPrepare($period, $figures);
    }

    /**
     * Submit a quarterly income/expense update to HMRC.
     */
    public function itsaSubmit(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $business_id = $this->request->post['business_id'] ?? '';
        $tax_year = $this->request->post['tax_year'] ?? '';
        $period_start = $this->request->post['period_start'] ?? '';
        $period_end = $this->request->post['period_end'] ?? '';

        if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->validateItsaSubmit()) {
            if ($business_id && $period_start) {
                $this->redirect($this->url->link('modification/' . $this->name . '/itsaPrepare', 'token=' . $this->session->data['token'] . '&business_id=' . urlencode($business_id) . '&period_start=' . urlencode($period_start), 'SSL'));
            } else {
                $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            }
            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $nino = strtoupper($settings['nino'] ?? '');

        $data = [
            'turnover'          => (float)($this->request->post['turnover'] ?? 0),
            'other_income'      => (float)($this->request->post['other_income'] ?? 0),
            'cost_of_goods'     => (float)($this->request->post['cost_of_goods'] ?? 0),
            'admin_costs'       => (float)($this->request->post['admin_costs'] ?? 0),
            'travel_costs'      => (float)($this->request->post['travel_costs'] ?? 0),
            'staff_costs'       => (float)($this->request->post['staff_costs'] ?? 0),
            'advertising_costs' => (float)($this->request->post['advertising_costs'] ?? 0),
            'premises_costs'    => (float)($this->request->post['premises_costs'] ?? 0),
            'other_expenses'    => (float)($this->request->post['other_expenses'] ?? 0),
            'finalised'         => 1,
        ];

        // Save draft before submitting
        $this->model_modification_hmrc_mtd->saveItsaSubmission($store_id, $business_id, $tax_year, $period_start, $period_end, $data);

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        $payload = [
            'incomes' => [
                'turnover'    => ['amount' => $data['turnover']],
                'otherIncome' => ['amount' => $data['other_income']],
            ],
            'deductions' => [
                'costOfGoods'           => ['amount' => $data['cost_of_goods'], 'disallowableAmount' => 0],
                'adminCosts'            => ['amount' => $data['admin_costs'], 'disallowableAmount' => 0],
                'businessTravelCosts'   => ['amount' => $data['travel_costs'], 'disallowableAmount' => 0],
                'staffCosts'            => ['amount' => $data['staff_costs'], 'disallowableAmount' => 0],
                'advertisingCosts'      => ['amount' => $data['advertising_costs'], 'disallowableAmount' => 0],
                'businessPremisesCosts' => ['amount' => $data['premises_costs'], 'disallowableAmount' => 0],
                'other'                 => ['amount' => $data['other_expenses'], 'disallowableAmount' => 0],
            ],
        ];

        $endpoint = '/individuals/business/self-employment/' . rawurlencode($nino) . '/' . rawurlencode($business_id) . '/period/summary/' . rawurlencode($tax_year);

        $response = $hmrc->post($endpoint, $tokens['access_token'], $this->user->getUserName(), $payload);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
            $this->redirect($this->url->link('modification/' . $this->name . '/itsaPrepare', 'token=' . $this->session->data['token'] . '&business_id=' . urlencode($business_id) . '&period_start=' . urlencode($period_start), 'SSL'));
            return;
        }

        $this->model_modification_hmrc_mtd->markItsaSubmitted($store_id, $business_id, $period_start, json_encode($response));

        $this->session->data['success'] = $this->language->get('text_success_itsa_submit');

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Submit an End of Period Statement (EOPS) for a given tax year.
     */
    public function eops(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $business_id = $this->request->post['business_id'] ?? '';
        $tax_year = $this->request->post['tax_year'] ?? '';

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        if (!$this->validatePermission()) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        if (empty($this->request->post['eops_finalised'])) {
            $this->session->data['error'] = $this->language->get('error_eops_finalised');
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $nino = strtoupper($settings['nino'] ?? '');

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        $endpoint = '/individuals/business/end-of-period-statement/' . rawurlencode($nino) . '/' . rawurlencode($business_id) . '/' . rawurlencode($tax_year);

        $response = $hmrc->post($endpoint, $tokens['access_token'], $this->user->getUserName(), ['finalised' => true]);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
        } else {
            $this->model_modification_hmrc_mtd->saveEops($store_id, $business_id, $tax_year, json_encode($response));
            $this->session->data['success'] = $this->language->get('text_success_eops');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Submit a Final Declaration (crystallisation) for a given tax year.
     */
    public function declaration(): void {
        $this->language->load('modification/' . $this->name);

        $this->load->model('modification/' . $this->name);

        $store_id = 0;
        $tax_year = $this->request->post['tax_year'] ?? '';

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        if (!$this->validatePermission()) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        if (empty($this->request->post['declaration_finalised'])) {
            $this->session->data['error'] = $this->language->get('error_declaration_finalised');
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $tokens = $this->getValidTokens($store_id);

        if (!$tokens) {
            $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
            return;
        }

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $nino = strtoupper($settings['nino'] ?? '');

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = $this->buildHmrcClient($settings);

        $endpoint = '/individuals/calculations/' . rawurlencode($nino) . '/self-assessment/' . rawurlencode($tax_year) . '/final-declaration';

        $response = $hmrc->post($endpoint, $tokens['access_token'], $this->user->getUserName(), ['finalised' => true]);

        if (isset($response['error'])) {
            $this->session->data['error'] = sprintf($this->language->get('error_api'), $response['error']);
        } else {
            $this->model_modification_hmrc_mtd->saveDeclaration($store_id, $tax_year, json_encode($response));
            $this->session->data['success'] = $this->language->get('text_success_declaration');
        }

        $this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     * Called by the extension manager uninstall action.
     */
    public function uninstall(): void {
        // Tables are intentionally left in place to preserve submitted return data.
        // The merchant can remove them manually if desired.
    }

    // -----------------------------------------------------------------------
    // Protected render methods
    // -----------------------------------------------------------------------

    /**
     * Build and render the main tabbed settings page.
     */
    protected function getSettings(): void {
        $store_id = 0;
        $lang = $this->language;

        // Language strings
        $this->data['heading_title'] = $lang->get('heading_title');

        $this->data['tab_core'] = $lang->get('tab_core');
        $this->data['tab_vat'] = $lang->get('tab_vat');
        $this->data['tab_itsa'] = $lang->get('tab_itsa');

        $this->data['text_credentials'] = $lang->get('text_credentials');
        $this->data['text_credentials_help'] = $lang->get('text_credentials_help');
        $this->data['text_connection_status'] = $lang->get('text_connection_status');
        $this->data['text_components'] = $lang->get('text_components');
        $this->data['text_connected'] = $lang->get('text_connected');
        $this->data['text_not_connected'] = $lang->get('text_not_connected');
        $this->data['text_sandbox_help'] = $lang->get('text_sandbox_help');
        $this->data['text_vat_enabled_help'] = $lang->get('text_vat_enabled_help');
        $this->data['text_redirect_uri_help'] = $lang->get('text_redirect_uri_help');
        $this->data['text_enabled'] = $lang->get('text_enabled');
        $this->data['text_disabled'] = $lang->get('text_disabled');
        $this->data['text_sandbox_mode'] = $lang->get('text_sandbox_mode');
        $this->data['text_production_mode'] = $lang->get('text_production_mode');

        $this->data['text_vat_settings'] = $lang->get('text_vat_settings');
        $this->data['text_vat_obligations'] = $lang->get('text_vat_obligations');
        $this->data['text_vat_history'] = $lang->get('text_vat_history');
        $this->data['text_vat_disabled_notice'] = $lang->get('text_vat_disabled_notice');
        $this->data['text_not_connected_notice'] = $lang->get('text_not_connected_notice');
        $this->data['text_no_obligations'] = $lang->get('text_no_obligations');
        $this->data['text_no_history'] = $lang->get('text_no_history');
        $this->data['text_obligation_open'] = $lang->get('text_obligation_open');
        $this->data['text_obligation_fulfilled'] = $lang->get('text_obligation_fulfilled');

        // ITSA tab strings
        $this->data['text_itsa_settings'] = $lang->get('text_itsa_settings');
        $this->data['text_itsa_periods'] = $lang->get('text_itsa_periods');
        $this->data['text_itsa_history'] = $lang->get('text_itsa_history');
        $this->data['text_itsa_year_actions'] = $lang->get('text_itsa_year_actions');
        $this->data['text_itsa_disabled_notice'] = $lang->get('text_itsa_disabled_notice');
        $this->data['text_no_periods'] = $lang->get('text_no_periods');
        $this->data['text_no_itsa_history'] = $lang->get('text_no_itsa_history');
        $this->data['text_itsa_status_open'] = $lang->get('text_itsa_status_open');
        $this->data['text_itsa_status_fulfilled'] = $lang->get('text_itsa_status_fulfilled');
        $this->data['text_eops_submitted'] = $lang->get('text_eops_submitted');
        $this->data['text_eops_not_submitted'] = $lang->get('text_eops_not_submitted');
        $this->data['text_declaration_submitted'] = $lang->get('text_declaration_submitted');
        $this->data['text_declaration_not_submitted'] = $lang->get('text_declaration_not_submitted');

        $this->data['entry_client_id'] = $lang->get('entry_client_id');
        $this->data['entry_client_secret'] = $lang->get('entry_client_secret');
        $this->data['entry_sandbox'] = $lang->get('entry_sandbox');
        $this->data['entry_vat_enabled'] = $lang->get('entry_vat_enabled');
        $this->data['entry_itsa_enabled'] = $lang->get('entry_itsa_enabled');
        $this->data['entry_vrn'] = $lang->get('entry_vrn');
        $this->data['entry_nino'] = $lang->get('entry_nino');
        $this->data['entry_itsa_business_id'] = $lang->get('entry_itsa_business_id');
        $this->data['entry_redirect_uri'] = $lang->get('entry_redirect_uri');
        $this->data['text_vrn_help'] = $lang->get('text_vrn_help');
        $this->data['text_nino_help'] = $lang->get('text_nino_help');
        $this->data['text_itsa_enabled_help'] = $lang->get('text_itsa_enabled_help');
        $this->data['text_itsa_business_help'] = $lang->get('text_itsa_business_help');

        $this->data['column_period'] = $lang->get('column_period');
        $this->data['column_due'] = $lang->get('column_due');
        $this->data['column_status'] = $lang->get('column_status');
        $this->data['column_action'] = $lang->get('column_action');
        $this->data['column_received'] = $lang->get('column_received');
        $this->data['column_period_key'] = $lang->get('column_period_key');
        $this->data['column_submitted'] = $lang->get('column_submitted');
        $this->data['column_net_vat'] = $lang->get('column_net_vat');
        $this->data['column_tax_year'] = $lang->get('column_tax_year');
        $this->data['column_income'] = $lang->get('column_income');
        $this->data['column_expenses'] = $lang->get('column_expenses');
        $this->data['column_eops'] = $lang->get('column_eops');
        $this->data['column_declaration'] = $lang->get('column_declaration');

        $this->data['button_save'] = $lang->get('button_save');
        $this->data['button_cancel'] = $lang->get('button_cancel');
        $this->data['button_connect'] = $lang->get('button_connect');
        $this->data['button_disconnect'] = $lang->get('button_disconnect');
        $this->data['button_fetch_obligations'] = $lang->get('button_fetch_obligations');
        $this->data['button_prepare'] = $lang->get('button_prepare');
        $this->data['button_fetch_periods'] = $lang->get('button_fetch_periods');
        $this->data['button_prepare_update'] = $lang->get('button_prepare_update');
        $this->data['button_submit_eops'] = $lang->get('button_submit_eops');
        $this->data['button_submit_declaration'] = $lang->get('button_submit_declaration');

        // Breadcrumbs
        $this->data['breadcrumbs'] = [
            [
                'text'      => $lang->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false,
            ],
            [
                'text'      => $lang->get('text_modification'),
                'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
            [
                'text'      => $lang->get('heading_title'),
                'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
        ];

        // URLs
        $this->data['action_save'] = $this->url->link('modification/' . $this->name . '/save', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_connect'] = $this->url->link('modification/' . $this->name . '/connect', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_disconnect'] = $this->url->link('modification/' . $this->name . '/disconnect', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_obligations'] = $this->url->link('modification/' . $this->name . '/obligations', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_prepare'] = $this->url->link('modification/' . $this->name . '/prepare', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_itsa_periods'] = $this->url->link('modification/' . $this->name . '/itsaPeriods', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_itsa_prepare'] = $this->url->link('modification/' . $this->name . '/itsaPrepare', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_itsa_submit'] = $this->url->link('modification/' . $this->name . '/itsaSubmit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_eops'] = $this->url->link('modification/' . $this->name . '/eops', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_declaration'] = $this->url->link('modification/' . $this->name . '/declaration', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['redirect_uri'] = $this->getRedirectUri();

        // Current settings
        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $this->data['client_id'] = $settings['client_id'] ?? '';
        $this->data['client_secret'] = $settings['client_secret'] ?? '';
        $this->data['sandbox'] = (int)($settings['sandbox'] ?? 1);
        $this->data['vat_enabled'] = (int)($settings['vat_enabled'] ?? 0);
        $this->data['vrn'] = $settings['vrn'] ?? '';
        $this->data['itsa_enabled'] = (int)($settings['itsa_enabled'] ?? 0);
        $this->data['nino'] = $settings['nino'] ?? '';
        $this->data['itsa_business_id'] = $settings['itsa_business_id'] ?? '';

        // Token / connection status
        $tokens = $this->model_modification_hmrc_mtd->getTokens($store_id);

        $this->data['is_connected'] = !empty($tokens['access_token']);
        $this->data['token_expires'] = $tokens['expires_at'] ?? '';

        // VAT obligations
        $this->data['obligations'] = $this->model_modification_hmrc_mtd->getObligations($store_id);

        // VAT return history
        $this->data['vat_returns'] = $this->model_modification_hmrc_mtd->getVatReturns($store_id);

        // ITSA quarterly periods
        $this->data['itsa_periods'] = $this->model_modification_hmrc_mtd->getItsaPeriods($store_id);

        // ITSA submitted updates history
        $this->data['itsa_submissions'] = $this->model_modification_hmrc_mtd->getItsaSubmissions($store_id);

        // ITSA EOPS and Final Declaration for display (group by tax year)
        $itsa_tax_years = [];

        foreach ($this->data['itsa_periods'] as $p) {
            $itsa_tax_years[$p['tax_year']] = $p['tax_year'];
        }

        foreach ($this->data['itsa_submissions'] as $s) {
            $itsa_tax_years[$s['tax_year']] = $s['tax_year'];
        }

        krsort($itsa_tax_years);

        $business_id = $settings['itsa_business_id'] ?? '';

        $itsa_year_status = [];

        foreach ($itsa_tax_years as $ty) {
            $eops = $business_id ? $this->model_modification_hmrc_mtd->getEops($store_id, $business_id, $ty) : [];
            $declaration = $this->model_modification_hmrc_mtd->getDeclaration($store_id, $ty);

            $itsa_year_status[$ty] = [
                'tax_year'              => $ty,
                'business_id'           => $business_id,
                'eops'                  => $eops,
                'declaration'           => $declaration,
                'eops_submitted'        => !empty($eops['submitted_at']),
                'declaration_submitted' => !empty($declaration['submitted_at']),
            ];
        }

        $this->data['itsa_year_status'] = $itsa_year_status;
        $this->data['itsa_eops_confirm'] = $lang->get('text_eops_finalised_confirm');
        $this->data['itsa_decl_confirm'] = $lang->get('text_declaration_finalised_confirm');

        // Session messages
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['token'] = $this->session->data['token'];

        $this->template = 'modification/hmrc_mtd.tpl';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    /**
     * Build and render the VAT return preparation form.
     */
    protected function getVatPrepare(array $obligation, array $figures): void {
        $lang = $this->language;

        $this->data['heading_title'] = $lang->get('heading_title');
        $this->data['heading_prepare'] = $lang->get('heading_prepare');

        $this->data['text_prepare_intro'] = $lang->get('text_prepare_intro');
        $this->data['text_vat_boxes'] = $lang->get('text_vat_boxes');
        $this->data['text_finalised_label'] = $lang->get('text_finalised_label');
        $this->data['text_finalised_confirm'] = $lang->get('text_finalised_confirm');
        $this->data['text_box_auto'] = $lang->get('text_box_auto');
        $this->data['text_box_manual'] = $lang->get('text_box_manual');
        $this->data['text_box_derived'] = $lang->get('text_box_derived');
        $this->data['text_box_derived_diff'] = $lang->get('text_box_derived_diff');

        $this->data['entry_vat_due_sales'] = $lang->get('entry_vat_due_sales');
        $this->data['entry_vat_due_acquisitions'] = $lang->get('entry_vat_due_acquisitions');
        $this->data['entry_total_vat_due'] = $lang->get('entry_total_vat_due');
        $this->data['entry_vat_reclaimed'] = $lang->get('entry_vat_reclaimed');
        $this->data['entry_net_vat_due'] = $lang->get('entry_net_vat_due');
        $this->data['entry_total_value_sales'] = $lang->get('entry_total_value_sales');
        $this->data['entry_total_value_purchases'] = $lang->get('entry_total_value_purchases');
        $this->data['entry_total_goods_supplied'] = $lang->get('entry_total_goods_supplied');
        $this->data['entry_total_acquisitions'] = $lang->get('entry_total_acquisitions');

        $this->data['button_submit'] = $lang->get('button_submit');
        $this->data['button_back'] = $lang->get('button_back');

        // Breadcrumbs
        $this->data['breadcrumbs'] = [
            [
                'text'      => $lang->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false,
            ],
            [
                'text'      => $lang->get('text_modification'),
                'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
            [
                'text'      => $lang->get('heading_title'),
                'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
            [
                'text'      => $lang->get('heading_prepare'),
                'href'      => $this->url->link('modification/' . $this->name . '/prepare', 'token=' . $this->session->data['token'] . '&period_key=' . urlencode($obligation['period_key']), 'SSL'),
                'separator' => ' :: ',
            ],
        ];

        $this->data['action_submit'] = $this->url->link('modification/' . $this->name . '/submit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['back'] = $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL');

        $this->data['obligation'] = $obligation;
        $this->data['period_label'] = sprintf($lang->get('text_period_label'), $obligation['start'], $obligation['end']);

        $this->data['vat_due_sales'] = number_format((float)$figures['vat_due_sales'], 2, '.', '');
        $this->data['vat_due_acquisitions'] = number_format((float)$figures['vat_due_acquisitions'], 2, '.', '');
        $this->data['total_vat_due'] = number_format((float)$figures['total_vat_due'], 2, '.', '');
        $this->data['vat_reclaimed'] = number_format((float)$figures['vat_reclaimed'], 2, '.', '');
        $this->data['net_vat_due'] = number_format((float)$figures['net_vat_due'], 2, '.', '');
        $this->data['total_value_sales'] = number_format((float)$figures['total_value_sales'], 2, '.', '');
        $this->data['total_value_purchases'] = number_format((float)$figures['total_value_purchases'], 2, '.', '');
        $this->data['total_goods_supplied'] = number_format((float)$figures['total_goods_supplied'], 2, '.', '');
        $this->data['total_acquisitions'] = number_format((float)$figures['total_acquisitions'], 2, '.', '');

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['token'] = $this->session->data['token'];

        $this->template = 'modification/hmrc_mtd_vat_prepare.tpl';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    /**
     * Build and render the ITSA quarterly update preparation form.
     */
    protected function getItsaPrepare(array $period, array $figures): void {
        $lang = $this->language;

        $this->data['heading_title'] = $lang->get('heading_title');
        $this->data['heading_itsa_prepare'] = $lang->get('heading_itsa_prepare');

        $this->data['text_itsa_prepare_intro'] = $lang->get('text_itsa_prepare_intro');
        $this->data['text_itsa_income_section'] = $lang->get('text_itsa_income_section');
        $this->data['text_itsa_expenses_section'] = $lang->get('text_itsa_expenses_section');
        $this->data['text_box_auto'] = $lang->get('text_box_auto');
        $this->data['text_box_manual'] = $lang->get('text_box_manual');
        $this->data['text_finalised_label'] = $lang->get('text_finalised_label');
        $this->data['text_itsa_finalised_confirm'] = $lang->get('text_itsa_finalised_confirm');

        $this->data['entry_turnover'] = $lang->get('entry_turnover');
        $this->data['entry_other_income'] = $lang->get('entry_other_income');
        $this->data['entry_cost_of_goods'] = $lang->get('entry_cost_of_goods');
        $this->data['entry_admin_costs'] = $lang->get('entry_admin_costs');
        $this->data['entry_travel_costs'] = $lang->get('entry_travel_costs');
        $this->data['entry_staff_costs'] = $lang->get('entry_staff_costs');
        $this->data['entry_advertising_costs'] = $lang->get('entry_advertising_costs');
        $this->data['entry_premises_costs'] = $lang->get('entry_premises_costs');
        $this->data['entry_other_expenses'] = $lang->get('entry_other_expenses');

        $this->data['button_submit'] = $lang->get('button_submit');
        $this->data['button_back'] = $lang->get('button_back');

        // Breadcrumbs
        $this->data['breadcrumbs'] = [
            [
                'text'      => $lang->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false,
            ],
            [
                'text'      => $lang->get('text_modification'),
                'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
            [
                'text'      => $lang->get('heading_title'),
                'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: ',
            ],
            [
                'text'      => $lang->get('heading_itsa_prepare'),
                'href'      => $this->url->link('modification/' . $this->name . '/itsaPrepare', 'token=' . $this->session->data['token'] . '&business_id=' . urlencode($period['business_id']) . '&period_start=' . urlencode($period['period_start']), 'SSL'),
                'separator' => ' :: ',
            ],
        ];

        $this->data['action_submit'] = $this->url->link('modification/' . $this->name . '/itsaSubmit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['back'] = $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL');

        $this->data['period'] = $period;
        $this->data['business_id'] = $period['business_id'];
        $this->data['period_start'] = $period['period_start'];
        $this->data['period_end'] = $period['period_end'];
        $this->data['tax_year'] = $period['tax_year'];
        $this->data['text_turnover_help'] = $lang->get('text_turnover_help');
        $this->data['text_other_income_help'] = $lang->get('text_other_income_help');
        $this->data['period_label'] = sprintf($lang->get('text_period_label'), $period['period_start'], $period['period_end']);

        $this->data['turnover'] = number_format((float)$figures['turnover'], 2, '.', '');
        $this->data['other_income'] = number_format((float)$figures['other_income'], 2, '.', '');
        $this->data['cost_of_goods'] = number_format((float)$figures['cost_of_goods'], 2, '.', '');
        $this->data['admin_costs'] = number_format((float)$figures['admin_costs'], 2, '.', '');
        $this->data['travel_costs'] = number_format((float)$figures['travel_costs'], 2, '.', '');
        $this->data['staff_costs'] = number_format((float)$figures['staff_costs'], 2, '.', '');
        $this->data['advertising_costs'] = number_format((float)$figures['advertising_costs'], 2, '.', '');
        $this->data['premises_costs'] = number_format((float)$figures['premises_costs'], 2, '.', '');
        $this->data['other_expenses'] = number_format((float)$figures['other_expenses'], 2, '.', '');

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['token'] = $this->session->data['token'];

        $this->template = 'modification/hmrc_mtd_itsa_prepare.tpl';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    // -----------------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------------

    protected function validateSettings(): bool {
        if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return empty($this->error);
    }

    protected function validatePermission(): bool {
        if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
            $this->session->data['error'] = $this->language->get('error_permission');
            return false;
        }

        return true;
    }

    protected function validateSubmit(): bool {
        if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        if (empty($this->request->post['finalised'])) {
            $this->session->data['error'] = $this->language->get('error_finalised');
            return false;
        }

        $boxes = ['vat_due_sales','vat_due_acquisitions','total_vat_due','vat_reclaimed', 'net_vat_due','total_value_sales','total_value_purchases', 'total_goods_supplied','total_acquisitions'];

        foreach ($boxes as $box) {
            if (!isset($this->request->post[$box]) || !is_numeric($this->request->post[$box])) {
                $this->session->data['error'] = $this->language->get('error_vat_box');
                return false;
            }
        }

        return true;
    }

    protected function validateItsaSubmit(): bool {
        if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
            $this->session->data['error'] = $this->language->get('error_permission');
            return false;
        }

        if (empty($this->request->post['finalised'])) {
            $this->session->data['error'] = $this->language->get('error_finalised');
            return false;
        }

        $fields = ['turnover', 'other_income', 'cost_of_goods', 'admin_costs', 'travel_costs', 'staff_costs', 'advertising_costs', 'premises_costs', 'other_expenses'];

        foreach ($fields as $field) {
            if (!isset($this->request->post[$field]) || !is_numeric($this->request->post[$field])) {
                $this->session->data['error'] = $this->language->get('error_itsa_box');
                return false;
            }
        }

        return true;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Load stored tokens and refresh if expired. Returns tokens array or false.
     */
    private function getValidTokens(int $store_id): array|false {
        $tokens = $this->model_modification_hmrc_mtd->getTokens($store_id);

        if (empty($tokens['access_token'])) {
            $this->session->data['error'] = $this->language->get('error_not_connected');
            return false;
        }

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        $hmrc = $this->buildHmrcClient($settings);

        if ($hmrc->isTokenExpired($tokens['expires_at'])) {
            $refreshed = $hmrc->refreshAccessToken($tokens['refresh_token']);

            if (isset($refreshed['error'])) {
                $this->session->data['error'] = sprintf($this->language->get('error_token_refresh'), $refreshed['error']);
                return false;
            }

            $this->model_modification_hmrc_mtd->saveTokens($store_id, $refreshed['access_token'], $refreshed['refresh_token'] ?? $tokens['refresh_token'], (int)($refreshed['expires_in'] ?? 14400));

            $tokens['access_token'] = $refreshed['access_token'];
        }

        return $tokens;
    }

    /**
     * Instantiate HmrcMtd client from stored settings.
     * Caller must require_once the library first.
     */
    private function buildHmrcClient(array $settings): HmrcMtd {
        return new HmrcMtd($settings['client_id'] ?? '', $settings['client_secret'] ?? '', (bool)(int)($settings['sandbox'] ?? 1));
    }

    /**
     * The OAuth redirect URI — the catalog-side callback controller URL.
     * This must be registered exactly in the HMRC Developer Hub application.
     */
    private function getRedirectUri(): string {
        return HTTPS_CATALOG . 'index.php?route=modification/hmrc_mtd/callback';
    }
}
