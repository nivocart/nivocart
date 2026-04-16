<?php
class Cart {
	/**
	 * @var object
	 */
	private object $db;
	/**
	 * @var object
	 */
	private object $config;
	/**
	 * @var object
	 */
	private object $customer;
	/**
	 * @var object
	 */
	private object $session;
	/**
	 * @var object
	 */
	private object $tax;
	/**
	 * @var object
	 */
	private object $weight;
	/**
	 * @var array<int, array<string, mixed>>
	 */
	private array $data = [];

	protected $registry;

	/**
	 * Constructor
	 *
	 * @param 	$registry
	 */
    public function __construct(Registry $registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->tax = $registry->get('tax');
		$this->weight = $registry->get('weight');

		if (!isset($this->session->data['cart']) || !is_array($this->session->data['cart'])) {
			$this->session->data['cart'] = [];
		}
	}

	/**
	 * Get Products
	 *
	 * @return array<int, array<string, mixed>> product records
	 *
	 * @example
	 *
	 * $cart = $this->cart->getProducts();
	 */
	public function getProducts(): array {
		if (!$this->data) {
			foreach ($this->session->data['cart'] as $key => $quantity) {
				$product = explode(':', $key);
				$product_id = $product[0];
				$stock = true;

				// Options: check string before unserialize
				if (!empty($product[1])) {
					$decoded = base64_decode($product[1], true); // strict mode

					if ($decoded === false) {
						throw new \Exception('Error: Invalid base64 encoding in product options.');
					}

					// Basic sanity check: unserialize expects a specific format
					if (!is_string($decoded) || strlen($decoded) === 0) {
						throw new \Exception('Error: Decoded product option is not a valid string.');
					}

					$options = unserialize($decoded, ['allowed_classes' => false]);

					// Enforce expected type
					if (!is_array($options)) {
						throw new \Exception('Error: Product options must be an array.');
					}

				} else {
					$options = [];
				}

				// Profile
				if (!empty($product[2])) {
					$profile_id = $product[2];
				} else {
					$profile_id = 0;
				}

				$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

				if ($product_query->num_rows) {
					$option_price = 0;
					$option_points = 0;
					$option_weight = 0;

					$option_data = [];

					foreach ($options as $product_option_id => $option_value) {
						$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.`type` FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($option_query->num_rows) {
							if ($option_query->row['type'] === 'select' || $option_query->row['type'] === 'radio' || $option_query->row['type'] === 'image') {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$option_value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] === '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] === '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] === '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] === '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] === '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] === '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
										$stock = false;
									}

									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $option_value,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'option_value'            => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);

								} else {
									$this->remove($key);
									continue 2;
								}

							} elseif ($option_query->row['type'] === 'checkbox' && is_array($option_value)) {
								foreach ($option_value as $product_option_value_id) {
									$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

									if ($option_value_query->num_rows) {
										if ($option_value_query->row['price_prefix'] === '+') {
											$option_price += $option_value_query->row['price'];
										} elseif ($option_value_query->row['price_prefix'] === '-') {
											$option_price -= $option_value_query->row['price'];
										}

										if ($option_value_query->row['points_prefix'] === '+') {
											$option_points += $option_value_query->row['points'];
										} elseif ($option_value_query->row['points_prefix'] === '-') {
											$option_points -= $option_value_query->row['points'];
										}

										if ($option_value_query->row['weight_prefix'] === '+') {
											$option_weight += $option_value_query->row['weight'];
										} elseif ($option_value_query->row['weight_prefix'] === '-') {
											$option_weight -= $option_value_query->row['weight'];
										}

										if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
											$stock = false;
										}

										$option_data[] = array(
											'product_option_id'       => $product_option_id,
											'product_option_value_id' => $product_option_value_id,
											'option_id'               => $option_query->row['option_id'],
											'option_value_id'         => $option_value_query->row['option_value_id'],
											'name'                    => $option_query->row['name'],
											'option_value'            => $option_value_query->row['name'],
											'type'                    => $option_query->row['type'],
											'quantity'                => $option_value_query->row['quantity'],
											'subtract'                => $option_value_query->row['subtract'],
											'price'                   => $option_value_query->row['price'],
											'price_prefix'            => $option_value_query->row['price_prefix'],
											'points'                  => $option_value_query->row['points'],
											'points_prefix'           => $option_value_query->row['points_prefix'],
											'weight'                  => $option_value_query->row['weight'],
											'weight_prefix'           => $option_value_query->row['weight_prefix']
										);

									} else {
										$this->remove($key);
										continue 3;
									}
								}

							} elseif ($option_query->row['type'] === 'text' || $option_query->row['type'] === 'textarea' || $option_query->row['type'] === 'file' || $option_query->row['type'] === 'date' || $option_query->row['type'] === 'datetime' || $option_query->row['type'] === 'time') {
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => '',
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => '',
									'name'                    => $option_query->row['name'],
									'option_value'            => $option_value,
									'type'                    => $option_query->row['type'],
									'quantity'                => '',
									'subtract'                => '',
									'price'                   => '',
									'price_prefix'            => '',
									'points'                  => '',
									'points_prefix'           => '',
									'weight'                  => '',
									'weight_prefix'           => ''
								);
							}

						} else {
							$this->remove($key);
							continue 2;
						}
					}

					if ($this->customer->isLogged()) {
						$customer_group_id = $this->customer->getCustomerGroupId();
					} else {
						$customer_group_id = $this->config->get('config_customer_group_id');
					}

					$price = $product_query->row['price'];

					// Product Discounts
					$discount_quantity = 0;

					foreach ($this->session->data['cart'] as $key_2 => $quantity_2) {
						$product_2 = explode(':', $key_2);

						if ($product_2[0] === $product_id) {
							$discount_quantity += $quantity_2;
						}
					}

					$product_discount_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_discount` WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 0,1");

					if ($product_discount_query->num_rows) {
						$price = $product_discount_query->row['price'];
					}

					// Product Specials
					$product_special_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 0,1");

					if ($product_special_query->num_rows) {
						$price = $product_special_query->row['price'];
						$special = true;
					} else {
						$special = false;
					}

					// Reward Points
					$product_reward_query = $this->db->query("SELECT points FROM `" . DB_PREFIX . "product_reward` WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "'");

					if ($product_reward_query->num_rows) {
						$reward = $product_reward_query->row['points'];
					} else {
						$reward = 0;
					}

					// Downloads
					$download_data = array();

					$download_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_download` p2d LEFT JOIN `" . DB_PREFIX . "download` d ON (p2d.download_id = d.download_id) LEFT JOIN `" . DB_PREFIX . "download_description` dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$product_id . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					foreach ($download_query->rows as $download) {
						$download_data[] = array(
							'download_id' => $download['download_id'],
							'name'        => $download['name'],
							'filename'    => $download['filename'],
							'mask'        => $download['mask'],
							'remaining'   => $download['remaining']
						);
					}

					// Stock
					if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $quantity)) {
 						$stock = false;
 					}

					$recurring = false;
					$recurring_frequency = 0;
					$recurring_price = 0;
					$recurring_cycle = 0;
					$recurring_duration = 0;
					$recurring_trial_status = 0;
					$recurring_trial_price = 0;
					$recurring_trial_cycle = 0;
					$recurring_trial_duration = 0;
					$recurring_trial_frequency = 0;

					$profile_name = '';

					if ($profile_id) {
						$profile_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "profile` p JOIN `" . DB_PREFIX . "product_profile` pp ON (pp.profile_id = p.profile_id) AND pp.product_id = " . (int)$product_query->row['product_id'] . " JOIN `" . DB_PREFIX . "profile_description` pd ON (pd.profile_id = p.profile_id) AND pd.language_id = " . (int)$this->config->get('config_language_id') . " WHERE pp.profile_id = " . (int)$profile_id . " AND status = 1 AND pp.customer_group_id = " . (int)$customer_group_id)->row;

						if ($profile_info) {
							$profile_name = $profile_info['name'];

							$recurring = true;
							$recurring_frequency = $profile_info['frequency'];
							$recurring_price = $profile_info['price'];
							$recurring_cycle = $profile_info['cycle'];
							$recurring_duration = $profile_info['duration'];
							$recurring_trial_frequency = $profile_info['trial_frequency'];
							$recurring_trial_status = $profile_info['trial_status'];
							$recurring_trial_price = $profile_info['trial_price'];
							$recurring_trial_cycle = $profile_info['trial_cycle'];
							$recurring_trial_duration = $profile_info['trial_duration'];
						}
					}

					$this->data[$key] = array(
						'key'                       => $key,
						'product_id'                => $product_query->row['product_id'],
						'name'                      => $product_query->row['name'],
						'model'                     => $product_query->row['model'],
						'shipping'                  => $product_query->row['shipping'],
						'image'                     => $product_query->row['image'],
						'label'                     => $product_query->row['label'],
						'option'                    => $option_data,
						'download'                  => $download_data,
						'quantity'                  => $quantity,
						'minimum'                   => $product_query->row['minimum'],
						'subtract'                  => $product_query->row['subtract'],
						'stock'                     => $stock,
						'price'                     => ($price + $option_price),
						'special'                   => $special,
						'cost'                      => $product_query->row['cost'],
						'quote'                     => $product_query->row['quote'],
						'age_minimum'               => $product_query->row['age_minimum'],
						'total'                     => ($price + $option_price) * $quantity,
						'reward'                    => $reward * $quantity,
						'points'                    => $product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $quantity : 0,
						'tax_class_id'              => $product_query->row['tax_class_id'],
						'weight'                    => ($product_query->row['weight'] + $option_weight) * $quantity,
						'weight_class_id'           => $product_query->row['weight_class_id'],
						'length'                    => $product_query->row['length'],
						'width'                     => $product_query->row['width'],
						'height'                    => $product_query->row['height'],
						'length_class_id'           => $product_query->row['length_class_id'],
						'profile_id'                => $profile_id,
						'profile_name'              => $profile_name,
						'recurring'                 => $recurring,
						'recurring_frequency'       => $recurring_frequency,
						'recurring_price'           => $recurring_price,
						'recurring_cycle'           => $recurring_cycle,
						'recurring_duration'        => $recurring_duration,
						'recurring_trial'           => $recurring_trial_status,
						'recurring_trial_frequency' => $recurring_trial_frequency,
						'recurring_trial_price'     => $recurring_trial_price,
						'recurring_trial_cycle'     => $recurring_trial_cycle,
						'recurring_trial_duration'  => $recurring_trial_duration
					);

				} else {
					$this->remove($key);
				}
			}
		}

		return $this->data;
	}

	/**
	 * Get Recurring Products
	 *
	 * @return array
	 *
	 * @example
	 *
	 * $this->cart->getRecurringProducts();
	 */
	public function getRecurringProducts(): array {
		$recurring_products = [];

		foreach ($this->getProducts() as $key => $value) {
			if ($value['recurring']) {
				$recurring_products[$key] = $value;
			}
		}

		return $recurring_products;
	}

	/**
	 * Add
	 *
	 * @param int          $product_id           primary key of the product record
	 * @param int          $profile_id
	 * @param int          $quantity
	 * @param array<mixed> $option
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->cart->add($product_id, $profile_id, $quantity, $option);
	 */
	public function add(int $product_id, int $profile_id, int $quantity = 1, array $option = []): void {
		$key = (int)$product_id . ':';

		if ($option || !is_array($option)) {
			$key .= base64_encode(serialize($option)) . ':';
		} else {
			$key .= ':';
		}

		if ($profile_id) {
			$key .= (int)$profile_id;
		}

		if ((int)$quantity && ((int)$quantity > 0)) {
			if (!isset($this->session->data['cart'][$key])) {
				$this->session->data['cart'][$key] = (int)$quantity;
			} else {
				$this->session->data['cart'][$key] += (int)$quantity;
			}
		}
	}

	/**
	 * Update
	 *
	 * @param int $key  primary key of the cart record
	 * @param int $quantity
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->cart->update($key, $quantity);
	 */
	public function update($key, int $quantity): void {
		if ((int)$quantity && ((int)$quantity > 0) && isset($this->session->data['cart'][$key])) {
			$this->session->data['cart'][$key] = (int)$quantity;
		} else {
			$this->remove($key);
		}
	}

	/**
	 * Remove
	 *
	 * @param int $key primary key of the cart record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $cart = $this->cart->remove($key);
	 */
	public function remove($key): void {
		if (isset($this->session->data['cart'][$key])) {
			unset($this->session->data['cart'][$key]);
		}
	}

	/**
	 * Clear
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->cart->clear();
	 */
	public function clear(): void {
		$this->session->data['cart'] = [];

		if (isset($this->session->data['customer_id'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET cart = '' WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "'");
		}
	}

	/**
	 * Get Weight
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $weight = $this->cart->getWeight();
	 */
	public function getWeight(): float {
		$weight = 0;

		foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
				$weight += $this->weight->convert($product['weight'], (int)$product['weight_class_id'], (int)$this->config->get('config_weight_class_id'));
			}
		}

		return $weight;
	}

	/**
	 * Get Sub Total
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $sub_total = $this->cart->getSubTotal();
	 */
	public function getSubTotal(): float {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}

		return $total;
	}

	/**
	 * Get Price From Price With Tax
	 *
	 * @return float>
	 */
	public function getPriceFromPriceWithTax(float $price, int $tax_class_id): float {
		$calculated_price = $price;

		if ($this->config->get('config_tax')) {
			$base = 100;

			$tax_rates = $this->tax->getRates($base, $tax_class_id);

			$rate_percent = 0;

			foreach ($tax_rates as $tax_rate) {
				if ($tax_rate['type'] === 'F') {
					$calculated_price -= $tax_rate['rate'];
				} elseif ($tax_rate['type'] === 'P') {
					$rate_percent += $tax_rate['rate'];
				}
			}

			$calculated_price = $calculated_price / (1 + ((float)$rate_percent) / 100);
		}

		return $calculated_price;
	}

	/**
	 * Get Taxes
	 *
	 * @return array<int, float>
	 *
	 * @example
	 *
	 * $taxes = $this->cart->getTaxes();
	 */
	public function getTaxes(): array {
		$tax_data = [];

		foreach ($this->getProducts() as $product) {
			if ($product['tax_class_id']) {
				$tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
						$tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $product['quantity']);
					} else {
						$tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $product['quantity']);
					}
				}
			}
		}

		return $tax_data;
	}

	/**
	 * Get Total
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $total = $this->cart->getTotal();
	 */
	public function getTotal(): float {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
		}

		return $total;
	}

	/**
	 * Count Products
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $count_products = $this->cart->countProducts();
	 */
	public function countProducts(): int {
		$product_total = 0;

		$products = $this->getProducts();

		foreach ($products as $product) {
			$product_total += $product['quantity'];
		}

		return $product_total;
	}

	/**
	 * Has Products
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $cart = $this->cart->hasProducts();
	 */
	public function hasProducts(): bool {
		return count($this->session->data['cart']);
	}

	/**
	 * Has Recurring Products
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $cart = $this->cart->hasRecurringProducts();
	 */
	public function hasRecurringProducts(): bool {
		return count($this->getRecurringProducts());
	}

	/**
	 * Has Stock
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $cart = $this->cart->hasStock();
	 */
	public function hasStock(): bool {
		foreach ($this->getProducts() as $product) {
			if ($product['stock'] > 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Has Shipping
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $cart = $this->cart->hasShipping();
	 */
	public function hasShipping(): bool {
		foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Has Download
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $cart = $this->cart->hasDownload();
	 */
	public function hasDownload(): bool {
		foreach ($this->getProducts() as $product) {
			if ($product['download']) {
				return true;
			}
		}

		return false;
	}
}
