<?php
/**
 * Class ModelModificationHmrcMtd (Catalog)
 *
 * Thin catalog-side model for the HMRC MTD OAuth callback.
 * Provides just enough DB access to validate the state nonce,
 * read credentials, and persist tokens after the OAuth exchange.
 *
 * The full admin model (admin/model/modification/hmrc_mtd.php) handles
 * all obligation and return management from the admin panel.
 *
 * @package NivoCart
 */
class ModelModificationHmrcMtd extends Model {
    /**
     * Read a single setting value.
     */
    public function getSetting(int $store_id, string $key, string $default = ''): string {
        $query = $this->db->query("SELECT value FROM `" . DB_PREFIX . "hmrc_mtd_settings` WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

        return $query->num_rows ? (string)$query->row['value'] : $default;
    }

    /**
     * Write (upsert) a single setting value.
     */
    public function saveSetting(int $store_id, string $key, string $value): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_settings` (store_id, `key`, value) VALUES ('" . (int)$store_id . "', '" . $this->db->escape($key) . "', '" . $this->db->escape($value) . "') ON DUPLICATE KEY UPDATE value = '" . $this->db->escape($value) . "'");
    }

    /**
     * Read all settings for a store as a key-value array.
     */
    public function getSettings(int $store_id): array {
        $query = $this->db->query("SELECT `key`, value FROM `" . DB_PREFIX . "hmrc_mtd_settings` WHERE store_id = '" . (int)$store_id . "'");

        $settings = [];

        foreach ($query->rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    /**
     * Persist OAuth tokens returned by HMRC.
     * Calculates expires_at from the expires_in seconds offset.
     */
    public function saveTokens(int $store_id, string $access_token, string $refresh_token, int $expires_in): void {
        $expires_at = date('Y-m-d H:i:s', time() + $expires_in);

        $this->db->query("INSERT INTO `" . DB_PREFIX . "hmrc_mtd_tokens` (store_id, access_token, refresh_token, expires_at, date_modified)
             VALUES (
                '" . (int)$store_id . "',
                '" . $this->db->escape($access_token) . "',
                '" . $this->db->escape($refresh_token) . "',
                '" . $this->db->escape($expires_at) . "',
                NOW()
             )
             ON DUPLICATE KEY UPDATE
                access_token = '" . $this->db->escape($access_token) . "',
                refresh_token = '" . $this->db->escape($refresh_token) . "',
                expires_at = '" . $this->db->escape($expires_at) . "',
                date_modified = NOW()"
        );
    }
}
