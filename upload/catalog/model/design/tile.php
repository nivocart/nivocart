<?php
/**
 * Class ModelDesignTile
 *
 * @package NivoCart
 */
class ModelDesignTile extends Model {
	/**
	 * Functions Get
	 */
	public function getTile(int $tile_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tile_item` ti LEFT JOIN `" . DB_PREFIX . "tile_item_description` tid ON (ti.tile_item_id = tid.tile_item_id) LEFT JOIN `" . DB_PREFIX . "tile` t ON (ti.tile_id = t.tile_id) WHERE ti.tile_id = '" . (int)$tile_id . "' AND t.status = '1' AND tid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ti.sort_order ASC, ti.tile_item_id ASC LIMIT 12");

		return $query->rows;
	}
}
