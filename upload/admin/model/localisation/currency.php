<?php
/**
 * Class ModelLocalisationCurrency
 *
 * @package NivoCart
 */
class ModelLocalisationCurrency extends Model {
	/**
	 * Functions Add, Edit, Delete, Get, Check
	 */
	public function addCurrency(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "currency` SET title = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', symbol_left = '" . $this->db->escape($data['symbol_left']) . "', symbol_right = '" . $this->db->escape($data['symbol_right']) . "', decimal_place = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . $this->db->escape($data['value']) . "', status = '" . (int)$data['status'] . "', date_modified = NOW()");

		$currency_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_currency_id'] = $currency_id;

		if ($this->config->get('config_currency_auto')) {
			$this->updateCurrencies(true);
		}

		$this->cache->delete('currency');
	}

	public function editCurrency(int $currency_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET title = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', symbol_left = '" . $this->db->escape($data['symbol_left']) . "', symbol_right = '" . $this->db->escape($data['symbol_right']) . "', decimal_place = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . $this->db->escape($data['value']) . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE currency_id = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function editValueByCode($code, $value): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', date_modified = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		$this->cache->delete('currency');
	}

	public function deleteCurrency(int $currency_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency` WHERE currency_id = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function getCurrency(int $currency_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE currency_id = '" . (int)$currency_id . "'");

		return $query->row;
	}

	public function getCurrencyByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape(trim($code)) . "'");

		return $query->row;
	}

	public function getCurrencies(array $data = []) {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "currency`";

			$sort_data = [
				'title',
				'code',
				'value',
				'date_modified',
				'status'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `title`";
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

		} else {
			$currency_data = $this->cache->get('currency');

			if (!$currency_data) {
				$currency_data = [];

				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` ORDER BY `title` ASC");

				foreach ($query->rows as $result) {
					$currency_data[$result['code']] = [
						'currency_id'   => $result['currency_id'],
						'title'         => $result['title'],
						'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
					];
				}

				$this->cache->set('currency', $currency_data);
			}

			return $currency_data;
		}
	}

	/** --------------------------------------------------------------------------------
	 * FloatRates follows 148 currencies using 19 data sources.
	 *
	 * Example USD: http://www.floatrates.com/daily/usd.xml
	 *
	 * Example XML Response:
	 * <item>
	 *	<title>1 USD = 0.81253219 EUR</title>
	 *	<link>https://www.floatrates.com/usd/eur/</link>
	 *	<description>1 U.S. Dollar = 0.81253219 Euro</description>
	 *	<pubDate>Mon, 12 Mar 2018 12:00:01 GMT</pubDate>
	 *	<baseCurrency>USD</baseCurrency>
	 *	<baseName>U.S. Dollar</baseName>
	 *	<targetCurrency>EUR</targetCurrency>
	 *	<targetName>Euro</targetName>
	 *	<exchangeRate>0.81253219</exchangeRate>
	 * </item>
	 * ----------------------------------------------------------------------------------
	 */
	public function updateCurrencies($default = '') {
		$default = $this->config->get('config_currency');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE code != '" . trim($default) . "' AND date_modified < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "' AND status = '1'");

		if (!$query->rows) {
			return;
		}

		$file_url = 'https://www.floatrates.com/daily/' . strtolower($default) . '.xml';

		// --- CONNECTIVITY CHECK ---
		// Use cURL with a short timeout to verify the remote host is reachable
		// before attempting any XML load. This prevents fatal errors on login
		// when the internet is down or floatrates.com is unavailable.
		$ch = curl_init($file_url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_NOBODY         => true,   // HEAD request — don't download the body
			CURLOPT_TIMEOUT        => 5,      // Max 5 seconds to wait
			CURLOPT_CONNECTTIMEOUT => 5,      // Max 5 seconds to connect
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => true,
		]);

		curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_errno($ch);

		unset($ch);

		// If cURL failed (no network) or HTTP status is not 200, abort silently
		if ($curl_error || $http_code !== 200) {
			return;
		}
		// --- END CONNECTIVITY CHECK ---

		$data = [
			'sort'  => 'code',
			'order' => 'ASC'
		];

		$results = $this->getCurrencies($data);

		if (!$results) {
			return;
		}

		$currencies = [];

		foreach ($results as $result) {
			if ($result['code'] != strtoupper($default)) {
				$currencies[] = $result['code'];
			}
		}

		if (empty($currencies)) {
			return;
		}

		// Suppress warnings and catch any XML parse failure gracefully
		libxml_use_internal_errors(true);

		$xml = simplexml_load_file($file_url);

		if ($xml === false) {
			libxml_clear_errors();
			return;
		}

		foreach ($xml->children() as $response) {
			if (in_array($response->targetCurrency, $currencies)) {
				$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . $response->exchangeRate . "', date_modified = NOW() WHERE code = '" . strtoupper($response->targetCurrency) . "'");
			}
		}

		libxml_use_internal_errors(false);

		$this->cache->delete('currency');

		$this->editValueByCode($default, '1.000000');
	}

	//----------------------------------------------------------------------------------
	// Alpha Vantage : Currency_Exchange_Rate API
	//
	// replace the "demo" apikey below with your own key from https://www.alphavantage.co/support/#api-key
	//
	// Example PHP/Json Response:
	// --------------------------
	// $json = file_get_contents('https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=USD&to_currency=JPY&apikey=demo');
	//
	// $data = json_decode($json,true);
	//
	// print_r($data);
	//
	// exit;
	//----------------------------------------------------------------------------------

	public function updateAlphaVantageCurrencies() {
		$default = $this->config->get('config_currency');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE `code` != '" . $default . "' AND date_modified < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "' AND status = '1'");

		if ($query->rows) {
			$api_key = $this->config->get('config_alpha_vantage');

			$api_key = (isset($api_key) && $api_key) ? mb_strtoupper($api_key, 'UTF-8') : 'P6WGY9G9LB22GMBJ';

			foreach ($query->rows as $result) {
				$code = mb_strtoupper($result['code'], 'UTF-8');

				$url = 'https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=' . strtoupper($default) . '&to_currency=' . $code . '&apikey=' . $api_key;

				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);

				$response = curl_exec($curl);

				unset($curl);

				$response_info = json_decode($response, true);

				if (isset($response_info)) {
					$value = (float)$response_info["Realtime Currency Exchange Rate"]["5. Exchange Rate"];

					$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . $value . "', date_modified = NOW() WHERE `code` = '" . $this->db->escape($result['code']) . "'");
				}
			}

			$this->editValueByCode($default, '1.000000');

			$this->cache->delete('currency');
		} else {
			return;
		}
	}

	protected function checkFileExists($url): bool {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_NOBODY, 1);
		curl_setopt($curl, CURLOPT_FAILONERROR, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		unset($curl);

		if ($response !== false) {
			return true;
		} else {
			return false;
		}
	}

	public function getTotalCurrencies() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "currency`");

		return $query->row['total'];
	}
}
