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
			// The admin typed the starting value: fetch the real one without the rate change check
			$this->updateRates('', [strtoupper(trim($data['code']))], false);
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
	 * Automatic rate updates
	 *
	 * Sources (all free, no API key):
	 *  - Frankfurter (primary): https://api.frankfurter.dev/v2/rates?base=GBP&quotes=USD,EUR
	 *    Data of the European Central Bank and other central banks, JSON.
	 *  - FloatRates (backup): https://www.floatrates.com/daily/gbp.xml
	 *
	 * Callers:
	 *  - cron_currency.php (CLI, recommended): updateRates() with the source given on the
	 *    command line, or both sources in order when none is given.
	 *  - Admin dashboard (updateCurrencies / updateAlphaVantageCurrencies): the fallback for
	 *    stores without a cron job. It steps aside while the cron job keeps succeeding.
	 *
	 * Every run is written to nc_cron_log (tasks currency_cron and currency_dashboard) and
	 * the newest successful run is shown on Localisation > Currency.
	 * ----------------------------------------------------------------------------------
	 */
	public const SOURCES = ['frankfurter', 'floatrates'];

	// Largest change (in percent) from the stored rate that one update may apply
	public const MAX_RATE_CHANGE = 25;

	// Days after which a stored rate counts as outdated and the rate change check is skipped for it
	public const OUTDATED_DAYS = 14;

	// Hours without a successful run after which the admin shows a warning
	public const OVERDUE_HOURS = 36;

	public const LOG_CRON = 'currency_cron';
	public const LOG_DASHBOARD = 'currency_dashboard';

	// Seconds a request to a rate source may take in total
	private int $timeout = 15;

	/**
	 * Fetch the rates and save them.
	 *
	 * @param string $source Empty for every source in order, or one of SOURCES
	 * @param array  $only   Upper-case currency codes to update, empty for all of them
	 * @param bool   $check  Apply the maximum rate change check
	 *
	 * @return array ['status' => 'success'|'error', 'updated' => int, 'message' => string]
	 */
	public function updateRates(string $source = '', array $only = [], bool $check = true): array {
		$default = strtoupper(trim((string)$this->config->get('config_currency')));

		if ($source !== '' && !in_array($source, self::SOURCES, true)) {
			return ['status' => 'error', 'updated' => 0, 'message' => 'Unknown rate source "' . $source . '".'];
		}

		$query = $this->db->query("SELECT `code`, `value`, `date_modified` FROM `" . DB_PREFIX . "currency` WHERE `code` != '" . $this->db->escape($default) . "'");

		$pending = [];
		$outdated = [];

		foreach ($query->rows as $row) {
			$code = strtoupper($row['code']);

			if (!$only || in_array($code, $only, true)) {
				$pending[$code] = (float)$row['value'];

				// A rate not refreshed for a long time (for example a disabled currency) may legitimately be far off
				if (strtotime($row['date_modified']) < strtotime('-' . self::OUTDATED_DAYS . ' days')) {
					$outdated[$code] = true;
				}
			}
		}

		if (!$pending) {
			return ['status' => 'success', 'updated' => 0, 'message' => 'No currency besides the default one needs a rate.'];
		}

		$updated = [];
		$dates = [];
		$notes = [];
		$problems = false;

		$sources = ($source !== '') ? [$source] : self::SOURCES;

		foreach ($sources as $name) {
			if (!$pending) {
				break;
			}

			try {
				$rates = ($name === 'frankfurter') ? $this->fetchFrankfurter($default, array_keys($pending)) : $this->fetchFloatRates($default, array_keys($pending));
			} catch (\Throwable $e) {
				$notes[] = ($name === 'frankfurter' ? 'Frankfurter' : 'FloatRates') . ' failed: ' . rtrim($e->getMessage(), '.') . '.';

				continue;
			}

			foreach ($rates as $code => $rate) {
				if (!isset($pending[$code])) {
					continue;
				}

				$old = $pending[$code];

				unset($pending[$code]);

				$problem = $this->saveRate($code, $rate['rate'], $old, $check && !isset($outdated[$code]));

				if ($problem !== '') {
					$notes[] = ($name === 'frankfurter' ? 'Frankfurter' : 'FloatRates') . ': ' . $problem;
					$problems = true;

					continue;
				}

				$updated[$name] = ($updated[$name] ?? 0) + 1;

				if ($rate['date'] !== '' && $rate['date'] > ($dates[$name] ?? '')) {
					$dates[$name] = $rate['date'];
				}
			}
		}

		return $this->finishRun($default, $updated, $dates, $notes, array_keys($pending), $problems);
	}

	/**
	 * Dashboard fallback for stores without a cron job (Frankfurter, then FloatRates).
	 */
	public function updateCurrencies($default = ''): void {
		if (!$this->dashboardUpdateDue()) {
			return;
		}

		$this->timeout = 6;

		$this->logRun(self::LOG_DASHBOARD, $this->updateRates());
	}

	/**
	 * Alpha Vantage: optional replacement for the dashboard fallback, needs a personal API key.
	 * The free key allows 25 requests a day, one per currency.
	 *
	 * Example: https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=GBP&to_currency=USD&apikey=KEY
	 */
	public function updateAlphaVantageCurrencies(): void {
		$api_key = trim((string)$this->config->get('config_alpha_vantage'));

		if ($api_key === '' || !$this->dashboardUpdateDue()) {
			return;
		}

		$this->timeout = 10;

		$default = strtoupper(trim((string)$this->config->get('config_currency')));

		$query = $this->db->query("SELECT `code`, `value` FROM `" . DB_PREFIX . "currency` WHERE `code` != '" . $this->db->escape($default) . "' AND `date_modified` < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "'");

		$updated = 0;
		$date = '';
		$notes = [];
		$missing = [];
		$problems = false;

		foreach ($query->rows as $row) {
			$code = strtoupper($row['code']);

			try {
				$body = $this->httpGet('https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=' . rawurlencode($default) . '&to_currency=' . rawurlencode($code) . '&apikey=' . rawurlencode($api_key));

				$data = json_decode($body, true);

				// A rate limit or key error comes back as a "Note", "Information" or "Error Message" without a rate
				$rate = (float)($data['Realtime Currency Exchange Rate']['5. Exchange Rate'] ?? 0);

				if (!is_array($data) || !is_finite($rate) || $rate <= 0) {
					throw new \Exception(is_array($data) ? (string)($data['Note'] ?? $data['Information'] ?? $data['Error Message'] ?? 'No rate in the response.') : 'Invalid response.');
				}
			} catch (\Throwable $e) {
				$notes[] = 'Alpha Vantage ' . $code . ': ' . rtrim($e->getMessage(), '.') . '.';

				// Stop at the first failure: a rate limit would fail every remaining request too
				$missing[] = $code;

				break;
			}

			$problem = $this->saveRate($code, $rate, (float)$row['value'], true);

			if ($problem !== '') {
				$notes[] = 'Alpha Vantage: ' . $problem;
				$problems = true;

				continue;
			}

			$updated++;

			$date = substr((string)($data['Realtime Currency Exchange Rate']['6. Last Refreshed'] ?? ''), 0, 10);
		}

		$result = $this->finishRun($default, $updated ? ['alphavantage' => $updated] : [], $date ? ['alphavantage' => $date] : [], $notes, $missing, $problems);

		$this->logRun(self::LOG_DASHBOARD, $result);
	}

	/**
	 * Write a run to nc_cron_log. Never throws: logging must not break an update.
	 */
	public function logRun(string $task, array $result): void {
		try {
			if (!$this->hasLogTable()) {
				return;
			}

			$this->db->query("INSERT INTO `" . DB_PREFIX . "cron_log` SET `task` = '" . $this->db->escape($task) . "', `rows_affected` = '" . (int)$result['updated'] . "', `status` = '" . $this->db->escape($result['status']) . "', `message` = '" . $this->db->escape(mb_substr($result['message'], 0, 250)) . "', `date_added` = NOW()");
		} catch (\Throwable $e) {
			// Ignore
		}
	}

	/**
	 * Newest log row of the given tasks (optionally only successful ones, optionally only
	 * from the last $hours hours). Empty array when there is none.
	 */
	public function getLastRun(array $tasks, bool $success_only = false, int $hours = 0): array {
		if (!$tasks || !$this->hasLogTable()) {
			return [];
		}

		$sql = "SELECT `rows_affected`, `status`, `message`, `task`, `date_added` FROM `" . DB_PREFIX . "cron_log` WHERE `task` IN ('" . implode("','", array_map([$this->db, 'escape'], $tasks)) . "')";

		if ($success_only) {
			$sql .= " AND `status` = 'success'";
		}

		if ($hours > 0) {
			$sql .= " AND `date_added` > DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)";
		}

		$query = $this->db->query($sql . " ORDER BY `log_id` DESC LIMIT 1");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Dashboard fallback guard: only when the cron job is not doing the work, at most once
	 * an hour after a failed or empty attempt, and only for rates older than a day.
	 */
	protected function dashboardUpdateDue(): bool {
		if (!$this->config->get('config_currency_auto') || !extension_loaded('curl')) {
			return false;
		}

		if ($this->getLastRun([self::LOG_CRON], true, self::OVERDUE_HOURS)) {
			return false;
		}

		if ($this->getLastRun([self::LOG_DASHBOARD], false, 1)) {
			return false;
		}

		$default = strtoupper(trim((string)$this->config->get('config_currency')));

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "currency` WHERE `code` != '" . $this->db->escape($default) . "' AND `date_modified` < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "'");

		return (int)$query->row['total'] > 0;
	}

	/**
	 * Save one rate unless it is not usable or moves further than MAX_RATE_CHANGE from the
	 * stored one. Returns an empty string when saved, otherwise the reason.
	 */
	protected function saveRate(string $code, float $rate, float $old, bool $check): string {
		if (!is_finite($rate) || $rate <= 0) {
			return $code . ' ignored (invalid rate).';
		}

		if ($check && $old > 0) {
			$change = ($rate - $old) / $old * 100;

			if (abs($change) > self::MAX_RATE_CHANGE) {
				return sprintf('%s rejected (%+.1f%% from the stored rate).', $code, $change);
			}
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . number_format($rate, 8, '.', '') . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape($code) . "'");

		return '';
	}

	/**
	 * Common end of every run: keep the default currency at 1, clear the cache, build the result.
	 */
	protected function finishRun(string $default, array $updated, array $dates, array $notes, array $missing, bool $problems): array {
		$labels = [
			'frankfurter'  => 'Frankfurter',
			'floatrates'   => 'FloatRates',
			'alphavantage' => 'Alpha Vantage'
		];

		$total = array_sum($updated);

		if ($total) {
			$this->editValueByCode($default, '1.000000');

			$this->cache->delete('currency');
		}

		$parts = [];

		foreach ($updated as $name => $count) {
			$parts[] = $count . ' from ' . $labels[$name] . (!empty($dates[$name]) ? ' (rates of ' . $dates[$name] . ')' : '');
		}

		$message = $total ? 'Updated ' . implode(', ', $parts) . '.' : 'No rate updated.';

		if ($missing) {
			$notes[] = 'No rate received for ' . implode(', ', $missing) . '.';
		}

		if ($notes) {
			$message .= ' ' . implode(' ', $notes);
		}

		return [
			'status'  => ($total && !$missing && !$problems) ? 'success' : 'error',
			'updated' => $total,
			'message' => $message
		];
	}

	/**
	 * Frankfurter: array of {"date", "base", "quote", "rate"} objects.
	 *
	 * @return array [CODE => ['rate' => float, 'date' => 'Y-m-d']]
	 */
	protected function fetchFrankfurter(string $base, array $codes): array {
		$data = json_decode($this->httpGet('https://api.frankfurter.dev/v2/rates?base=' . rawurlencode($base) . '&quotes=' . implode(',', array_map('rawurlencode', $codes))), true);

		if (!is_array($data)) {
			throw new \Exception('Invalid response.');
		}

		if (isset($data['message'])) {
			throw new \Exception((string)$data['message']);
		}

		$rates = [];

		foreach ($data as $item) {
			if (!is_array($item) || empty($item['quote']) || !isset($item['rate']) || strtoupper((string)($item['base'] ?? $base)) !== $base) {
				continue;
			}

			$rate = (float)$item['rate'];

			if (is_finite($rate) && $rate > 0) {
				$rates[strtoupper((string)$item['quote'])] = ['rate' => $rate, 'date' => (string)($item['date'] ?? '')];
			}
		}

		if (!$rates) {
			throw new \Exception('No usable rates in the response.');
		}

		return $rates;
	}

	/**
	 * FloatRates: RSS-style XML with one <item> per currency, see the example below.
	 *
	 * <item>
	 *	<pubDate>Mon, 12 Mar 2018 12:00:01 GMT</pubDate>
	 *	<baseCurrency>USD</baseCurrency>
	 *	<targetCurrency>EUR</targetCurrency>
	 *	<exchangeRate>0.81253219</exchangeRate>
	 * </item>
	 *
	 * @return array [CODE => ['rate' => float, 'date' => 'Y-m-d']]
	 */
	protected function fetchFloatRates(string $base, array $codes): array {
		$body = $this->httpGet('https://www.floatrates.com/daily/' . strtolower($base) . '.xml');

		$previous = libxml_use_internal_errors(true);

		$xml = simplexml_load_string($body);

		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		if ($xml === false) {
			throw new \Exception('Invalid XML response.');
		}

		$rates = [];

		foreach ($xml->children() as $item) {
			$code = strtoupper(trim((string)$item->targetCurrency));
			$rate = (float)(string)$item->exchangeRate;

			if (in_array($code, $codes, true) && is_finite($rate) && $rate > 0) {
				$time = strtotime((string)$item->pubDate);

				$rates[$code] = ['rate' => $rate, 'date' => $time ? gmdate('Y-m-d', $time) : ''];
			}
		}

		if (!$rates) {
			throw new \Exception('No usable rates in the response.');
		}

		return $rates;
	}

	/**
	 * GET a URL with cURL and return the body. Throws on any failure.
	 */
	protected function httpGet(string $url): string {
		if (!function_exists('curl_init')) {
			throw new \Exception('The PHP cURL extension is not available.');
		}

		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_USERAGENT      => 'NivoCart',
			CURLOPT_HTTPHEADER     => ['Accept: application/json, application/xml']
		]);

		$body = curl_exec($ch);
		$error = curl_error($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

		unset($ch);

		if ($body === false) {
			throw new \Exception('Connection error: ' . $error);
		}

		if ($code !== 200) {
			throw new \Exception('HTTP status ' . $code . '.');
		}

		if ($body === '') {
			throw new \Exception('Empty response.');
		}

		return $body;
	}

	protected function hasLogTable(): bool {
		$query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . 'cron_log') . "'");

		return is_object($query) && $query->num_rows > 0;
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
