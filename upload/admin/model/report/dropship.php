<?php
/**
 * Class ModelReportDropship
 *
 * Model for the Dropshipping Profit Report.
 * Joins nc_dropship_order → nc_order_product → nc_product_dropship
 * to produce revenue, product cost, and gateway fee figures.
 * Reads nc_ds_cost_config (when available) for overhead proration.
 *
 * Location: admin/model/report/dropship.php
 *
 * @package NivoCart
 */
class ModelReportDropship extends Model {
    /**
     * Return true if the required DS tables all exist.
     */
    public function dsTablesExist(): bool {
        $required = [
            DB_PREFIX . 'dropship_channel',
            DB_PREFIX . 'dropship_order',
            DB_PREFIX . 'product_dropship',
        ];

        foreach ($required as $table) {
            $r = $this->db->query("SHOW TABLES LIKE '" . $table . "'");

            if (!$r->num_rows) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true if the DS Cost Calculator table exists.
     */
    public function costConfigExists(): bool {
        $r = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ds_cost_config'");

        return (bool)$r->num_rows;
    }

    // =====================================================================
    // CHANNELS FOR FILTER DROPDOWN
    // =====================================================================

    /**
     * Return all active DS channels for the filter dropdown.
     */
    public function getChannelsForFilter(): array {
        $query = $this->db->query("SELECT `channel_id`, `name`, `provider` FROM `" . DB_PREFIX . "dropship_channel` WHERE `status` = '1' ORDER BY `name` ASC");

        return $query->rows;
    }

    // =====================================================================
    // MAIN REPORT QUERY
    // =====================================================================

    /**
     * Return profit rows grouped by period (and optionally channel).
     *
     * Each row contains:
     *   date_start, date_end, channel_id, channel_name,
     *   orders (unique order count), revenue, product_cost,
     *   gateway_fees, returns_provision, gross_profit, gross_margin_pct
     *
     * Overhead costs are NOT included per-row — they are calculated
     * separately via getOverheadSummary() and subtracted from gross totals.
     */
    public function getDropshipProfit(array $data = []): array {
        $group = $data['filter_group'] ?? 'week';
        $date_start = $data['filter_date_start'] ?? '';
        $date_end = $data['filter_date_end'] ?? '';
        $status_id = (int)($data['filter_order_status_id'] ?? 0);
        $channel_id = (int)($data['filter_channel_id'] ?? 0);
        $vat_reg = (bool)($data['vat_registered'] ?? false);

        // Base query — one row per DS order line item
        $sql = "
            SELECT
                o.order_id,
                o.date_added,
                o.total AS order_total,
                op.quantity,
                op.price AS sale_price,
                dch.channel_id,
                dch.name AS channel_name,
                pd.supplier_cost,
                pd.vat_rate,
                cc_ch.gateway_fee_pct,
                cc_ch.gateway_fee_fixed,
                cc_ch.returns_pct,
                cc_ch.fx_fee_pct
            FROM `" . DB_PREFIX . "dropship_order` dord
            INNER JOIN `" . DB_PREFIX . "order` o
                ON (dord.order_id = o.order_id)
            INNER JOIN `" . DB_PREFIX . "order_product` op
                ON (dord.order_product_id = op.order_product_id)
            INNER JOIN `" . DB_PREFIX . "dropship_channel` dch
                ON (dord.channel_id = dch.channel_id)
            LEFT JOIN `" . DB_PREFIX . "product_dropship` pd
                ON (op.product_id = pd.product_id AND dord.channel_id = pd.channel_id)
        ";

        // Join cost config only if table exists
        if ($this->costConfigExists()) {
            $sql .= "LEFT JOIN `" . DB_PREFIX . "ds_cost_config` cc_ch ON (cc_ch.channel_id = dord.channel_id)";
        } else {
            // Provide null columns so calculations below still work
            $sql .= "LEFT JOIN (SELECT NULL AS gateway_fee_pct, NULL AS gateway_fee_fixed, NULL AS returns_pct, NULL AS fx_fee_pct, NULL AS channel_id) cc_ch ON (1=0)";
        }

        $sql .= " WHERE 1";

        if ($status_id > 0) {
            $sql .= " AND o.order_status_id = '" . (int)$status_id . "'";
        } else {
            $sql .= " AND o.order_status_id > '0'";
        }

        if (!empty($date_start)) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "'";
        }

        if (!empty($date_end)) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'";
        }

        if ($channel_id > 0) {
            $sql .= " AND dord.channel_id = '" . (int)$channel_id . "'";
        }

        $raw = $this->db->query($sql);

        if (empty($raw->rows)) {
            return [];
        }

        // ---------------------------------------------------------------
        // Group rows in PHP (avoids complex SQL with per-order gateway
        // fee aggregation and VAT-adjusted cost calculation)
        // ---------------------------------------------------------------
        $buckets = [];      // key → aggregated row
        $seen_order = [];   // track order totals already counted (gateway fee per unique order)

        foreach ($raw->rows as $r) {
            $bucket_key = $this->bucketKey($r['date_added'], $group, (int)$r['channel_id']);

            if (!isset($buckets[$bucket_key])) {
                $buckets[$bucket_key] = [
                    'date_added'        => $r['date_added'],
                    'channel_id'        => (int)$r['channel_id'],
                    'channel_name'      => $r['channel_name'],
                    'orders'            => [],
                    'revenue'           => 0.0,
                    'product_cost'      => 0.0,
                    'gateway_fees'      => 0.0,
                    'returns_provision' => 0.0,
                ];
            }

            $b = &$buckets[$bucket_key];

            // Track unique orders within bucket
            $b['orders'][$r['order_id']] = true;

            // Revenue = line item sale price × quantity (customer-paid, inc. any line discounts)
            $line_revenue = (float)$r['sale_price'] * (int)$r['quantity'];
            $b['revenue'] += $line_revenue;

            // Product cost — apply VAT if store is NOT VAT-registered
            $cost_ex = (float)($r['supplier_cost'] ?? 0);

            if (!$vat_reg && !empty($r['vat_rate'])) {
                $cost_ex = $cost_ex * (1 + (float)$r['vat_rate'] / 100);
            }

            // Apply FX fee on top of supplier cost
            $fx_pct = (float)($r['fx_fee_pct'] ?? 0);

            if ($fx_pct > 0) {
                $cost_ex = $cost_ex * (1 + $fx_pct / 100);
            }

            $b['product_cost'] += $cost_ex * (int)$r['quantity'];

            // Returns provision on this line's revenue
            $returns_pct = (float)($r['returns_pct'] ?? 0);
            $b['returns_provision'] += $line_revenue * ($returns_pct / 100);

            // Gateway fees — charged once per order (avoid double-counting)
            $order_bucket_key = $bucket_key . '_' . $r['order_id'];

            if (!isset($seen_order[$order_bucket_key])) {
                $seen_order[$order_bucket_key] = true;
                $order_total = (float)$r['order_total'];
                $gw_pct = (float)($r['gateway_fee_pct'] ?? 0);
                $gw_fixed = (float)($r['gateway_fee_fixed'] ?? 0);
                $b['gateway_fees'] += ($order_total * $gw_pct / 100) + $gw_fixed;
            }

            unset($b);
        }

        // ---------------------------------------------------------------
        // Build final result rows
        // ---------------------------------------------------------------
        $results = [];

        foreach ($buckets as $bucket) {
            $revenue = round($bucket['revenue'], 4, PHP_ROUND_HALF_UP);

            $product_cost = round($bucket['product_cost'], 4, PHP_ROUND_HALF_UP);
            $gateway_fees = round($bucket['gateway_fees'], 4, PHP_ROUND_HALF_UP);
            $returns_prov = round($bucket['returns_provision'], 4, PHP_ROUND_HALF_UP);

            $gross_profit = $revenue - $product_cost - $gateway_fees - $returns_prov;
            $gross_margin_pct = ($revenue > 0) ? ($gross_profit / $revenue) * 100 : 0;

            [$date_start_out, $date_end_out] = $this->bucketDates($bucket['date_added'], $group);

            $results[] = [
                'date_start'        => $date_start_out,
                'date_end'          => $date_end_out,
                'channel_id'        => $bucket['channel_id'],
                'channel_name'      => $bucket['channel_name'],
                'orders'            => count($bucket['orders']),
                'revenue'           => $revenue,
                'product_cost'      => $product_cost,
                'gateway_fees'      => $gateway_fees,
                'returns_provision' => $returns_prov,
                'gross_profit'      => $gross_profit,
                'gross_margin_pct'  => round($gross_margin_pct, 2, PHP_ROUND_HALF_UP)
            ];
        }

        // Sort descending by date_start
        usort($results, fn($a, $b) => strcmp($b['date_start'], $a['date_start']));

        // Pagination
        if (isset($data['start'], $data['limit'])) {
            $results = array_slice($results, max(0, (int)$data['start']), max(1, (int)$data['limit']));
        }

        return $results;
    }

    /**
     * Return the total count of grouped rows (for pagination).
     */
    public function getTotalDropshipProfit(array $data = []): int {
        // Re-run without pagination to count buckets
        $data_all = $data;

        unset($data_all['start'], $data_all['limit']);

        return count($this->getDropshipProfit($data_all));
    }

    // =====================================================================
    // OVERHEAD SUMMARY
    // =====================================================================

    /**
     * Calculate all prorated overhead costs for the given date range.
     * Returns an array of named cost components + a total.
     * All values are zero if the DS Cost Calculator table does not exist.
     */
    public function getOverheadSummary(array $data = []): array {
        $zero = [
            'hosting'      => 0.0,
            'domain'       => 0.0,
            'tools'        => 0.0,
            'platform'     => 0.0,
            'advertising'  => 0.0,
            'chargeback'   => 0.0,
            'other'        => 0.0,
            'total'        => 0.0,
        ];

        if (!$this->costConfigExists()) {
            return $zero;
        }

        $date_start = $data['filter_date_start'] ?? date('Y-m-d');
        $date_end = $data['filter_date_end'] ?? date('Y-m-d');
        $channel_id = (int)($data['filter_channel_id'] ?? 0);

        // Days in period
        $days = max(1, (int)((strtotime($date_end) - strtotime($date_start)) / 86400) + 1);

        // Global costs (channel_id = 0)
        $global_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ds_cost_config` WHERE `channel_id` = '0' LIMIT 1");

        $global = $global_query->row ?? [];

        $hosting_share = ((float)($global['hosting_monthly'] ?? 0)) * ($days / 30.44);
        $domain_share = ((float)($global['domain_annual'] ?? 0)) / 365 * $days;
        $tools_share = ((float)($global['tools_annual'] ?? 0)) / 365 * $days;
        $other_share = ((float)($global['other_monthly'] ?? 0)) * ($days / 30.44);

        // Revenue total for chargeback % calculation
        $revenue_total = $this->getTotalRevenue($data);
        $chargeback_share = $revenue_total * ((float)($global['chargeback_pct'] ?? 0) / 100);

        // Per-channel costs
        if ($channel_id > 0) {
            $ch_sql = "SELECT * FROM `" . DB_PREFIX . "ds_cost_config` WHERE `channel_id` = '" . (int)$channel_id . "' LIMIT 1";
        } else {
            $ch_sql = "SELECT * FROM `" . DB_PREFIX . "ds_cost_config` WHERE `channel_id` > '0'";
        }

        $ch_query = $this->db->query($ch_sql);

        $platform_share = 0.0;
        $advertising_share = 0.0;

        foreach ($ch_query->rows as $ch) {
            $platform_share += ((float)($ch['platform_monthly'] ?? 0)) * ($days / 30.44);
            $advertising_share += ((float)($ch['advertising_monthly'] ?? 0)) * ($days / 30.44);
        }

        $total = $hosting_share + $domain_share + $tools_share + $platform_share + $advertising_share + $chargeback_share + $other_share;

        return [
            'hosting'     => round($hosting_share, 4, PHP_ROUND_HALF_UP),
            'domain'      => round($domain_share, 4, PHP_ROUND_HALF_UP),
            'tools'       => round($tools_share, 4, PHP_ROUND_HALF_UP),
            'platform'    => round($platform_share, 4, PHP_ROUND_HALF_UP),
            'advertising' => round($advertising_share, 4, PHP_ROUND_HALF_UP),
            'chargeback'  => round($chargeback_share, 4, PHP_ROUND_HALF_UP),
            'other'       => round($other_share, 4, PHP_ROUND_HALF_UP),
            'total'       => round($total, 4, PHP_ROUND_HALF_UP),
        ];
    }

    /**
     * Return the total revenue for the given filters (for chargeback % base).
     */
    private function getTotalRevenue(array $data): float {
        $status_id = (int)($data['filter_order_status_id'] ?? 0);
        $channel_id = (int)($data['filter_channel_id'] ?? 0);
        $date_start = $data['filter_date_start'] ?? '';
        $date_end = $data['filter_date_end'] ?? '';

        $sql = "
            SELECT SUM(op.price * op.quantity) AS revenue
            FROM `" . DB_PREFIX . "dropship_order` dord
            INNER JOIN `" . DB_PREFIX . "order` o ON (dord.order_id = o.order_id)
            INNER JOIN `" . DB_PREFIX . "order_product` op ON (dord.order_product_id = op.order_product_id)
            WHERE 1
        ";

        if ($status_id > 0) {
            $sql .= " AND o.order_status_id = '" . (int)$status_id . "'";
        } else {
            $sql .= " AND o.order_status_id > '0'";
        }

        if (!empty($date_start)) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "'";
        }

        if (!empty($date_end)) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'";
        }

        if ($channel_id > 0) {
            $sql .= " AND dord.channel_id = '" . (int)$channel_id . "'";
        }

        $q = $this->db->query($sql);

        return (float)($q->row['revenue'] ?? 0);
    }

    /**
     * Return the global VAT registration flag from nc_ds_cost_config.
     */
    public function isVatRegistered(): bool {
        if (!$this->costConfigExists()) {
            return false;
        }

        $q = $this->db->query("SELECT `vat_registered` FROM `" . DB_PREFIX . "ds_cost_config` WHERE `channel_id` = '0' LIMIT 1");

        return (bool)($q->row['vat_registered'] ?? false);
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    /**
     * Build a string key that uniquely identifies a time bucket + channel.
     */
    private function bucketKey(string $date_added, string $group, int $channel_id): string {
        $ts = strtotime($date_added);

        $time_key = match($group) {
            'day'   => date('Y-m-d', $ts),
            'month' => date('Y-m', $ts),
            'year'  => date('Y', $ts),
            default => date('Y', $ts) . '-W' . date('W', $ts),
        };

        return $time_key . '_ch' . $channel_id;
    }

    /**
     * Return [date_start, date_end] strings for a bucket based on the
     * date of any record within it and the group type.
     */
    private function bucketDates(string $date_added, string $group): array {
        $ts = strtotime($date_added);

        return match($group) {
            'day'   => [date('Y-m-d', $ts), date('Y-m-d', $ts)],
            'month' => [
                date('Y-m-01', $ts),
                date('Y-m-t', $ts),
            ],
            'year'  => [
                date('Y-01-01', $ts),
                date('Y-12-31', $ts),
            ],
            default => [
                date('Y-m-d', strtotime('monday this week', $ts)),
                date('Y-m-d', strtotime('sunday this week', $ts)),
            ],
        };
    }
}
