<?php
/**
 * Class ModelModificationDsCostCalculator
 *
 * Model for the DS Cost Calculator modification.
 * Reads and writes to nc_ds_cost_config (channel_id = 0 for global row).
 *
 * Location: admin/model/modification/ds_cost_calculator.php
 *
 * @package NivoCart
 */
class ModelModificationDsCostCalculator extends Model {
    /**
     * Return a single cost config row by channel_id.
     * channel_id = 0 → global row.
     * Returns an array of zeros/defaults if the row does not yet exist.
     */
    public function getConfig(int $channel_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ds_cost_config` WHERE `channel_id` = '" . (int)$channel_id . "' LIMIT 1");

        if ($query->row) {
            return $query->row;
        }

        // Return safe defaults so templates never need to check for null
        return $this->defaults($channel_id);
    }

    /**
     * Return all config rows (global + all per-channel rows).
     */
    public function getAllConfigs(): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ds_cost_config` ORDER BY `channel_id` ASC");

        return $query->rows;
    }

    /**
     * Insert or update a cost config row.
     * Uses INSERT … ON DUPLICATE KEY UPDATE on the UNIQUE channel_id key.
     */
    public function saveConfig(int $channel_id, array $data): void {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "ds_cost_config`
                (`channel_id`, `hosting_monthly`, `domain_annual`, `tools_annual`,
                 `platform_monthly`, `advertising_monthly`,
                 `gateway_fee_pct`, `gateway_fee_fixed`,
                 `fx_fee_pct`, `returns_pct`, `chargeback_pct`,
                 `vat_registered`,
                 `other_monthly`, `other_description`, `date_modified`)
            VALUES (
                '" . (int)$channel_id . "',
                '" . (float)($data['hosting_monthly'] ?? 0) . "',
                '" . (float)($data['domain_annual'] ?? 0) . "',
                '" . (float)($data['tools_annual'] ?? 0) . "',
                '" . (float)($data['platform_monthly'] ?? 0) . "',
                '" . (float)($data['advertising_monthly'] ?? 0) . "',
                '" . (float)($data['gateway_fee_pct'] ?? 0) . "',
                '" . (float)($data['gateway_fee_fixed'] ?? 0) . "',
                '" . (float)($data['fx_fee_pct'] ?? 0) . "',
                '" . (float)($data['returns_pct'] ?? 0) . "',
                '" . (float)($data['chargeback_pct'] ?? 0) . "',
                '" . (int)($data['vat_registered'] ?? 0) . "',
                '" . (float)($data['other_monthly'] ?? 0) . "',
                '" . $this->db->escape($data['other_description'] ?? '') . "',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `hosting_monthly` = VALUES(`hosting_monthly`),
                `domain_annual` = VALUES(`domain_annual`),
                `tools_annual` = VALUES(`tools_annual`),
                `platform_monthly` = VALUES(`platform_monthly`),
                `advertising_monthly` = VALUES(`advertising_monthly`),
                `gateway_fee_pct` = VALUES(`gateway_fee_pct`),
                `gateway_fee_fixed` = VALUES(`gateway_fee_fixed`),
                `fx_fee_pct` = VALUES(`fx_fee_pct`),
                `returns_pct` = VALUES(`returns_pct`),
                `chargeback_pct` = VALUES(`chargeback_pct`),
                `vat_registered` = VALUES(`vat_registered`),
                `other_monthly` = VALUES(`other_monthly`),
                `other_description` = VALUES(`other_description`),
                `date_modified` = NOW()
        ");
    }

    // =====================================================================
    // CHANNEL HELPERS
    // =====================================================================

    /**
     * Return all active dropship channels (from nc_dropship_channel).
     * Used to build the per-channel cost panels.
     * Returns an empty array if the table does not exist yet.
     */
    public function getChannels(): array {
        $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "dropship_channel'");

        if (!$check->num_rows) {
            return [];
        }

        $query = $this->db->query("SELECT `channel_id`, `name`, `provider` FROM `" . DB_PREFIX . "dropship_channel` WHERE `status` = '1' ORDER BY `name` ASC");

        return $query->rows;
    }

    /**
     * Check whether the nc_ds_cost_config table exists.
     * Used by the controller's DB gate.
     */
    public function tableExists(): bool {
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ds_cost_config'");

        return (bool)$result->num_rows;
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    /**
     * Return a zero-filled defaults array for a given channel_id.
     */
    private function defaults(int $channel_id): array {
        return [
            'cost_config_id'      => 0,
            'channel_id'          => $channel_id,
            'hosting_monthly'     => '0.0000',
            'domain_annual'       => '0.0000',
            'tools_annual'        => '0.0000',
            'platform_monthly'    => '0.0000',
            'advertising_monthly' => '0.0000',
            'gateway_fee_pct'     => '0.0000',
            'gateway_fee_fixed'   => '0.0000',
            'fx_fee_pct'          => '0.0000',
            'returns_pct'         => '0.0000',
            'chargeback_pct'      => '0.0000',
            'vat_registered'      => 0,
            'other_monthly'       => '0.0000',
            'other_description'   => '',
            'date_modified'       => ''
        ];
    }
}
