<?php
/**
 * Class ModelCatalogTagCloud
 *
 * @package NivoCart
 */
declare(strict_types = 1);

class ModelCatalogTagCloud extends Model {
	/**
	 * Returns a rendered tag cloud HTML string, or null if no tags are found.
	 */
	public function getRandomTags(int $limit, int|float $min_font_size, int|float $max_font_size, int|string $font_weight, bool $random): ?string {
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');

		// Each row may hold a comma-delimited list of tags, so split them in PHP and count every keyword separately
		$query = $this->db->query("SELECT ptg.tag AS `tag`
			FROM `" . DB_PREFIX . "product_tag` ptg
			LEFT JOIN `" . DB_PREFIX . "product` p ON (ptg.product_id = p.product_id)
			LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (ptg.product_id = p2s.product_id)
			WHERE ptg.language_id = " . $language_id . " AND p2s.store_id = " . $store_id . " AND p.status = '1'"
		);

		if (empty($query->rows)) {
			return null;
		}

		$tags = [];
		$names = [];

		foreach ($query->rows as $row) {
			foreach (explode(',', (string)$row['tag']) as $keyword) {
				$keyword = trim($keyword);

				if ($keyword === '') {
					continue;
				}

				$key = mb_strtolower($keyword, 'UTF-8');

				if (!isset($tags[$key])) {
					$tags[$key] = 0;
					$names[$key] = $keyword;
				}

				$tags[$key]++;
			}
		}

		if (!$tags) {
			return null;
		}

		// Keep the most used keywords, limited to the module setting
		arsort($tags);

		$tags = array_slice($tags, 0, max(1, $limit), true);

		$cloud_tags = [];

		foreach ($tags as $key => $total) {
			$cloud_tags[$names[$key]] = $total;
		}

		return $this->generateTagCloud($cloud_tags, $random, (int)$min_font_size, (int)$max_font_size, $font_weight);
	}

	/**
	 * Builds and returns the tag cloud HTML string.
	 *
	 * @param array<string, int> $tags
	 */
	protected function generateTagCloud(array $tags, bool $random, int $min_font_size, int $max_font_size, int|string $font_weight): string {
		arsort($tags);

		$values = array_values($tags);
		$max_qty = max($values);
		$min_qty = min($values);
		$spread = max(1, $max_qty - $min_qty);
		$step = ($max_font_size - $min_font_size) / $spread;

		$cloud = [];

		foreach ($tags as $tag => $count) {
			$size = $random ? mt_rand($min_font_size, $max_font_size) : (int)round($min_font_size + (($count - $min_qty) * $step), 0, PHP_ROUND_HALF_UP);

			$tag = trim((string)$tag);
			$url = $this->url->link('product/search', 'search=' . rawurlencode($tag) . '&tag=' . rawurlencode($tag), 'SSL');
			$style = 'text-decoration:none; font-size:' . $size . 'px; font-weight:' . $font_weight . ';';

			$cloud[] = '<a href="' . $url . '" style="' . $style . '" title="">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</a>';
		}

		shuffle($cloud);

		return implode(' ', $cloud);
	}
}
