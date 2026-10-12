<?php
/**
 * Class ControllerFeedPinterestFeed
 *
 * Google Product Feed format (RSS 2.0 with g: namespace) used by Pinterest Catalogs,
 * and reusable for Google Merchant Center. The XML is built into a cache file and served from it.
 *
 * @package NivoCart
 */
class ControllerFeedPinterestFeed extends Controller {
	private const BATCH = 200;

	/** @var array<string, array{0: int, 1: int}|false> Image validation results for this run */
	private array $image_checked = [];

	public function index() {
		if (!$this->config->get('pinterest_feed_status')) {
			$this->response->addHeader('HTTP/1.1 404 Not Found');
			$this->response->setOutput('Feed disabled');

			return;
		}

		$file = $this->getCacheDir() . 'pinterest_feed.xml';
		$ttl = max(1, (int)$this->config->get('pinterest_feed_cache_hours')) * 3600;

		if (!is_file($file) || (time() - filemtime($file)) > $ttl) {
			$this->build($file);
		}

		if (!is_file($file)) {
			$this->response->addHeader('HTTP/1.1 503 Service Unavailable');
			$this->response->addHeader('Retry-After: 300');
			$this->response->setOutput('Feed is being generated, please try again shortly');

			return;
		}

		$this->response->addHeader('Content-Type: application/xml; charset=UTF-8');
		$this->response->setOutput((string)file_get_contents($file));
	}

	/**
	 * Builds the feed into a temporary file, then moves it over the cache file.
	 */
	protected function build(string $file): void {
		$lock = fopen($file . '.lock', 'c');

		// Another request is already building: serve the old file (or 503 if there is none)
		if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
			return;
		}

		if (function_exists('set_time_limit')) {
			set_time_limit(300);
		}

		ignore_user_abort(true);

		$this->load->model('feed/pinterest_feed');

		$currency = strtoupper((string)($this->config->get('pinterest_feed_currency') ?: $this->config->get('config_currency')));

		if (!$this->currency->has($currency)) {
			$currency = $this->config->get('config_currency');
		}

		$min_image = (int)($this->config->get('pinterest_feed_min_image') ?: 600);
		$map = $this->config->get('pinterest_feed_category');
		$map = is_array($map) ? $map : [];
		$default_category = (string)$this->config->get('pinterest_feed_category_default');
		$categories = $this->model_feed_pinterest_feed->getCategories();
		$brand_default = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		$has_variants = $this->model_feed_pinterest_feed->hasVariants();

		$stats = ['items' => 0, 'skipped_image' => 0, 'skipped_price' => 0, 'skipped_other' => 0];

		$tmp = $file . '.tmp';
		$handle = fopen($tmp, 'wb');

		if (!$handle) {
			flock($lock, LOCK_UN);
			fclose($lock);

			return;
		}

		fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		fwrite($handle, '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\n");
		fwrite($handle, '<channel>' . "\n");
		fwrite($handle, '<title>' . $this->xml($brand_default) . '</title>' . "\n");
		fwrite($handle, '<link>' . $this->xml(HTTPS_SERVER) . '</link>' . "\n");
		fwrite($handle, '<description>' . $this->xml($brand_default) . '</description>' . "\n");

		$start = 0;

		do {
			$products = $this->model_feed_pinterest_feed->getProducts($start, self::BATCH);
			$start += self::BATCH;

			if (!$products) {
				break;
			}

			$ids = array_map(fn($row) => (int)$row['product_id'], $products);
			$product_categories = $this->model_feed_pinterest_feed->getProductCategories($ids);
			$product_images = $this->model_feed_pinterest_feed->getProductImages($ids);

			foreach ($products as $product) {
				$item = $this->buildItem($product, $product_categories[(int)$product['product_id']] ?? [], $product_images[(int)$product['product_id']] ?? [], $categories, $map, $default_category, $currency, $min_image, $brand_default, $stats);

				if ($item !== '') {
					fwrite($handle, $item);

					$stats['items']++;
				}
			}
		} while (count($products) === self::BATCH);

		fwrite($handle, '</channel>' . "\n");
		fwrite($handle, '</rss>' . "\n");
		fclose($handle);

		// Do not replace a good feed with an empty one
		if ($stats['items'] > 0 || !is_file($file)) {
			rename($tmp, $file);

			$stats['generated'] = time();
			$stats['variants'] = $has_variants;

			file_put_contents($this->getCacheDir() . 'pinterest_feed.json', json_encode($stats));
		} else {
			unlink($tmp);
		}

		flock($lock, LOCK_UN);
		fclose($lock);
	}

	/**
	 * Returns the XML for one item, or an empty string when the product must be left out.
	 */
	protected function buildItem(array $product, array $category_ids, array $extra_images, array $categories, array $map, string $default_category, string $currency, int $min_image, string $brand_default, array &$stats): string {
		$name = trim(html_entity_decode(strip_tags(html_entity_decode((string)$product['name'], ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));

		if ($name === '') {
			$stats['skipped_other']++;

			return '';
		}

		// Main image must exist locally and be large enough
		$image = $this->getImageUrl((string)$product['image'], $min_image);

		if ($image === '') {
			$stats['skipped_image']++;

			return '';
		}

		// Prices (tax handled the same way as the product page)
		$use_tax = (bool)$this->config->get('config_tax');
		$price = $this->formatPrice($this->tax->calculate((float)$product['price'], (int)$product['tax_class_id'], $use_tax), $currency);

		if ((float)$price <= 0) {
			$stats['skipped_price']++;

			return '';
		}

		$sale_price = '';

		if ((float)$product['special'] > 0) {
			$special = $this->formatPrice($this->tax->calculate((float)$product['special'], (int)$product['tax_class_id'], $use_tax), $currency);

			if ((float)$special > 0 && (float)$special < (float)$price) {
				$sale_price = $special;
			}
		}

		// Link
		$link = html_entity_decode($this->url->link('product/product', 'product_id=' . (int)$product['product_id'], 'SSL'), ENT_QUOTES, 'UTF-8');

		if (mb_strlen($link, 'UTF-8') > 511) {
			$stats['skipped_other']++;

			return '';
		}

		// Description: plain text, never empty
		$description = html_entity_decode((string)$product['description'], ENT_QUOTES, 'UTF-8');
		$description = preg_replace('@<(script|style)[^>]*?>.*?</\1>@si', ' ', $description);
		$description = html_entity_decode(strip_tags($description), ENT_QUOTES, 'UTF-8');
		$description = trim(preg_replace('/\s+/u', ' ', $description));

		if (mb_strlen($description, 'UTF-8') < 10) {
			$description = $name;
		}

		$description = mb_substr($description, 0, 10000, 'UTF-8');

		// Category path (product_type) and Google category: the most specific category that has an ID wins
		$product_type = '';
		$google_category = '';
		$best_depth = 0;

		foreach ($category_ids as $category_id) {
			$path = $this->getCategoryPath($category_id, $categories);

			if (!$path) {
				continue;
			}

			if ($product_type === '') {
				$product_type = implode(' > ', array_column($path, 'name'));
			}

			// Walk from the deepest category up to the top level one
			for ($depth = count($path); $depth >= 1; $depth--) {
				$id = (int)$path[$depth - 1]['category_id'];

				if (!empty($map[$id])) {
					if ($depth > $best_depth) {
						$best_depth = $depth;
						$google_category = (string)$map[$id];
						$product_type = implode(' > ', array_column($path, 'name'));
					}

					break;
				}
			}
		}

		if ($google_category === '') {
			$google_category = $default_category;
		}

		$brand = trim(html_entity_decode((string)$product['manufacturer'], ENT_QUOTES, 'UTF-8'));
		$mpn = trim((string)($product['mpn'] ?: ($product['sku'] ?: $product['model'])));
		$gtin = $this->getGtin($product);

		$xml = '<item>' . "\n";
		$xml .= '<g:id>' . (int)$product['product_id'] . '</g:id>' . "\n";
		$xml .= '<title>' . $this->xml(mb_substr($name, 0, 150, 'UTF-8')) . '</title>' . "\n";
		$xml .= '<description>' . $this->xml($description) . '</description>' . "\n";
		$xml .= '<link>' . $this->xml($link) . '</link>' . "\n";
		$xml .= '<g:image_link>' . $this->xml($image) . '</g:image_link>' . "\n";

		$count = 0;

		foreach ($extra_images as $extra) {
			if ($count >= 10) {
				break;
			}

			$extra_url = $this->getImageUrl((string)$extra, $min_image);

			if ($extra_url !== '' && $extra_url !== $image) {
				$xml .= '<g:additional_image_link>' . $this->xml($extra_url) . '</g:additional_image_link>' . "\n";

				$count++;
			}
		}

		$xml .= '<g:price>' . $price . ' ' . $currency . '</g:price>' . "\n";

		if ($sale_price !== '') {
			$xml .= '<g:sale_price>' . $sale_price . ' ' . $currency . '</g:sale_price>' . "\n";
		}

		$xml .= '<g:availability>in stock</g:availability>' . "\n";
		$xml .= '<g:condition>new</g:condition>' . "\n";
		$xml .= '<g:brand>' . $this->xml($brand !== '' ? $brand : $brand_default) . '</g:brand>' . "\n";

		if ($mpn !== '') {
			$xml .= '<g:mpn>' . $this->xml(mb_substr($mpn, 0, 70, 'UTF-8')) . '</g:mpn>' . "\n";
		}

		if ($gtin !== '') {
			$xml .= '<g:gtin>' . $gtin . '</g:gtin>' . "\n";
		}

		if ($product_type !== '') {
			$xml .= '<g:product_type>' . $this->xml($product_type) . '</g:product_type>' . "\n";
		}

		if ($google_category !== '') {
			$xml .= '<g:google_product_category>' . $this->xml($google_category) . '</g:google_product_category>' . "\n";
		}

		if (!empty($product['pid'])) {
			$xml .= '<g:item_group_id>' . $this->xml(mb_substr((string)$product['pid'], 0, 50, 'UTF-8')) . '</g:item_group_id>' . "\n";
		}

		$xml .= '</item>' . "\n";

		return $xml;
	}

	/**
	 * Full image URL for a local original, or '' when missing, unreadable, not a web image or too small.
	 */
	protected function getImageUrl(string $filename, int $min_size): string {
		$filename = html_entity_decode($filename, ENT_QUOTES, 'UTF-8');

		if ($filename === '' || stripos($filename, 'no_image') !== false) {
			return '';
		}

		if (!array_key_exists($filename, $this->image_checked)) {
			$this->image_checked[$filename] = false;

			$real = realpath(DIR_IMAGE . $filename);

			if ($real && is_file($real) && strpos(str_replace('\\', '/', $real), str_replace('\\', '/', rtrim(DIR_IMAGE, '/\\'))) === 0) {
				$info = @getimagesize($real);

				if ($info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
					$this->image_checked[$filename] = [(int)$info[0], (int)$info[1]];
				}
			}
		}

		$size = $this->image_checked[$filename];

		if (!$size || min($size) < $min_size) {
			return '';
		}

		$path = implode('/', array_map('rawurlencode', explode('/', str_replace('\\', '/', $filename))));

		return HTTPS_SERVER . 'image/' . $path;
	}

	/**
	 * Category chain from the top level category down to the given category.
	 *
	 * @return array<int, array{category_id: int, name: string}>
	 */
	protected function getCategoryPath(int $category_id, array $categories): array {
		$path = [];
		$guard = 0;

		while ($category_id && isset($categories[$category_id]) && $guard++ < 10) {
			array_unshift($path, [
				'category_id' => $category_id,
				'name'        => html_entity_decode((string)$categories[$category_id]['name'], ENT_QUOTES, 'UTF-8')
			]);

			$category_id = $categories[$category_id]['parent_id'];
		}

		return $path;
	}

	protected function getGtin(array $product): string {
		foreach (['upc', 'ean', 'jan', 'isbn'] as $field) {
			$value = preg_replace('/\D/', '', (string)$product[$field]);

			if (in_array(strlen($value), [8, 12, 13, 14], true)) {
				return $value;
			}
		}

		return '';
	}

	protected function formatPrice(float $amount, string $currency): string {
		return number_format((float)$this->currency->format($amount, $currency, 0, false), 2, '.', '');
	}

	protected function xml(string $text): string {
		$text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);

		return htmlspecialchars((string)$text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	protected function getCacheDir(): string {
		return defined('DIR_CACHE') ? DIR_CACHE : DIR_SYSTEM . 'cache/';
	}
}
