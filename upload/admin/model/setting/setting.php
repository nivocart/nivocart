<?php
class ModelSettingSetting extends Model {
	/**
	 * Get Settings
	 *
	 * Get the record of the setting records in the database.
	 *
	 * @param int $store_id
	 *
	 * @return array<int, array<string, mixed>> setting records that have store ID
	 *
	 * @example
	 *
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
	 *
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
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->editSetting($group, $data, $store_id);
	 */
	public function editSetting(string $group, array $data = [], int $store_id = 0): void {
		$this->deleteSetting($group, $store_id);

		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($group)) == $group) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(!is_array($value) ? $value : json_encode($value)) . "', `serialized` = '" . (bool)is_array($value) . "'");
			}
		}
	}

	/**
	 * Edit Setting Value
	 *
	 * Edit setting value record in the database.
	 *
	 * @param string              $group
	 * @param string              $key
	 * @param array<mixed>|string $value
	 * @param int                 $store_id
	 *
	 * @return void
	 *
	 * @example
	 *
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
	 *
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
	 *
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
	 *
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
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->getColors();
	 */
	public function getColors(): array {
		$skins = [];

		$skins[] = array('skin' => 'white','color' => '#FFFFFF','title' => 'White');
		$skins[] = array('skin' => 'beige','color' => '#F5F5DC','title' => 'Beige');
		$skins[] = array('skin' => 'ash','color' => '#E5E5D0','title' => 'Ash');
		$skins[] = array('skin' => 'silver','color' => '#C2C2C2','title' => 'Silver');
		$skins[] = array('skin' => 'grey','color' => '#808080','title' => 'Grey');
		$skins[] = array('skin' => 'charcoal','color' => '#36454F','title' => 'Charcoal');
		$skins[] = array('skin' => 'black','color' => '#000000','title' => 'Black');
		$skins[] = array('skin' => 'pistachio','color' => '#93C572','title' => 'Pistachio');
		$skins[] = array('skin' => 'lime','color' => '#A4C400','title' => 'Lime');
		$skins[] = array('skin' => 'green','color' => '#60A917','title' => 'Green');
		$skins[] = array('skin' => 'emerald','color' => '#008A00','title' => 'Emerald');
		$skins[] = array('skin' => 'teal','color' => '#00ABA9','title' => 'Teal');
		$skins[] = array('skin' => 'cyan','color' => '#1BA1E2','title' => 'Cyan');
		$skins[] = array('skin' => 'cobalt','color' => '#0000FF','title' => 'Cobalt');
		$skins[] = array('skin' => 'navy','color' => '#000084','title' => 'Navy');
		$skins[] = array('skin' => 'indigo','color' => '#6A00FF','title' => 'Indigo');
		$skins[] = array('skin' => 'violet','color' => '#AA00FF','title' => 'Violet');
		$skins[] = array('skin' => 'pink','color' => '#F472D0','title' => 'Pink');
		$skins[] = array('skin' => 'magenta','color' => '#D80073','title' => 'Magenta');
		$skins[] = array('skin' => 'crimson','color' => '#A20025','title' => 'Crimson');
		$skins[] = array('skin' => 'red','color' => '#E51400','title' => 'Red');
		$skins[] = array('skin' => 'orange','color' => '#FA6800','title' => 'Orange');
		$skins[] = array('skin' => 'amber','color' => '#F0A30A','title' => 'Amber');
		$skins[] = array('skin' => 'citrus','color' => '#FFF033','title' => 'Citrus');
		$skins[] = array('skin' => 'yellow','color' => '#E3C800','title' => 'Yellow');
		$skins[] = array('skin' => 'brown','color' => '#825A2C','title' => 'Brown');
		$skins[] = array('skin' => 'olive','color' => '#6D8759','title' => 'Olive');
		$skins[] = array('skin' => 'steel','color' => '#647687','title' => 'Steel');
		$skins[] = array('skin' => 'mauve','color' => '#76608A','title' => 'Mauve');
		$skins[] = array('skin' => 'sienna','color' => '#B77733','title' => 'Sienna');
		$skins[] = array('skin' => 'mist','color' => '#F2F2F2','title' => 'Mist');
		$skins[] = array('skin' => 'clear','color' => 'transparent','title' => 'Clear');

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
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $this->model_setting_setting->getShapes();
	 */
	public function getShapes(): array {
		$shapes = [];

		$shapes[] = array('shape' => 'rounded-0','title' => 'Square');
		$shapes[] = array('shape' => 'rounded-3','title' => 'Round 3');
		$shapes[] = array('shape' => 'rounded-5','title' => 'Round 5');
		$shapes[] = array('shape' => 'rounded-7','title' => 'Round 7');
		$shapes[] = array('shape' => 'rounded-9','title' => 'Round 9');
		$shapes[] = array('shape' => 'rounded-11','title' => 'Round 11');

		return $shapes;
	}
}
