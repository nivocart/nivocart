<?php
/**
 * Class ModelModificationHmrcMtd
 *
 * Handles all database operations for the HMRC Making Tax Digital modification.
 * Tables are created lazily via checkHmrcMtd() called from the controller index.
 *
 * @package NivoCart
 */
class ModelModificationHmrcMtd extends Model {
    // -----------------------------------------------------------------------
    // Table bootstrap — called on every index() load (EU Taxes pattern)
    // -----------------------------------------------------------------------

    /**
     * Ensure all required tables exist. Safe to call on every page load.
     */
    public function checkHmrcMtd(): void {
        // Settings key/value store (client credentials, toggles, VRN, etc.)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_settings` (
                `setting_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `key` varchar(64) NOT NULL,
                `value` text NOT NULL,
                PRIMARY KEY (`setting_id`),
                UNIQUE KEY `store_key` (`store_id`, `key`(32))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // OAuth tokens — one active set per store
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_tokens` (
                `token_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `access_token` text NOT NULL,
                `refresh_token` text NOT NULL,
                `expires_at` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`token_id`),
                UNIQUE KEY `store_id` (`store_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // VAT obligations fetched from HMRC
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_vat_obligations` (
                `obligation_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `period_key` varchar(10) NOT NULL,
                `start` date NOT NULL,
                `end` date NOT NULL,
                `due` date NOT NULL,
                `status` varchar(1) NOT NULL DEFAULT 'O',
                `received` date DEFAULT NULL,
                `date_fetched` datetime NOT NULL,
                PRIMARY KEY (`obligation_id`),
                UNIQUE KEY `store_period` (`store_id`, `period_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Submitted VAT returns
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_vat_returns` (
                `return_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `period_key` varchar(10) NOT NULL,
                `vat_due_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
                `vat_due_acquisitions` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_vat_due` decimal(15,2) NOT NULL DEFAULT 0.00,
                `vat_reclaimed` decimal(15,2) NOT NULL DEFAULT 0.00,
                `net_vat_due` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_value_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_value_purchases` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_goods_supplied` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_acquisitions` decimal(15,2) NOT NULL DEFAULT 0.00,
                `finalised` tinyint(1) NOT NULL DEFAULT 0,
                `hmrc_receipt` text DEFAULT NULL,
                `submitted_at` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`return_id`),
                UNIQUE KEY `store_period` (`store_id`, `period_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // ---- ITSA tables -------------------------------------------------------

        // Quarterly update periods fetched from HMRC (one row per quarter per business)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_itsa_periods` (
                `period_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `business_id` varchar(20) NOT NULL,
                `tax_year` varchar(9) NOT NULL,
                `period_start` date NOT NULL,
                `period_end` date NOT NULL,
                `due` date DEFAULT NULL,
                `status` varchar(1) NOT NULL DEFAULT 'O',
                `date_fetched` datetime NOT NULL,
                PRIMARY KEY (`period_id`),
                UNIQUE KEY `store_business_start` (`store_id`, `business_id`(20), `period_start`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Submitted quarterly income/expense updates
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_itsa_submissions` (
                `submission_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `business_id` varchar(20) NOT NULL,
                `tax_year` varchar(9) NOT NULL,
                `period_start` date NOT NULL,
                `period_end` date NOT NULL,
                `turnover` decimal(15,2) NOT NULL DEFAULT 0.00,
                `other_income` decimal(15,2) NOT NULL DEFAULT 0.00,
                `cost_of_goods` decimal(15,2) NOT NULL DEFAULT 0.00,
                `admin_costs` decimal(15,2) NOT NULL DEFAULT 0.00,
                `travel_costs` decimal(15,2) NOT NULL DEFAULT 0.00,
                `staff_costs` decimal(15,2) NOT NULL DEFAULT 0.00,
                `advertising_costs` decimal(15,2) NOT NULL DEFAULT 0.00,
                `premises_costs` decimal(15,2) NOT NULL DEFAULT 0.00,
                `other_expenses` decimal(15,2) NOT NULL DEFAULT 0.00,
                `finalised` tinyint(1) NOT NULL DEFAULT 0,
                `hmrc_receipt` text DEFAULT NULL,
                `submitted_at` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`submission_id`),
                UNIQUE KEY `store_business_start` (`store_id`, `business_id`(20), `period_start`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // End of Period Statements (one per business per tax year)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_itsa_eops` (
                `eops_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `business_id` varchar(20) NOT NULL,
                `tax_year` varchar(9) NOT NULL,
                `finalised` tinyint(1) NOT NULL DEFAULT 0,
                `hmrc_receipt` text DEFAULT NULL,
                `submitted_at` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`eops_id`),
                UNIQUE KEY `store_business_year` (`store_id`, `business_id`(20), `tax_year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Final Declarations (one per NINO per tax year — replaces SA100)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hmrc_mtd_itsa_declarations` (
                `declaration_id` int NOT NULL AUTO_INCREMENT,
                `store_id` int NOT NULL DEFAULT 0,
                `tax_year` varchar(9) NOT NULL,
                `finalised` tinyint(1) NOT NULL DEFAULT 0,
                `hmrc_receipt` text DEFAULT NULL,
                `submitted_at` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`declaration_id`),
                UNIQUE KEY `store_year` (`store_id`, `tax_year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    // -----------------------------------------------------------------------
    // Settings
    // -----------------------------------------------------------------------

    public function saveSetting(int $store_id, string $key, string $value): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_settings` (`store_id`, `key`, `value`) VALUES ('" . (int)$store_id . "', '" . $this->db->escape($key) . "', '" . $this->db->escape($value) . "') ON DUPLICATE KEY UPDATE `value` = '" . $this->db->escape($value) . "'");
    }

    public function getSetting(int $store_id, string $key, string $default = ''): string {
        $query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "hmrc_mtd_settings` WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

        return $query->row ? $query->row['value'] : $default;
    }

    /**
     * Save all settings from a POST array in one call.
     * Keys: client_id, client_secret, sandbox, vat_enabled, vrn
     */
    public function saveSettings(int $store_id, array $data): void {
        $allowed = ['client_id', 'client_secret', 'sandbox', 'vat_enabled', 'vrn', 'itsa_enabled', 'nino', 'itsa_business_id'];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $this->saveSetting($store_id, $key, (string)$data[$key]);
            }
        }
    }

    /**
     * Return all settings for a store as a flat key => value array.
     */
    public function getSettings(int $store_id): array {
        $query = $this->db->query("SELECT `key`, `value` FROM `" . DB_PREFIX . "hmrc_mtd_settings` WHERE store_id = '" . (int)$store_id . "'");

        $result = [];

        foreach ($query->rows as $row) {
            $result[$row['key']] = $row['value'];
        }

        return $result;
    }

    // -----------------------------------------------------------------------
    // Tokens
    // -----------------------------------------------------------------------

    public function saveTokens(int $store_id, string $access_token, string $refresh_token, int $expires_in): void {
        $expires_at = date('Y-m-d H:i:s', time() + (int)$expires_in);

        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_tokens` (`store_id`, `access_token`, `refresh_token`, `expires_at`, `date_modified`)
            VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($access_token) . "',
                '" . $this->db->escape($refresh_token) . "',
                '" . $this->db->escape($expires_at) . "',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `access_token` = '" . $this->db->escape($access_token) . "',
                `refresh_token` = '" . $this->db->escape($refresh_token) . "',
                `expires_at` = '" . $this->db->escape($expires_at) . "',
                `date_modified` = NOW()
        ");
    }

    public function getTokens(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_tokens` WHERE store_id = '" . (int)$store_id . "'");

        return $query->row ?: [];
    }

    public function deleteTokens(int $store_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "hmrc_mtd_tokens` WHERE store_id = '" . (int)$store_id . "'");
    }

    // -----------------------------------------------------------------------
    // VAT Obligations
    // -----------------------------------------------------------------------

    /**
     * Upsert obligations from the HMRC API response.
     * $obligations is the array from HMRC's 'obligations' key.
     */
    public function saveObligations(int $store_id, array $obligations): void {
        foreach ($obligations as $ob) {
            $received = !empty($ob['received']) ? "'" . $this->db->escape($ob['received']) . "'" : 'NULL';

            $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_vat_obligations` (`store_id`, `period_key`, `start`, `end`, `due`, `status`, `received`, `date_fetched`)
                VALUES (
                    '" . (int)$store_id . "',
                    '" . $this->db->escape($ob['periodKey']) . "',
                    '" . $this->db->escape($ob['start']) . "',
                    '" . $this->db->escape($ob['end']) . "',
                    '" . $this->db->escape($ob['due']) . "',
                    '" . $this->db->escape($ob['status']) . "',
                    $received,
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    `start` = '" . $this->db->escape($ob['start']) . "',
                    `end` = '" . $this->db->escape($ob['end']) . "',
                    `due` = '" . $this->db->escape($ob['due']) . "',
                    `status` = '" . $this->db->escape($ob['status']) . "',
                    `received` = $received,
                    `date_fetched` = NOW()
            ");
        }
    }

    /**
     * Return all stored obligations for a store, newest period first.
     */
    public function getObligations(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_vat_obligations` WHERE store_id = '" . (int)$store_id . "' ORDER BY `end` DESC");

        return $query->rows;
    }

    /**
     * Return only open (unfulfilled) obligations.
     */
    public function getOpenObligations(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_vat_obligations` WHERE store_id = '" . (int)$store_id . "' AND `status` = 'O' ORDER BY `due` ASC");

        return $query->rows;
    }

    /**
     * Return a single obligation by period key.
     */
    public function getObligation(int $store_id, string $period_key): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_vat_obligations` WHERE store_id = '" . (int)$store_id . "' AND period_key = '" . $this->db->escape($period_key) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // VAT Returns
    // -----------------------------------------------------------------------

    /**
     * Insert or update a draft VAT return record.
     */
    public function saveVatReturn(int $store_id, string $period_key, array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_vat_returns` (`store_id`, `period_key`, `vat_due_sales`, `vat_due_acquisitions`, `total_vat_due`, `vat_reclaimed`, `net_vat_due`, `total_value_sales`, `total_value_purchases`, `total_goods_supplied`, `total_acquisitions`, `finalised`, `date_added`)
            VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($period_key) . "',
                '" . (float)$data['vat_due_sales'] . "',
                '" . (float)$data['vat_due_acquisitions'] . "',
                '" . (float)$data['total_vat_due'] . "',
                '" . (float)$data['vat_reclaimed'] . "',
                '" . (float)$data['net_vat_due'] . "',
                '" . (float)$data['total_value_sales'] . "',
                '" . (float)$data['total_value_purchases'] . "',
                '" . (float)$data['total_goods_supplied'] . "',
                '" . (float)$data['total_acquisitions'] . "',
                '" . (int)$data['finalised'] . "',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `vat_due_sales` = '" . (float)$data['vat_due_sales'] . "',
                `vat_due_acquisitions` = '" . (float)$data['vat_due_acquisitions'] . "',
                `total_vat_due` = '" . (float)$data['total_vat_due'] . "',
                `vat_reclaimed` = '" . (float)$data['vat_reclaimed'] . "',
                `net_vat_due` = '" . (float)$data['net_vat_due'] . "',
                `total_value_sales` = '" . (float)$data['total_value_sales'] . "',
                `total_value_purchases` = '" . (float)$data['total_value_purchases'] . "',
                `total_goods_supplied` = '" . (float)$data['total_goods_supplied'] . "',
                `total_acquisitions` = '" . (float)$data['total_acquisitions'] . "',
                `finalised` = '" . (int)$data['finalised'] . "'
        ");
    }

    /**
     * Store the HMRC submission receipt and mark as submitted.
     */
    public function markVatReturnSubmitted(int $store_id, string $period_key, string $receipt_json): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "hmrc_mtd_vat_returns` SET `hmrc_receipt` = '" . $this->db->escape($receipt_json) . "', `submitted_at` = NOW(), `finalised` = 1 WHERE store_id = '" . (int)$store_id . "' AND period_key = '" . $this->db->escape($period_key) . "'");
    }

    /**
     * Return all submitted VAT returns for a store, newest first.
     */
    public function getVatReturns(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_vat_returns` WHERE store_id = '" . (int)$store_id . "' AND submitted_at IS NOT NULL ORDER BY submitted_at DESC");

        return $query->rows;
    }

    /**
     * Return a single VAT return draft or submitted record by period key.
     */
    public function getVatReturn(int $store_id, string $period_key): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_vat_returns` WHERE store_id   = '" . (int)$store_id . "' AND period_key = '" . $this->db->escape($period_key) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // VAT Figure Calculation
    // -----------------------------------------------------------------------

    /**
     * Aggregate VAT and sales figures from NivoCart order totals
     * for a given date range. Returns pre-filled values for the 9-box return.
     *
     * Box 1 (VAT due on sales):     SUM of 'tax' order totals in completed orders.
     * Box 6 (Total sales ex-VAT):   SUM of 'sub_total' order totals in completed orders.
     * All other boxes default to 0 — the merchant reviews and edits before submitting.
     *
     * @param int    $store_id
     * @param string $start    Date format Y-m-d
     * @param string $end      Date format Y-m-d
     * @return array
     */
    public function calculateVatFigures(int $store_id, string $start, string $end): array {
        // Completed order status IDs — 5 = Complete in default OpenCart
        // We use a status > 0 AND not cancelled/refunded heuristic.
        // Only orders from the relevant store.
        $date_start = $this->db->escape($start . ' 00:00:00');
        $date_end = $this->db->escape($end . ' 23:59:59');

        // VAT collected (Box 1) — sum of 'tax' totals on completed orders
        $tax_query = $this->db->query("SELECT COALESCE(SUM(ot.value), 0) AS vat_total FROM `" . DB_PREFIX . "order_total` ot INNER JOIN `" . DB_PREFIX . "order` o ON o.order_id = ot.order_id WHERE ot.code = 'tax' AND o.store_id = '" . (int)$store_id . "' AND o.order_status_id > 0 AND o.date_added >= '" . $date_start . "' AND o.date_added <= '" . $date_end . "'");

        // Sales ex-VAT (Box 6) — sum of 'sub_total' totals on completed orders
        $sales_query = $this->db->query("SELECT COALESCE(SUM(ot.value), 0) AS sales_total FROM `" . DB_PREFIX . "order_total` ot INNER JOIN `" . DB_PREFIX . "order` o ON o.order_id = ot.order_id WHERE ot.code = 'sub_total' AND o.store_id = '" . (int)$store_id . "' AND o.order_status_id > 0 AND o.date_added >= '" . $date_start . "' AND o.date_added <= '" . $date_end . "'");

        $vat_due_sales = round((float)$tax_query->row['vat_total'], 2, PHP_ROUND_HALF_UP);
        $total_value_sales = round((float)$sales_query->row['sales_total'], 2, PHP_ROUND_HALF_UP);

        return [
            'vat_due_sales'         => $vat_due_sales,
            'vat_due_acquisitions'  => 0.00,
            'total_vat_due'         => $vat_due_sales,   // Box 3 = Box 1 + Box 2
            'vat_reclaimed'         => 0.00,
            'net_vat_due'           => $vat_due_sales,   // Box 5 = Box 3 - Box 4
            'total_value_sales'     => $total_value_sales,
            'total_value_purchases' => 0.00,
            'total_goods_supplied'  => 0.00,
            'total_acquisitions'    => 0.00,
        ];
    }

    // -----------------------------------------------------------------------
    // ITSA — Quarterly Periods
    // -----------------------------------------------------------------------

    /**
     * Upsert quarterly obligation periods from the HMRC API response.
     * $periods is a flat array of period arrays, each with keys:
     *   business_id, tax_year, period_start, period_end, due, status
     */
    public function saveItsaPeriods(int $store_id, array $periods): void {
        foreach ($periods as $p) {
            $due = !empty($p['due']) ? "'" . $this->db->escape($p['due']) . "'" : 'NULL';

            $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_itsa_periods` (`store_id`, `business_id`, `tax_year`, `period_start`, `period_end`, `due`, `status`, `date_fetched`)
                VALUES (
                    '" . (int)$store_id . "',
                    '" . $this->db->escape($p['business_id']) . "',
                    '" . $this->db->escape($p['tax_year']) . "',
                    '" . $this->db->escape($p['period_start']) . "',
                    '" . $this->db->escape($p['period_end']) . "',
                    $due,
                    '" . $this->db->escape($p['status']) . "',
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    `tax_year`    = '" . $this->db->escape($p['tax_year']) . "',
                    `period_end`  = '" . $this->db->escape($p['period_end']) . "',
                    `due`         = $due,
                    `status`      = '" . $this->db->escape($p['status']) . "',
                    `date_fetched`= NOW()
            ");
        }
    }

    /**
     * Return all stored ITSA periods for a store, newest first.
     */
    public function getItsaPeriods(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_periods` WHERE store_id = '" . (int)$store_id . "' ORDER BY `period_start` DESC");

        return $query->rows;
    }

    /**
     * Return a single ITSA period by store, business ID and start date.
     */
    public function getItsaPeriod(int $store_id, string $business_id, string $period_start): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_periods` WHERE store_id = '" . (int)$store_id . "' AND business_id = '" . $this->db->escape($business_id) . "' AND period_start = '" . $this->db->escape($period_start) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // ITSA — Quarterly Submissions
    // -----------------------------------------------------------------------

    /**
     * Insert or update a draft quarterly income/expense submission.
     */
    public function saveItsaSubmission(int $store_id, string $business_id, string $tax_year, string $period_start, string $period_end, array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_itsa_submissions` (`store_id`, `business_id`, `tax_year`, `period_start`, `period_end`, `turnover`, `other_income`, `cost_of_goods`, `admin_costs`, `travel_costs`, `staff_costs`, `advertising_costs`, `premises_costs`, `other_expenses`, `finalised`, `date_added`)
            VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($business_id) . "',
                '" . $this->db->escape($tax_year) . "',
                '" . $this->db->escape($period_start) . "',
                '" . $this->db->escape($period_end) . "',
                '" . (float)$data['turnover'] . "',
                '" . (float)$data['other_income'] . "',
                '" . (float)$data['cost_of_goods'] . "',
                '" . (float)$data['admin_costs'] . "',
                '" . (float)$data['travel_costs'] . "',
                '" . (float)$data['staff_costs'] . "',
                '" . (float)$data['advertising_costs'] . "',
                '" . (float)$data['premises_costs'] . "',
                '" . (float)$data['other_expenses'] . "',
                '" . (int)$data['finalised'] . "',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `tax_year` = '" . $this->db->escape($tax_year) . "',
                `period_end` = '" . $this->db->escape($period_end) . "',
                `turnover` = '" . (float)$data['turnover'] . "',
                `other_income` = '" . (float)$data['other_income'] . "',
                `cost_of_goods` = '" . (float)$data['cost_of_goods'] . "',
                `admin_costs` = '" . (float)$data['admin_costs'] . "',
                `travel_costs` = '" . (float)$data['travel_costs'] . "',
                `staff_costs` = '" . (float)$data['staff_costs'] . "',
                `advertising_costs` = '" . (float)$data['advertising_costs'] . "',
                `premises_costs` = '" . (float)$data['premises_costs'] . "',
                `other_expenses` = '" . (float)$data['other_expenses'] . "',
                `finalised` = '" . (int)$data['finalised'] . "'
        ");
    }

    /**
     * Store the HMRC receipt and mark a quarterly submission as submitted.
     */
    public function markItsaSubmitted(int $store_id, string $business_id, string $period_start, string $receipt_json): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "hmrc_mtd_itsa_submissions` SET `hmrc_receipt` = '" . $this->db->escape($receipt_json) . "', `submitted_at` = NOW(), `finalised` = 1 WHERE store_id = '" . (int)$store_id . "' AND business_id = '" . $this->db->escape($business_id) . "' AND period_start = '" . $this->db->escape($period_start) . "'");

        // Also mark the period as Fulfilled
        $this->db->query("UPDATE `" . DB_PREFIX . "hmrc_mtd_itsa_periods` SET `status` = 'F' WHERE store_id = '" . (int)$store_id . "' AND business_id = '" . $this->db->escape($business_id) . "' AND period_start = '" . $this->db->escape($period_start) . "'");
    }

    /**
     * Return all submitted quarterly updates for a store, newest first.
     */
    public function getItsaSubmissions(int $store_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_submissions` WHERE store_id = '" . (int)$store_id . "' AND submitted_at IS NOT NULL ORDER BY period_start DESC");

        return $query->rows;
    }

    /**
     * Return a single quarterly submission draft or record.
     */
    public function getItsaSubmission(int $store_id, string $business_id, string $period_start): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_submissions` WHERE store_id = '" . (int)$store_id . "' AND business_id = '" . $this->db->escape($business_id) . "' AND period_start = '" . $this->db->escape($period_start) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // ITSA — End of Period Statements
    // -----------------------------------------------------------------------

    public function saveEops(int $store_id, string $business_id, string $tax_year, string $receipt_json): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_itsa_eops` (`store_id`, `business_id`, `tax_year`, `finalised`, `hmrc_receipt`, `submitted_at`, `date_added`)
            VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($business_id) . "',
                '" . $this->db->escape($tax_year) . "',
                1,
                '" . $this->db->escape($receipt_json) . "',
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `finalised` = 1,
                `hmrc_receipt` = '" . $this->db->escape($receipt_json) . "',
                `submitted_at` = NOW()
        ");
    }

    public function getEops(int $store_id, string $business_id, string $tax_year): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_eops` WHERE store_id = '" . (int)$store_id . "' AND business_id = '" . $this->db->escape($business_id) . "' AND tax_year = '" . $this->db->escape($tax_year) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // ITSA — Final Declarations
    // -----------------------------------------------------------------------

    public function saveDeclaration(int $store_id, string $tax_year, string $receipt_json): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_itsa_declarations` (`store_id`, `tax_year`, `finalised`, `hmrc_receipt`, `submitted_at`, `date_added`)
            VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($tax_year) . "',
                1,
                '" . $this->db->escape($receipt_json) . "',
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `finalised` = 1,
                `hmrc_receipt` = '" . $this->db->escape($receipt_json) . "',
                `submitted_at` = NOW()
        ");
    }

    public function getDeclaration(int $store_id, string $tax_year): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hmrc_mtd_itsa_declarations` WHERE store_id = '" . (int)$store_id . "' AND tax_year = '" . $this->db->escape($tax_year) . "'");

        return $query->row ?: [];
    }

    // -----------------------------------------------------------------------
    // ITSA — Income Calculation
    // -----------------------------------------------------------------------

    /**
     * Aggregate income from NivoCart order totals for a given date range.
     * Used to pre-fill the quarterly update form.
     *
     * Turnover = SUM of 'sub_total' (sales ex-VAT) on completed orders.
     * All expense fields default to 0 — entered manually by the merchant.
     *
     * @param int    $store_id
     * @param string $start    Date format Y-m-d
     * @param string $end      Date format Y-m-d
     * @return array
     */
    public function calculateItsaIncome(int $store_id, string $start, string $end): array {
        $date_start = $this->db->escape($start . ' 00:00:00');
        $date_end = $this->db->escape($end . ' 23:59:59');

        $query = $this->db->query("SELECT COALESCE(SUM(ot.value), 0) AS turnover FROM `" . DB_PREFIX . "order_total` ot INNER JOIN `" . DB_PREFIX . "order` o ON o.order_id = ot.order_id WHERE ot.code = 'sub_total' AND o.store_id = '" . (int)$store_id . "' AND o.order_status_id > 0 AND o.date_added >= '" . $date_start . "' AND o.date_added <= '" . $date_end . "'");

        $turnover = round((float)$query->row['turnover'], 2, PHP_ROUND_HALF_UP);

        return [
            'turnover'          => $turnover,
            'other_income'      => 0.00,
            'cost_of_goods'     => 0.00,
            'admin_costs'       => 0.00,
            'travel_costs'      => 0.00,
            'staff_costs'       => 0.00,
            'advertising_costs' => 0.00,
            'premises_costs'    => 0.00,
            'other_expenses'    => 0.00,
        ];
    }
}
