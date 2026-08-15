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
	 * Get the record of the setting records in the database.
	 *
	 * @param int $store_id
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $settings = $this->model_setting_setting->getSettings($store_id);
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
	 * Get Value
	 *
	 * @param string $key
	 * @param int    $store_id
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $value = $this->model_setting_setting->getValue($key, $store_id);
	 */
	public function getValue(string $key, int $store_id = 0): string {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return '';
		}
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

		$skins[] = ['skin' => 'white','color' => '#FFFFFF','title' => 'White'];
		$skins[] = ['skin' => 'beige','color' => '#F5F5DC','title' => 'Beige'];
		$skins[] = ['skin' => 'ash','color' => '#E5E5D0','title' => 'Ash'];
		$skins[] = ['skin' => 'silver','color' => '#C2C2C2','title' => 'Silver'];
		$skins[] = ['skin' => 'grey','color' => '#808080','title' => 'Grey'];
		$skins[] = ['skin' => 'charcoal','color' => '#36454F','title' => 'Charcoal'];
		$skins[] = ['skin' => 'black','color' => '#000000','title' => 'Black'];
		$skins[] = ['skin' => 'pistachio','color' => '#93C572','title' => 'Pistachio'];
		$skins[] = ['skin' => 'lime','color' => '#A4C400','title' => 'Lime'];
		$skins[] = ['skin' => 'green','color' => '#60A917','title' => 'Green'];
		$skins[] = ['skin' => 'emerald','color' => '#008A00','title' => 'Emerald'];
		$skins[] = ['skin' => 'teal','color' => '#00ABA9','title' => 'Teal'];
		$skins[] = ['skin' => 'cyan','color' => '#1BA1E2','title' => 'Cyan'];
		$skins[] = ['skin' => 'cobalt','color' => '#0000FF','title' => 'Cobalt'];
		$skins[] = ['skin' => 'navy','color' => '#000084','title' => 'Navy'];
		$skins[] = ['skin' => 'indigo','color' => '#6A00FF','title' => 'Indigo'];
		$skins[] = ['skin' => 'violet','color' => '#AA00FF','title' => 'Violet'];
		$skins[] = ['skin' => 'pink','color' => '#F472D0','title' => 'Pink'];
		$skins[] = ['skin' => 'magenta','color' => '#D80073','title' => 'Magenta'];
		$skins[] = ['skin' => 'crimson','color' => '#A20025','title' => 'Crimson'];
		$skins[] = ['skin' => 'red','color' => '#E51400','title' => 'Red'];
		$skins[] = ['skin' => 'orange','color' => '#FA6800','title' => 'Orange'];
		$skins[] = ['skin' => 'amber','color' => '#F0A30A','title' => 'Amber'];
		$skins[] = ['skin' => 'citrus','color' => '#FFF033','title' => 'Citrus'];
		$skins[] = ['skin' => 'yellow','color' => '#E3C800','title' => 'Yellow'];
		$skins[] = ['skin' => 'brown','color' => '#825A2C','title' => 'Brown'];
		$skins[] = ['skin' => 'olive','color' => '#6D8759','title' => 'Olive'];
		$skins[] = ['skin' => 'steel','color' => '#647687','title' => 'Steel'];
		$skins[] = ['skin' => 'mauve','color' => '#76608A','title' => 'Mauve'];
		$skins[] = ['skin' => 'sienna','color' => '#B77733','title' => 'Sienna'];
		$skins[] = ['skin' => 'mist','color' => '#F2F2F2','title' => 'Mist'];
		$skins[] = ['skin' => 'clear','color' => 'transparent','title' => 'Clear'];

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
