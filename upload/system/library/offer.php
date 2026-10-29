<?php
/**
 * Library Class Offer
 *
 * @package NivoCart
 *
 * ------------------------
 *
 * Pure offer matching — no registry, no cart, no session
 *
 * Offer Variation
 *
 * @return string
 */
enum OfferVariation: string {
    case ProToPro = '1';
    case ProToCat = '2';
    case CatToCat = '3';
    case CatToPro = '4';
}

/**
 * Offer Rule
 *
 * Constructor
 *
 * @param string $item1
 * @param string $item2
 * @param string $type
 * @param float $amount
 * @param OfferVariation $variation
 */
class OfferRule {
    public readonly string $item1;
    public readonly string $item2;
    public readonly string $type;
    public readonly float $amount;
    public readonly OfferVariation $variation;

    public function __construct(string $item1, string $item2, string $type, float $amount, OfferVariation $variation) {
        if ($type !== '$' && $type !== '%') {
            throw new \InvalidArgumentException("Unknown offer type: $type");
        }

        $this->item1 = $item1;
        $this->item2 = $item2;
        $this->type = $type;
        $this->amount = $amount;
        $this->variation = $variation;
    }

	/**
	 * trigger Matches
	 *
	 * @param array $product
	 *
	 * @return bool
	 */
    public function triggerMatches(array $product): bool {
        return match(true) {
            $this->variation === OfferVariation::ProToPro,
            $this->variation === OfferVariation::ProToCat
                => $this->item1 === $product['product_id'],
            default
                => in_array($this->item1, $product['category_id'], strict: true),
        };
    }

	/**
	 * target Matches
	 *
	 * @param array $product
	 *
	 * @return bool
	 */
    public function targetMatches(array $product): bool {
        return match(true) {
            $this->variation === OfferVariation::ProToPro,
            $this->variation === OfferVariation::CatToPro
                => $product['product_id'] === $this->item2,
            default
                => in_array($this->item2, $product['category_id'], strict: true),
        };
    }

	/**
	 * calculate Discount
	 *
	 * Returns the discount for one unit of the product, excluding tax. The tax on the
	 * discount is worked out by the order total model.
	 *
	 * @param array $product
	 *
	 * @return float
	 */
    public function calculateDiscount(array $product): float {
        $price = (float)$product['price'];

        if ($this->type === '$') {
            return min($this->amount, $price);
        }

        return $price * $this->amount / 100;
    }
}

/**
 * Offer Matcher
 *
 * @param string $item1
 * @param string $item2
 * @param string $type
 * @param float $amount
 * @param OfferVariation $variation
 */
class OfferMatcher {
    /**
	* @var OfferRule[]
	*/
    private array $rules = [];

	/**
	 * add Rule
	 *
	 * @param OfferRule $rule
	 *
	 * @return void
	 */
    public function addRule(OfferRule $rule): void {
        $this->rules[] = $rule;
    }

	/**
	 * get Rules
	 *
	 * @return array
	 */
    public function getRules(): array {
        return $this->rules;
    }

	/**
	 * is Empty
	 *
	 * @return bool
	 */
    public function isEmpty(): bool {
        return empty($this->rules);
    }

    /**
	 * match Discount
	 *
     * Calculate the discount for one trigger product pass against the
     * discountable product pool. Returns the discount amount for this pass.
	 *
	 * @param array  $trigger_product
	 * @param array  &$discountable_products
	 * @param array  &$already_discounted
	 * @param int    $one_to_many
	 * @param array|null $matched_product set to the product the discount was given on, null if none
	 *
	 * @return float
	 */
    public function matchDiscount(array $trigger_product, array &$discountable_products, array &$already_discounted, int $one_to_many, ?array &$matched_product = null): float {
        $matched_product = null;

        foreach ($this->rules as $rule) {
            if (!$rule->triggerMatches($trigger_product)) {
                continue;
            }

            for ($i = count($discountable_products) - 1; $i >= 0; $i--) {
                if ($discountable_products[$i]['quantity'] === 0) {
                    continue;
                }

                if (!$rule->targetMatches($discountable_products[$i])) {
                    continue;
                }

                $pro_id = $discountable_products[$i]['product_id'];

                if ($one_to_many === 1) {
                    if (in_array($pro_id, $already_discounted, strict: true)) {
                        continue;
                    }

                    $already_discounted[] = $pro_id;
                }

                $discountable_products[$i]['quantity']--;

                $matched_product = $discountable_products[$i];

                return $rule->calculateDiscount($discountable_products[$i]);
            }
        }

        return 0.0;
    }
}
