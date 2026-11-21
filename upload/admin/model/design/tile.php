<?php
/**
 * Class ModelDesignTile
 *
 * @package NivoCart
 */
class ModelDesignTile extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addTile(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "tile` SET `name` = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");

		$tile_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_tile_id'] = $tile_id;

		if (isset($data['tile_item'])) {
			$this->addTileItems($tile_id, $data['tile_item']);
		}
	}

	public function editTile(int $tile_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "tile` SET `name` = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "' WHERE tile_id = '" . (int)$tile_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "tile_item` WHERE tile_id = '" . (int)$tile_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tile_item_description` WHERE tile_id = '" . (int)$tile_id . "'");

		if (isset($data['tile_item'])) {
			$this->addTileItems($tile_id, $data['tile_item']);
		}
	}

	public function deleteTile(int $tile_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tile` WHERE tile_id = '" . (int)$tile_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tile_item` WHERE tile_id = '" . (int)$tile_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tile_item_description` WHERE tile_id = '" . (int)$tile_id . "'");
	}

	public function getTile(int $tile_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "tile` WHERE tile_id = '" . (int)$tile_id . "'");

		return $query->row;
	}

	public function getTiles(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "tile`";

		$sort_data = [
			'name',
			'status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTileItems(int $tile_id): array {
		$tile_item_data = [];

		$tile_item_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tile_item` WHERE tile_id = '" . (int)$tile_id . "' ORDER BY sort_order ASC, tile_item_id ASC");

		foreach ($tile_item_query->rows as $tile_item) {
			$tile_item_description_data = [];

			$tile_item_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tile_item_description` WHERE tile_item_id = '" . (int)$tile_item['tile_item_id'] . "' AND tile_id = '" . (int)$tile_id . "'");

			foreach ($tile_item_description_query->rows as $tile_item_description) {
				$tile_item_description_data[$tile_item_description['language_id']] = [
					'title'     => $tile_item_description['title'],
					'text'      => $tile_item_description['text'],
					'link_text' => $tile_item_description['link_text']
				];
			}

			$tile_item_data[] = [
				'tile_item_description' => $tile_item_description_data,
				'image'                 => $tile_item['image'],
				'link'                  => $tile_item['link'],
				'external_link'         => $tile_item['external_link'],
				'layout'                => $tile_item['layout'],
				'align'                 => $tile_item['align'],
				'valign'                => $tile_item['valign'],
				'background'            => $tile_item['background'],
				'colour'                => $tile_item['colour'],
				'sort_order'            => $tile_item['sort_order']
			];
		}

		return $tile_item_data;
	}

	public function getTotalTiles(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "tile`");

		return $query->row['total'];
	}

	public function getTotalItemsByTileId(int $tile_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "tile_item` WHERE tile_id = '" . (int)$tile_id . "'");

		return $query->row['total'];
	}

	/**
	 * Helpers
	 */
	private function addTileItems(int $tile_id, array $tile_items): void {
		$layouts = ['top', 'cover', 'left'];
		$aligns = ['left', 'center', 'right'];
		$valigns = ['top', 'middle', 'bottom'];

		foreach ($tile_items as $tile_item) {
			$layout = (isset($tile_item['layout']) && in_array($tile_item['layout'], $layouts, true)) ? $tile_item['layout'] : 'top';
			$align = (isset($tile_item['align']) && in_array($tile_item['align'], $aligns, true)) ? $tile_item['align'] : 'center';
			$valign = (isset($tile_item['valign']) && in_array($tile_item['valign'], $valigns, true)) ? $tile_item['valign'] : 'middle';

			$background = isset($tile_item['background']) ? strtoupper(ltrim($tile_item['background'], '#')) : '';
			$colour = isset($tile_item['colour']) ? strtoupper(ltrim($tile_item['colour'], '#')) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "tile_item` SET tile_id = '" . (int)$tile_id . "', `image` = '" . $this->db->escape(html_entity_decode($tile_item['image'], ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($tile_item['link']) . "', external_link = '" . (int)$tile_item['external_link'] . "', layout = '" . $this->db->escape($layout) . "', `align` = '" . $this->db->escape($align) . "', valign = '" . $this->db->escape($valign) . "', background = '" . $this->db->escape($background) . "', colour = '" . $this->db->escape($colour) . "', sort_order = '" . (int)$tile_item['sort_order'] . "'");

			$tile_item_id = $this->db->getLastId();

			foreach ($tile_item['tile_item_description'] as $language_id => $tile_item_description) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "tile_item_description` SET tile_item_id = '" . (int)$tile_item_id . "', language_id = '" . (int)$language_id . "', tile_id = '" . (int)$tile_id . "', title = '" . $this->db->escape($tile_item_description['title']) . "', `text` = '" . $this->db->escape($tile_item_description['text']) . "', link_text = '" . $this->db->escape($tile_item_description['link_text']) . "'");
			}
		}
	}
}
