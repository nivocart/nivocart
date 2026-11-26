<?php
/**
 * Video watch page helper functions shared by Catalog and Admin.
 *
 * A watch page is a page dedicated to one hosted video. Products sharing the same video file
 * (variants) share one watch page. Everything that links to a watch page (product page, video hub,
 * canonical tag and sitemaps) uses these functions, so the addresses always agree.
 *
 * videoWatchList()      - every video that has a watch page in a store
 * videoWatchCode()      - short code of a video, calculated from its file name
 * videoWatchName()      - product name without the variant suffix, for example "[Blue-L]"
 * videoWatchVariant()   - the variant suffix of a product name
 * videoWatchSlug()      - readable part of the address
 * videoWatchPath()      - SEO path of a watch page
 * videoWatchUrl()       - full address of a watch page
 * videoWatchHubUrl()    - full address of the page listing all videos
 * videoWatchImageUrl()  - full address of a file in the image folder
 * videoWatchText()      - plain text from an HTML description
 *
 * @package NivoCart
 */

/**
 * Videos that have a watch page in a store, keyed by code and sorted by slug.
 *
 * A video is listed when its file and cover image exist and at least one product using it is
 * enabled, available, assigned to the store and has no age restriction. The product with the
 * lowest ID supplies the name used in the address, in the default language of the main store.
 * The result is empty when the video table (CJDropshipping modification) does not exist.
 *
 * @param object $db       Database object with query() and escape() methods
 * @param int    $store_id
 *
 * @return array<string, array<string, mixed>>
 */
function videoWatchList(object $db, int $store_id): array {
	static $cache = [];

	if (isset($cache[$store_id])) {
		return $cache[$store_id];
	}

	$cache[$store_id] = [];

	$check = $db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_video'");

	if (empty($check->num_rows)) {
		return $cache[$store_id];
	}

	// Default language of the main store. Looked up in separate steps, so text columns of two tables are never
	// compared with each other: that fails when the tables do not use the same collation.
	$setting = $db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language' AND `store_id` = '0' LIMIT 1");

	$language = $db->query("SELECT `language_id` FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $db->escape((string)($setting->row['value'] ?? '')) . "' LIMIT 1");

	$language_id = (int)($language->row['language_id'] ?? 0);

	$query = $db->query("SELECT pv.`product_id`, pv.`video`, pv.`poster`, pv.`width`, pv.`height`, pv.`duration`, pd.`name`, pd.`description` FROM `" . DB_PREFIX . "product_video` pv INNER JOIN `" . DB_PREFIX . "product` p ON (p.`product_id` = pv.`product_id`) INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.`product_id` = p.`product_id`) INNER JOIN `" . DB_PREFIX . "product_description` pd ON (pd.`product_id` = p.`product_id`) WHERE pv.`video` != '' AND pv.`poster` != '' AND p.`status` = '1' AND p.`date_available` <= NOW() AND p.`age_minimum` = '0' AND p2s.`store_id` = '" . (int)$store_id . "' AND pd.`language_id` = '" . (int)$language_id . "' ORDER BY pv.`product_id` ASC, pv.`sort_order` ASC, pv.`product_video_id` ASC");

	$videos = [];
	$used = [];
	$files = [];

	foreach (($query->rows ?? []) as $row) {
		$product_id = (int)$row['product_id'];

		// One video per product, the same one the product page shows
		if (isset($used[$product_id])) {
			continue;
		}

		$used[$product_id] = true;

		foreach ([$row['video'], $row['poster']] as $file) {
			if (!isset($files[$file])) {
				$files[$file] = is_file(DIR_IMAGE . $file);
			}
		}

		if (!$files[$row['video']] || !$files[$row['poster']]) {
			continue;
		}

		$code = videoWatchCode($row['video']);

		if (!isset($videos[$code])) {
			$title = videoWatchName((string)$row['name']);

			$videos[$code] = [
				'code'        => $code,
				'slug'        => videoWatchSlug($title),
				'title'       => $title,
				'description' => videoWatchText((string)$row['description']),
				'video'       => $row['video'],
				'poster'      => $row['poster'],
				'width'       => (int)$row['width'],
				'height'      => (int)$row['height'],
				'duration'    => (int)round((float)$row['duration']),
				'product_id'  => $product_id,
				'products'    => []
			];
		}

		$videos[$code]['products'][] = $product_id;
	}

	uasort($videos, fn(array $a, array $b): int => [$a['slug'], $a['code']] <=> [$b['slug'], $b['code']]);

	$cache[$store_id] = $videos;

	return $cache[$store_id];
}

/**
 * Short code of a video: 8 characters calculated from its file name.
 *
 * @param string $video Path of the video relative to the image folder
 *
 * @return string
 */
function videoWatchCode(string $video): string {
	return substr(md5(basename($video)), 0, 8);
}

/**
 * Product name as plain text, without trailing variant suffixes such as "[Blue-L]".
 *
 * @param string $name Product name as stored in the database
 *
 * @return string
 */
function videoWatchName(string $name): string {
	$name = videoWatchText($name);

	$short = trim((string)preg_replace('/(\s*\[[^\[\]]*\])+$/u', '', $name));

	return ($short !== '') ? $short : $name;
}

/**
 * Variant suffix of a product name without the brackets, or the whole name when it has none.
 *
 * @param string $name Product name as stored in the database
 *
 * @return string
 */
function videoWatchVariant(string $name): string {
	$name = videoWatchText($name);

	if (preg_match('/\[([^\[\]]+)\]$/u', $name, $match)) {
		return trim($match[1]);
	}

	return $name;
}

/**
 * Lower case words joined by hyphens, cut at a whole word.
 *
 * @param string $name   Plain text
 * @param int    $length Maximum length
 *
 * @return string
 */
function videoWatchSlug(string $name, int $length = 50): string {
	$name = str_replace(["'", "\u{2019}"], '', $name);

	$ascii = false;

	if (function_exists('transliterator_transliterate')) {
		$ascii = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
	} elseif (function_exists('iconv')) {
		$ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
	}

	if (is_string($ascii)) {
		$name = $ascii;
	}

	$slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');

	if (strlen($slug) > $length) {
		$cut = substr($slug, 0, $length + 1);
		$end = strrpos($cut, '-');

		$slug = trim(substr($cut, 0, ($end !== false) ? $end : $length), '-');
	}

	return ($slug !== '') ? $slug : 'product';
}

/**
 * SEO path of a watch page, for example "video/portable-dog-sling-97668959".
 *
 * @param array<string, mixed> $video Entry of videoWatchList()
 *
 * @return string
 */
function videoWatchPath(array $video): string {
	return 'video/' . $video['slug'] . '-' . $video['code'];
}

/**
 * Full address of a watch page. The ampersand is not HTML encoded.
 *
 * @param string               $base    Store address ending with a slash
 * @param bool                 $seo_url Whether the store uses SEO URLs
 * @param array<string, mixed> $video   Entry of videoWatchList()
 *
 * @return string
 */
function videoWatchUrl(string $base, bool $seo_url, array $video): string {
	return $base . ($seo_url ? videoWatchPath($video) : 'index.php?route=product/video/watch&video=' . $video['code']);
}

/**
 * Full address of the page listing all videos.
 *
 * @param string $base    Store address ending with a slash
 * @param bool   $seo_url Whether the store uses SEO URLs
 *
 * @return string
 */
function videoWatchHubUrl(string $base, bool $seo_url): string {
	return $base . ($seo_url ? 'videos' : 'index.php?route=product/video');
}

/**
 * Full address of a video or cover image stored in the image folder.
 *
 * @param string $base Store address ending with a slash
 * @param string $path Path relative to the image folder
 *
 * @return string
 */
function videoWatchImageUrl(string $base, string $path): string {
	return $base . 'image/' . implode('/', array_map('rawurlencode', explode('/', $path)));
}

/**
 * Plain text, on one line, from a stored (HTML encoded) text or description.
 *
 * Supplier descriptions can be encoded twice, so entities are decoded before and after
 * the tags are removed.
 *
 * @param string $html
 *
 * @return string
 */
function videoWatchText(string $html): string {
	$text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = (string)preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $text);
	$text = (string)preg_replace('/<[^<>]*>/', ' ', $text);
	$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

	return trim((string)preg_replace('/[\s\x{00A0}]+/u', ' ', $text));
}
