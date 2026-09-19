<?php
/**
 * filterVariantsBySize
 *
 * Given an array of product data (each element must have 'product_id' and 'name'),
 * returns the array with size variants deduplicated: for each unique
 * (base product name, colour) combination only the variant with the
 * highest-priority size is kept. Products whose names carry no recognised
 * size code are always kept as-is.
 *
 * Recognised bracket patterns:
 *   [Colour-SIZE]   e.g. [Black-M], [Rose Red-XXL]
 *   [SIZE]          e.g. [M], [XL]   (size-only variant, no colour prefix)
 *
 * Non-standard brackets such as [Black 48inch], [Short Orange-Boxed] or
 * [red-S 1.0*30] do not end with a known size code and are never touched.
 *
 * @param  array $products  Sequential or associative array of product data.
 * @return array            Sequential array with size duplicates removed.
 *
 * @package NivoCart
 */
function filterVariantsBySize(array $products): array {
	// Sizes ordered from most preferred (index 0) to least preferred.
	// Edit this list to change which size is chosen when several are available.
	static $SIZE_PRIORITY = ['M', 'L', 'S', 'XL', 'XS', 'XXL', 'XXS', '2XL', '3XL', '4XL', 'XXXL', 'XXXXL'];

	// Longest tokens listed first so the regex engine matches XXXL before XXL,
	// XXL before XL, XXXXL before XXXL, etc.
	$size_alt = 'XXXXL|XXXL|XXL|XXS|4XL|3XL|2XL|XL|XS|[SML]';

	// Pattern 1: [Colour-Size]  – anything before the last hyphen is the colour.
	$re_cs = '/^(.*)\[(.+?)-(' . $size_alt . ')\]$/i';
	// Pattern 2: [Size]         – bracket contains only a size code.
	$re_s  = '/^(.*)\[(' . $size_alt . ')\]$/i';

	// ── First pass: determine the winning product_id per (base, colour) group ──

	$winners = []; // $key => ['product_id' => int, 'rank' => int]

	foreach ($products as $product) {
		$name = $product['name'] ?? '';

		if (preg_match($re_cs, $name, $m)) {
			$base = rtrim($m[1]);
			$colour = strtolower(trim($m[2]));
			$size = strtoupper($m[3]);
			$key = $base . '|||c:' . $colour;
		} elseif (preg_match($re_s, $name, $m)) {
			$base = rtrim($m[1]);
			$size = strtoupper($m[2]);
			$key = $base . '|||size-only';
		} else {
			continue; // not a recognised size variant — skip the winner test
		}

		$rank = array_search($size, $SIZE_PRIORITY, true);

		if ($rank === false) {
			$rank = count($SIZE_PRIORITY); // unknown size → lowest priority
		}

		if (!isset($winners[$key]) || $rank < $winners[$key]['rank']) {
			$winners[$key] = ['product_id' => (int)$product['product_id'], 'rank' => $rank];
		}
	}

	// Build an O(1) lookup set of winning product IDs.
	$winner_ids = [];

	foreach ($winners as $w) {
		$winner_ids[$w['product_id']] = true;
	}

	// ── Second pass: rebuild the list preserving original order ──

	$result = [];

	foreach ($products as $product) {
		$name = $product['name'] ?? '';
		$is_size_variant = preg_match($re_cs, $name) || preg_match($re_s, $name);

		if (!$is_size_variant || isset($winner_ids[(int)$product['product_id']])) {
			$result[] = $product;
		}
		// else: duplicate size variant — silently dropped
	}

	return $result;
}
