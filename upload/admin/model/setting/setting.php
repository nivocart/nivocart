<?php
/**
 * Class ModelSettingSetting
 *
 * @package NivoCart
 */
class ModelSettingSetting extends Model {
	/**
	 * Get Settings
	 *
	 * @param int $store_id
	 *
	 * @return array<int, array<string, mixed>> setting records that have store ID
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $results = $this->model_setting_setting->getSettings($store_id);
	 */
	public function getSettings(int $store_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' OR `store_id` = '0' ORDER BY `store_id` ASC");

		return $query->rows;
	}

	/**
	 * Get Setting
	 *
	 * @param string $group
	 * @param int    $store_id
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $setting_info = $this->model_setting_setting->getSetting($group, $store_id);
	 */
	public function getSetting(string $group, int $store_id = 0): array {
		$setting_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$setting_data[$result['key']] = $result['value'];
			} else {
				$setting_data[$result['key']] = $result['value'] ? json_decode($result['value'], true) : [];
			}
		}

		return $setting_data;
	}

	/**
	 * Edit Setting
	 *
	 * @param string               $group
	 * @param array<string, mixed> $data     array of data
	 * @param int                  $store_id
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->editSetting($group, $data, $store_id);
	 */
	public function editSetting(string $group, array $data = [], int $store_id = 0): void {
		$this->deleteSetting($group, $store_id);

		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($group)) === $group) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(!is_array($value) ? $value : json_encode($value)) . "', `serialized` = '" . (bool)is_array($value) . "'");
			}
		}
	}

	/**
	 * Merge Settings
	 *
	 * Upserts a partial array of settings without touching other keys in the group.
	 * Use this instead of editSetting() when only a subset of settings is being saved.
	 *
	 * @param string               $group
	 * @param array<string, mixed> $data
	 * @param int                  $store_id
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->mergeSettings('config', $data);
	 */
	public function mergeSettings(string $group, array $data = [], int $store_id = 0): void {
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($group)) !== $group) {
				continue;
			}

			$serialized = is_array($value);
			$encoded = $serialized ? json_encode($value) : $value;

			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES ('" . (int)$store_id . "', '" . $this->db->escape($group) . "', '" . $this->db->escape($key) . "', '" . $this->db->escape($encoded) . "', '" . (int)$serialized . "') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `serialized` = VALUES(`serialized`)");
		}
	}

	/**
	 * Edit Setting Value
	 *
	 * @param string              $group
	 * @param string              $key
	 * @param array<mixed>|string $value
	 * @param int                 $store_id
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->editValue($group, $key, $value, $store_id);
	 */
	public function editSettingValue(string $group = '', string $key = '', $value = '', int $store_id = 0): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape(!is_array($value) ? $value : json_encode($value)) . "', `serialized` = '" . (bool)is_array($value) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND `store_id` = '" . (int)$store_id . "'");
	}

	/**
	 * Delete Setting
	 *
	 * @param string $group
	 * @param int    $store_id
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->deleteSetting($group, $store_id);
	 */
	public function deleteSetting(string $group, int $store_id = 0): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
	}

	/**
	 * Delete Settings By Group
	 *
	 * @param string $group
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->deleteSettingsByGroup($group);
	 */
	public function deleteSettingsByGroup(string $group): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group` = '" . $this->db->escape($code) . "'");
	}

	/**
	 * Delete Settings By Store ID
	 *
	 * @param int $store_id
	 *
	 * @return void
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->deleteSettingsByStoreId($store_id);
	 */
	public function deleteSettingsByStoreId(int $store_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "'");
	}

	/**
	 * Get Colors
	 *
	 * Set of Colours for themes, used by Modules.
	 *
	 * @return array
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->getColors();
	 */
	public function getColors(): array {
		$skins = [];

		// Neutrals — light to dark
		$skins[] = ['skin' => 'white', 'color' => '#FFFFFF', 'title' => 'White'];
		$skins[] = ['skin' => 'mist', 'color' => '#F2F2F2', 'title' => 'Mist'];
		$skins[] = ['skin' => 'beige', 'color' => '#F5F0E8', 'title' => 'Beige'];
		$skins[] = ['skin' => 'ash', 'color' => '#E5E5D0', 'title' => 'Ash'];
		$skins[] = ['skin' => 'silver', 'color' => '#BDC3C7', 'title' => 'Silver'];
		$skins[] = ['skin' => 'grey', 'color' => '#6B7280', 'title' => 'Grey'];
		$skins[] = ['skin' => 'steel', 'color' => '#647687', 'title' => 'Steel'];
		$skins[] = ['skin' => 'charcoal', 'color' => '#36454F', 'title' => 'Charcoal'];
		$skins[] = ['skin' => 'black', 'color' => '#000000', 'title' => 'Black'];

		// Reds
		$skins[] = ['skin' => 'red', 'color' => '#EF4444', 'title' => 'Red'];
		$skins[] = ['skin' => 'crimson', 'color' => '#9F1239', 'title' => 'Crimson'];

		// Oranges & browns
		$skins[] = ['skin' => 'orange', 'color' => '#F97316', 'title' => 'Orange'];
		$skins[] = ['skin' => 'sienna', 'color' => '#B77733', 'title' => 'Sienna'];
		$skins[] = ['skin' => 'brown', 'color' => '#825A2C', 'title' => 'Brown'];

		// Ambers & yellows
		$skins[] = ['skin' => 'amber', 'color' => '#F59E0B', 'title' => 'Amber'];
		$skins[] = ['skin' => 'citrus', 'color' => '#FACC15', 'title' => 'Citrus'];
		$skins[] = ['skin' => 'yellow', 'color' => '#EAB308', 'title' => 'Yellow'];

		// Greens
		$skins[] = ['skin' => 'lime', 'color' => '#84CC16', 'title' => 'Lime'];
		$skins[] = ['skin' => 'pistachio', 'color' => '#93C572', 'title' => 'Pistachio'];
		$skins[] = ['skin' => 'olive', 'color' => '#6D8759', 'title' => 'Olive'];
		$skins[] = ['skin' => 'green', 'color' => '#16A34A', 'title' => 'Green'];
		$skins[] = ['skin' => 'emerald', 'color' => '#059669', 'title' => 'Emerald'];

		// Teals & cyans
		$skins[] = ['skin' => 'teal', 'color' => '#0D9488', 'title' => 'Teal'];
		$skins[] = ['skin' => 'cyan', 'color' => '#0EA5E9', 'title' => 'Cyan'];

		// Blues
		$skins[] = ['skin' => 'cobalt', 'color' => '#0050EF', 'title' => 'Cobalt'];
		$skins[] = ['skin' => 'navy', 'color' => '#1E3A5F', 'title' => 'Navy'];

		// Indigos & violets
		$skins[] = ['skin' => 'indigo', 'color' => '#6366F1', 'title' => 'Indigo'];
		$skins[] = ['skin' => 'violet', 'color' => '#8B5CF6', 'title' => 'Violet'];
		$skins[] = ['skin' => 'mauve', 'color' => '#76608A', 'title' => 'Mauve'];

		// Pinks & magentas
		$skins[] = ['skin' => 'magenta', 'color' => '#BE185D', 'title' => 'Magenta'];
		$skins[] = ['skin' => 'pink', 'color' => '#EC4899', 'title' => 'Pink'];

		// Special
		$skins[] = ['skin' => 'clear', 'color' => 'transparent', 'title' => 'Clear'];

		return $skins;
	}

	/**
	 * Get Shapes
	 *
	 * Set of Shapes for themes, used by Modules.
	 *
	 * @return array
	 *
	 * @example
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->getShapes();
	 */
	public function getShapes(): array {
		$shapes = [];

		$shapes[] = ['shape' => 'rounded-0','title' => 'Square'];
		$shapes[] = ['shape' => 'rounded-3','title' => 'Round 3'];
		$shapes[] = ['shape' => 'rounded-5','title' => 'Round 5'];
		$shapes[] = ['shape' => 'rounded-7','title' => 'Round 7'];
		$shapes[] = ['shape' => 'rounded-9','title' => 'Round 9'];
		$shapes[] = ['shape' => 'rounded-11','title' => 'Round 11'];

		return $shapes;
	}
}
