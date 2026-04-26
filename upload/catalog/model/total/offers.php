<?php

enum OfferVariation: string {
    case ProToPro = '1';
    case ProToCat = '2';
    case CatToCat = '3';
    case CatToPro = '4';
}

class Offer {
    public string $item1;
    public string $item2;
    public string $type;
    public float  $amount;
    public OfferVariation $variation;
    public bool $isvalid = false;

    public function init(string $item1, string $item2, string $type, float $amount, OfferVariation $variation): void {
        if ($type !== '$' && $type !== '%') {
            throw new \InvalidArgumentException("Unknown offer type: $type");
        }

        $this->item1 = $item1;
        $this->item2 = $item2;
        $this->type = $type;
        $this->amount = $amount;
        $this->variation = $variation;
        $this->isvalid = true;
    }

    public function getId(): string {
        return $this->item1;
    }
}

/**
 * Class ModelTotalOffers
 *
 * @package NivoCart
 */
class ModelTotalOffers extends Model {
    protected $registry;
    protected array $discount_list = [];

    public function __construct(Registry $registry) {
        $this->registry = $registry;
        $this->offers();
    }

    public function __get(string $name): mixed {
        return $this->registry->get($name);
    }

    protected function getDiscount(array $discount_item, array &$discountable_products, array &$already_discounted_items = [], int $one_to_many = 0): float {
        $offer_taxes = (bool)$this->config->get('offers_taxes');
        $discount_total = 0.0;

        foreach ($this->discount_list as $line) {
            // Check item1 matches the trigger product or category
            $item1_matches = match(true) {
                $line->variation === OfferVariation::ProToPro,
                $line->variation === OfferVariation::ProToCat
                    => $line->item1 === $discount_item['product_id'],
                default
                    => in_array($line->item1, $discount_item['category_id'], strict: true),
            };

            if (!$item1_matches) {
                continue;
            }

            for ($i = count($discountable_products) - 1; $i >= 0; $i--) {
                if ($discountable_products[$i]['quantity'] === 0) {
                    continue;
                }

                // Check item2 matches the discountable product or category
                $item2_matches = match(true) {
                    $line->variation === OfferVariation::ProToPro,
                    $line->variation === OfferVariation::CatToPro
                        => $discountable_products[$i]['product_id'] === $line->item2,
                    default
                        => in_array($line->item2, $discountable_products[$i]['category_id'], strict: true),
                };

                if (!$item2_matches) {
                    continue;
                }

                if ($one_to_many !== 0) {
                    $pro_id = $discountable_products[$i]['product_id'];

                    if ($one_to_many === 1) {
                        if (in_array($pro_id, $already_discounted_items, strict: true)) {
                            continue;
                        }

                        $already_discounted_items[] = $pro_id;
                    }
                }

                $discountable_products[$i]['quantity']--;

                $product = $discountable_products[$i];
                $tax_class_id = $product['tax_class_id'];

                if ($line->type === '$') {
                    $discount_total = ($offer_taxes && $line->amount > 0) ? (float)$this->tax->calculate($line->amount, $tax_class_id, $this->config->get('config_tax')) : $line->amount;
                } else {
                    $base = $product['price'] * $line->amount;

                    $discount_total = ($offer_taxes && $base > 0) ? (float)$this->tax->calculate($base, $tax_class_id, $this->config->get('config_tax')) / 100 : $base;
                }
            }
        }

        return $discount_total;
    }

    public function getTotal(&$total_data, &$total, &$taxes): void {
        $products = $this->cart->getProducts();

        $offer_taxes = (bool)$this->config->get('offers_taxes');

        usort($products, fn($a, $b) => $a['product_id'] <=> $b['product_id']);

        $this->load->model('checkout/offers');

        // Enrich each product with its category list
        $discountable_products = array_map(function (array $product): array {
            $product['category_id'] = $this->model_checkout_offers->getCategoryList($product['product_id']);
            return $product;
        }, $products);

        $discount_total = 0.0;

        foreach ($discountable_products as $i => $trigger_product) {
            $already_discounted_items = [];

            while ($discountable_products[$i]['quantity'] > 0) {
                if ($one_to_many === 0) {
                    $discountable_products[$i]['quantity']--;
                }

                $item_discountable = $this->getDiscount(
                    $discountable_products[$i],
                    $discountable_products,
                    $already_discounted_items,
                    $one_to_many ?? 0
                );

                if ($item_discountable === 0.0) {
                    if ($one_to_many === 0) {
                        $discountable_products[$i]['quantity']++;
                        break;
                    }

                    if (!empty($already_discounted_items)) {
                        $discountable_products[$i]['quantity']--;
                        $already_discounted_items = [];
                        continue;
                    }

                    break;
                }

                $discount_total += $item_discountable;

                if ($offer_taxes) {
                    foreach ($discountable_products as $product) {
                        if (!empty($product['tax_class_id']) && $product['total'] > 0) {
                            $tax_rates = $this->tax->getRates($product['total'], $product['tax_class_id']);

                            foreach ($tax_rates as $tax_rate) {
                                $taxes[$tax_rate['tax_rate_id']] = ($taxes[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
                            }
                        }
                    }
                }
            }
        }

        if ($discount_total > 0.0) {
            $this->language->load('total/offers');

            $total_data[] = [
                'code'       => 'offers',
                'title'      => $this->language->get('text_offers'),
                'text'       => '-' . $this->currency->format($discount_total, $this->config->get('config_currency')),
                'value'      => -round($discount_total, 2, PHP_ROUND_HALF_UP),
                'sort_order' => $this->config->get('offers_sort_order')
            ];

            $total -= $discount_total;
        }
    }

    protected function addOffer(string $item1, string $item2, string $type, float $amount, OfferVariation $variation): void {
        $offer = new Offer();
        $offer->init($item1, $item2, $type, $amount, $variation);

        if ($offer->isvalid) {
            $this->discount_list[] = $offer;
        }
    }

    protected function offers(): void {
        $this->load->model('checkout/offers');

        $m = $this->model_checkout_offers;

        $groups = [
            OfferVariation::ProToPro => $m->getOfferProductProducts(),
            OfferVariation::ProToCat => $m->getOfferProductCategories(),
            OfferVariation::CatToPro => $m->getOfferCategoryProducts(),
            OfferVariation::CatToCat => $m->getOfferCategoryCategories()
        ];

        foreach ($groups as $variation => $results) {
            foreach ($results ?: [] as $result) {
                $this->addOffer(
                    item1: $result['one'],
                    item2: $result['two'],
                    type: $result['type'] === 'F' ? '$' : '%',
                    amount: (float)$result['disc'],
                    variation: $variation
                );
            }
        }
    }
}
