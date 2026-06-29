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

    $css = file_get_contents($source_path);

    if ($css === false) {
        return null;
    }

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
