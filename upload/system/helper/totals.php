<?php
/**
 * Helper Class OrderTotalHelper
 *
 * Shared utilities for order total models
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

    /**
     * Merge tax amounts by tax rate id
     *
     * Total models return tax amounts keyed by tax_rate_id: positive for charges (shipping, fees)
     * and negative for discounts (coupons, rewards). Amounts for the same rate must be added
     * together. The array union operator (+) keeps only the first value for a key, which silently
     * drops them.
     *
     * @param array<int|string, float> $taxes     Tax amounts collected so far
     * @param array<int|string, float> $new_taxes Tax amounts returned by a total model
     *
     * @return array<int|string, float>
     */
    public static function mergeTaxes(array $taxes, array $new_taxes): array {
        foreach ($new_taxes as $tax_rate_id => $amount) {
            $taxes[$tax_rate_id] = (float)($taxes[$tax_rate_id] ?? 0.0) + (float)$amount;
        }

        return $taxes;
    }
}
