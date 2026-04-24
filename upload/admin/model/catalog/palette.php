<?php
/**
 * Class ModelCatalogPalette
 *
 * @package NivoCart
 */
class ModelCatalogPalette extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addPalette(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "palette` SET `name` = '" . $this->db->escape($data['name']) . "'");

		$palette_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_palette_id'] = $palette_id;

		if (isset($data['palette_color'])) {
			foreach ($data['palette_color'] as $palette_color) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color` SET palette_id = '" . (int)$palette_id . "', color = '" . $this->db->escape($palette_color['color']) . "', skin = '" . $this->db->escape($palette_color['skin']) . "'");

				$palette_color_id = $this->db->getLastId();

				foreach ($palette_color['color_description'] as $language_id => $color_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color_description` SET palette_color_id = '" . (int)$palette_color_id . "', language_id = '" . (int)$language_id . "', palette_id = '" . (int)$palette_id . "', `title` = '" .  $this->db->escape($color_description['title']) . "'");
				}
			}
		}

		$this->cache->delete('palette.total');
	}

	public function editPalette(int $palette_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "palette` SET `name` = '" . $this->db->escape($data['name']) . "' WHERE palette_id = '" . (int)$palette_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color` WHERE palette_id = '" . (int)$palette_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color_description` WHERE palette_id = '" . (int)$palette_id . "'");

		if (isset($data['palette_color'])) {
			foreach ($data['palette_color'] as $palette_color) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color` SET palette_id = '" . (int)$palette_id . "', color = '" . $this->db->escape($palette_color['color']) . "', skin = '" . $this->db->escape($palette_color['skin']) . "'");

				$palette_color_id = $this->db->getLastId();

				foreach ($palette_color['color_description'] as $language_id => $color_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color_description` SET palette_color_id = '" . (int)$palette_color_id . "', language_id = '" . (int)$language_id . "', palette_id = '" . (int)$palette_id . "', `title` = '" .  $this->db->escape($color_description['title']) . "'");
				}
			}
		}

		$this->cache->delete('palette.total');
	}

	public function deletePalette(int $palette_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette` WHERE palette_id = '" . (int)$palette_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color` WHERE palette_id = '" . (int)$palette_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color_description` WHERE palette_id = '" . (int)$palette_id . "'");

		$this->cache->delete('palette.total');
	}

	public function getPalette(int $palette_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "palette` p LEFT JOIN `" . DB_PREFIX . "palette_color` pc ON (pc.palette_id = p.palette_id) LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (pcd.palette_id = p.palette_id) WHERE pcd.palette_id = '" . (int)$palette_id . "' AND pcd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.name");

		return $query->row;
	}

	public function getPalettes(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "palette` p";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE p.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY p.palette_id";

		$sort_data = ['p.name'];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY p.name";
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

	public function getTotalPalettes(array $data = []): int {
		$sql = "SELECT COUNT(p.palette_id) AS `total` FROM `" . DB_PREFIX . "palette` p";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE p.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getPaletteDescriptions(int $palette_id): array {
		$color_data = [];

		$color_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette_color` WHERE palette_id = '" . (int)$palette_id . "'");

		foreach ($color_query->rows as $color) {
			$color_description_data = [];

			$color_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette_color_description` WHERE palette_color_id = '" . (int)$color['palette_color_id'] . "'");

			foreach ($color_description_query->rows as $color_description) {
				$color_description_data[$color_description['language_id']] = ['title' => $color_description['title']];
			}

			$color_data[] = [
				'palette_color_id'  => $color['palette_color_id'],
				'color'             => $color['color'],
				'skin'              => $color['skin'],
				'color_description' => $color_description_data
			];
		}

		return $color_data;
	}

	public function getPaletteColors(array $data = []): array {
		$colors_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette_color` pc LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (pcd.palette_id = pc.palette_id) WHERE pcd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pcd.palette_color_id ORDER BY pc.palette_id, pcd.title ASC");

		foreach ($query->rows as $result) {
			$colors_data[] = [
				'palette_color_id' => $result['palette_color_id'],
				'color'            => $result['color'],
				'skin'             => $result['skin'],
				'title'            => $result['title']
			];
		}

		return $colors_data;
	}

	public function getPaletteColorsByPaletteId(int $palette_id): array {
		$colors_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette` p LEFT JOIN `" . DB_PREFIX . "palette_color` pc ON (pc.palette_id = p.palette_id) LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (pcd.palette_id = p.palette_id) WHERE pcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pcd.palette_id = '" . (int)$palette_id . "' GROUP BY pcd.palette_color_id ORDER BY p.palette_id, pcd.title ASC");

		foreach ($query->rows as $result) {
			$colors_data[] = [
				'palette_color_id' => $result['palette_color_id'],
				'color'            => $result['color'],
				'skin'             => $result['skin'],
				'title'            => $result['title']
			];
		}

		return $colors_data;
	}

	public function getPaletteColorsByColorId(int $palette_color_id): array {
		$palette_colors_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette_color` pc LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (pcd.palette_color_id = pc.palette_color_id) WHERE pcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pc.palette_color_id = '" . (int)$palette_color_id . "' GROUP BY pc.palette_color_id ORDER BY pcd.title ASC");

		foreach ($query->rows as $result) {
			$palette_colors_data[] = [
				'palette_color_id' => $result['palette_color_id'],
				'color'            => $result['color'],
				'skin'             => $result['skin'],
				'title'            => $result['title']
			];
		}

		return $palette_colors_data;
	}

	public function getPaletteIds(array $data = []): array {
		$palette_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette`");

		foreach ($query->rows as $result) {
			$palette_data[] = ['palette_id' => $result['palette_id']];
		}

		return $palette_data;
	}

	public function getPaletteColor(int $palette_color_id) {
		$query = $this->db->query("SELECT DISTINCT color FROM `" . DB_PREFIX . "palette_color` WHERE palette_color_id = '" . (int)$palette_color_id . "'");

		return $query->row['color'];
	}

	public function getPaletteName(int $palette_id) {
		$query = $this->db->query("SELECT DISTINCT `name` FROM `" . DB_PREFIX . "palette` WHERE palette_id = '" . (int)$palette_id . "'");

		return $query->row['name'];
	}

	public function getTotalColorsByPaletteId(int $palette_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "palette_color` WHERE palette_id = '" . (int)$palette_id . "'");

		return $query->row['total'];
	}
}
