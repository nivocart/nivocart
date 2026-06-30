<?php
/**
 * Helper Minify
 *
 * @package NivoCart
 */

/**
 * Normalise a filesystem path: forward slashes, no trailing slash.
 */
function normalisePath(string $path): string {
	return rtrim(str_replace('\\', '/', $path), '/');
}

/**
 * Build an ordered list of [filesystemRoot, urlPrefix] candidates that map
 * a filesystem path to a root-relative web URL.
 *
 * $_SERVER['DOCUMENT_ROOT'] is the ground truth here: it reflects exactly
 * what the web server considers the root for the *current* request,
 * whatever that happens to be - a WAMP www/ folder with the app nested
 * one or more levels below it, a root-domain Hostinger install, or a
 * bench-style subdomain pointed straight at catalog/. It's only a problem
 * when it's unset (e.g. CLI/cron contexts), not when it's "wrong".
 *
 * DIR_APPLICATION is used two ways:
 *   1. As a sanity check - the resolved image must live inside the
 *      catalog/ tree, or something is misconfigured.
 *   2. As a fallback path-construction strategy ONLY when DOCUMENT_ROOT
 *      is unavailable, covering the two most likely install shapes
 *      (catalog/ itself is the web root, or catalog/'s parent is).
 *
 * @return array<int, array{0: string, 1: string}>
 */
function buildCssWebRootCandidates(): array {
	$candidates = [];

	if (!empty($_SERVER['DOCUMENT_ROOT'])) {
		$realDocRoot = realpath($_SERVER['DOCUMENT_ROOT']);

		if ($realDocRoot !== false) {
			$candidates[] = [normalisePath($realDocRoot), ''];
		}
	}

	// Fallback heuristics, only relevant when DOCUMENT_ROOT wasn't usable above.
	if (empty($candidates) && defined('DIR_APPLICATION')) {
		$realAppDir = realpath(DIR_APPLICATION);

		if ($realAppDir !== false) {
			$appDir = normalisePath($realAppDir);
			$parentDir = normalisePath(dirname($appDir));

			// Guess: subdomain install, doc root === catalog/ folder itself.
			$candidates[] = [$appDir, ''];
			// Guess: doc root === immediate parent of catalog/.
			$candidates[] = [$parentDir, '/catalog'];

			error_log('[minifyCss] DOCUMENT_ROOT unavailable - falling back to DIR_APPLICATION-based guesses. Verify rewritten image urls carefully in this context.');
		} else {
			error_log('[minifyCss] DIR_APPLICATION is defined but realpath() could not resolve it: ' . DIR_APPLICATION);
		}
	}

	if (empty($candidates)) {
		error_log('[minifyCss] No usable web root candidates found - ensure DOCUMENT_ROOT or DIR_APPLICATION is set. Relative image urls cannot be rewritten.');
	}

	return $candidates;
}

/**
 * Resolve a relative CSS url() reference into a root-relative web path.
 *
 * Works for any theme folder name, since it resolves against the actual
 * on-disk location of the source stylesheet rather than assuming a fixed
 * folder structure. Leaves absolute URLs, protocol-relative URLs, and
 * data URIs untouched.
 *
 * @param string $relativeUrl The raw value inside url(...), e.g. ../image/plus.png
 * @param string $cssDir      Absolute filesystem directory containing the source CSS file
 * @param array  $webRoots    Candidates from buildCssWebRootCandidates()
 * @return string|null        Root-relative web path, or null if it could not be resolved
 *                             (caller should leave the original url() untouched)
 */
function resolveCssUrl(string $relativeUrl, string $cssDir, array $webRoots): ?string {
	$relativeUrl = trim($relativeUrl);

	// Already absolute, protocol-relative, or a data URI - leave alone.
	if ($relativeUrl === '' ||
		str_starts_with($relativeUrl, '/') ||
		str_starts_with($relativeUrl, 'data:') ||
		preg_match('#^[a-z][a-z0-9+.\-]*:#i', $relativeUrl) // any scheme:// or scheme:
	) {
		return null;
	}

	$realPath = realpath($cssDir . '/' . $relativeUrl);

	if ($realPath === false) {
		error_log("[minifyCss] Referenced image not found on disk: {$relativeUrl} (resolved from {$cssDir})");
		return null;
	}
	$realPath = normalisePath($realPath);

	// Sanity check: if DIR_APPLICATION is available, the resolved image
	// should live inside the catalog/ tree. If it doesn't, something is
	// misconfigured (e.g. a stray symlink, or DOCUMENT_ROOT pointing at
	// an unrelated location) - flag it rather than silently producing a
	// URL that will 404.
	if (defined('DIR_APPLICATION')) {
		$realAppDir = realpath(DIR_APPLICATION);

		if ($realAppDir !== false && !str_starts_with($realPath, normalisePath($realAppDir))) {
			error_log("[minifyCss] Resolved image path falls outside DIR_APPLICATION: {$realPath}. Check DOCUMENT_ROOT alignment for this environment.");
		}
	}

	foreach ($webRoots as [$fsRoot, $urlPrefix]) {
		if ($fsRoot !== '' && str_starts_with($realPath, $fsRoot)) {
			$webPath = $urlPrefix . substr($realPath, strlen($fsRoot));
			return $webPath === '' ? null : $webPath;
		}
	}

	error_log("[minifyCss] Could not map resolved path to a web URL (no matching root): {$realPath}. Check DIR_APPLICATION / DOCUMENT_ROOT alignment for this environment.");
	return null;
}

/**
 * Rewrite all relative url(...) references in a CSS string to root-relative
 * web paths, resolved against the CSS file's actual directory on disk.
 *
 * @param string $css    Raw CSS content
 * @param string $cssDir Absolute filesystem directory containing the source CSS file
 * @return string
 */
function rewriteCssUrls(string $css, string $cssDir): string {
	$webRoots = buildCssWebRootCandidates();

	if (empty($webRoots)) {
		// Already logged in buildCssWebRootCandidates(); nothing safe to rewrite.
		return $css;
	}

	return preg_replace_callback(
		'/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i',
		function (array $matches) use ($cssDir, $webRoots): string {
			$quote = $matches[1];
			$original = $matches[2];

			$resolved = resolveCssUrl($original, $cssDir, $webRoots);

			if ($resolved === null) {
				// Couldn't resolve (or didn't need to) - leave original untouched.
				return $matches[0];
			}

			return 'url(' . $quote . $resolved . $quote . ')';
		},
		$css
	);
}

function minifyCss(string $source_path): ?string {
	if (!file_exists($source_path)) {
		return null;
	}

	$css = file_get_contents($source_path);

	if ($css === false) {
		return null;
	}

	// Rewrite relative image (and other) url() references to root-relative
	// paths BEFORE minifying, while we still know exactly where this CSS
	// file lives on disk. This must happen before inlining, since inline
	// <style> blocks resolve relative urls against the page URL, not the
	// stylesheet's original location.
	$css = rewriteCssUrls($css, dirname($source_path));

	// Strip comments
	$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
	// Remove whitespace around structural characters
	$css = preg_replace('/\s*([{}:;,>~+])\s*/', '$1', $css);
	// Collapse remaining whitespace
	$css = preg_replace('/\s{2,}/', ' ', $css);
	// Strip newlines and tabs
	$css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
	return trim($css);
}
