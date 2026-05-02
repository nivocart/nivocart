<?php
/**
 * Class ModelTotalOffers
 *
 * @package NivoCart
 */
class ModelTotalOffers extends Model {
    private OfferMatcher $matcher;

	/**
	 * Constructor
	 *
	 * @param $registry
	 */
    public function __construct(Registry $registry) {
        parent::__construct($registry);
        $this->matcher = new OfferMatcher();
        $this->loadOfferRules();
    }

	/**
	 * getTotal
	 *
	 * @param array $taxes
	 * @param float $total
	 *
	 * @return array
	 */
    public function getTotal(array $taxes, float $total): array {
        if ($this->matcher->isEmpty()) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $products = $this->cart->getProducts();
        $apply_taxes = (bool)$this->config->get('offers_taxes');
        $one_to_many = 0;

        usort($products, fn($a, $b) => $a['product_id'] <=> $b['product_id']);

        $this->load->model('checkout/offers');

        $discountable_products = array_map(function (array $product): array {
            $product['category_id'] = $this->model_checkout_offers->getCategoryList($product['product_id']);
            return $product;
        }, $products);

        $discount_total = 0.0;
        $tax_adjustments = [];

        foreach ($discountable_products as $i => $trigger_product) {
            $already_discounted = [];

            while ($discountable_products[$i]['quantity'] > 0) {
                if ($one_to_many === 0) {
                    $discountable_products[$i]['quantity']--;
                }

                $item_discount = $this->matcher->matchDiscount(
                    $discountable_products[$i],
                    $discountable_products,
                    $already_discounted,
                    $one_to_many,
                    $apply_taxes,
                    $this->tax,
                    $this->config
                );

                if ($item_discount === 0.0) {
                    if ($one_to_many === 0) {
                        $discountable_products[$i]['quantity']++;
                        break;
                    }

                    if (!empty($already_discounted)) {
                        $discountable_products[$i]['quantity']--;
                        $already_discounted = [];
                        continue;
                    }

                    break;
                }

                $discount_total += $item_discount;

                if ($apply_taxes) {
                    foreach ($discountable_products as $product) {
                        if (!empty($product['tax_class_id']) && $product['total'] > 0) {
                            foreach ($this->tax->getRates($product['total'], $product['tax_class_id']) as $tax_rate) {
                                $tax_adjustments[$tax_rate['tax_rate_id']] = ($tax_adjustments[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
                            }
                        }
                    }
                }
            }
        }

        if ($discount_total <= 0.0) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/offers');

        return [
            'total_data' => [[
                'code'       => 'offers',
                'title'      => $this->language->get('text_offers'),
                'text'       => '-' . $this->currency->format($discount_total, $this->config->get('config_currency')),
                'value'      => -round($discount_total, 2, PHP_ROUND_HALF_UP),
                'sort_order' => $this->config->get('offers_sort_order')
            ]],
            'total' => -$discount_total,
            'taxes' => $tax_adjustments,
        ];
    }

	/**
	 * loadOfferRules
	 *
	 * @return void
	 */
    private function loadOfferRules(): void {
        $this->load->model('checkout/offers');

        $m = $this->model_checkout_offers;

        $groups = [
            ['variation' => OfferVariation::ProToPro, 'results' => $m->getOfferProductProducts()],
            ['variation' => OfferVariation::ProToCat, 'results' => $m->getOfferProductCategories()],
            ['variation' => OfferVariation::CatToPro, 'results' => $m->getOfferCategoryProducts()],
            ['variation' => OfferVariation::CatToCat, 'results' => $m->getOfferCategoryCategories()],
        ];

        foreach ($groups as $group) {
            foreach ($group['results'] ?: [] as $result) {
                $this->matcher->addRule(new OfferRule(
                    item1: $result['one'],
                    item2: $result['two'],
                    type: $result['type'] === 'F' ? '$' : '%',
                    amount: (float)$result['disc'],
                    variation: $group['variation']
                ));
            }
        }
    }
}
