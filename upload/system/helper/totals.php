<?php
/**
 * Class OrderTotalHelper
 *
 * Shared utilities for order total models.
 *
 * @package NivoCart
 */
class OrderTotalHelper {
    /**
     * Extract a code from a title string formatted as "Some Title (CODE)"
     */
    public static function extractCodeFromTitle(string $title): string {
        $start = strpos($title, '(');
        $end = strrpos($title, ')');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($title, $start + 1, $end - $start - 1);
        }

        return '';
    }

    /**
     * Extract a numeric value from a title string formatted as "Some Title (123)"
     */
    public static function extractValueFromTitle(string $title): float {
        return (float)self::extractCodeFromTitle($title);
    }
}
