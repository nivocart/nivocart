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

		$query = $this->db->query("SELECT ptg.tag AS `tag`, COUNT(ptg.tag) AS `total`
			FROM `" . DB_PREFIX . "product_tag` ptg
			LEFT JOIN `" . DB_PREFIX . "product` p ON (ptg.product_id = p.product_id)
			LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (ptg.product_id = p2s.product_id)
			WHERE ptg.language_id = " . $language_id . " AND p2s.store_id = " . $store_id . " AND p.status = '1'
			GROUP BY ptg.tag LIMIT 0, " . $limit
		);

		if (empty($query->rows)) {
			return null;
		}

		$tags = [];

		foreach ($query->rows as $row) {
			$name = trim(str_replace(',', ' ', (string)$row['tag']));
			$tags[$name] = (int)$row['total'];
		}

		return $this->generateTagCloud($tags, $random, (int)$min_font_size, (int)$max_font_size, $font_weight);
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

			$tag = trim(str_replace('&', '&amp;', (string)$tag));
			$url = $this->url->link('product/search', 'search=' . $tag . '&tag=' . $tag, 'SSL');
			$style = 'text-decoration:none; font-size:' . $size . 'px; font-weight:' . $font_weight . ';';

			$cloud[] = '<a href="' . $url . '" style="' . $style . '" title="">' . $tag . '</a>';
		}

		shuffle($cloud);

		return implode(' ', $cloud);
	}
}
