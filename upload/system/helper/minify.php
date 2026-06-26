<?php
/**
 * Helper Minify
 *
 * @package NivoCart
 */
function minifyCss(string $source_path): ?string {
    if (!file_exists($source_path)) {
        return null;
    }

    $cache_file = DIR_CACHE . basename($source_path, '.css') . '.min.css';

    // Serve cache if it's newer than the source
    if (file_exists($cache_file) && filemtime($cache_file) >= filemtime($source_path)) {
        return $cache_file;
    }

    $css = file_get_contents($source_path);
    if ($css === false) {
        return null;
    }

    // Strip comments (non-greedy, handles nested * safely)
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    // Remove whitespace around structural characters
    $css = preg_replace('/\s*([{}:;,>~+])\s*/', '$1', $css);
    // Collapse remaining whitespace
    $css = preg_replace('/\s{2,}/', ' ', $css);
    // Strip newlines and tabs
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = trim($css);

    if (file_put_contents($cache_file, $css) === false) {
        return null;
    }

    return $cache_file;
}
