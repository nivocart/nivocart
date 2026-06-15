<?php
/**
 * Class ControllerAccountTokenLogin
 *
 * @package NivoCart
 *
 * Admin token-based customer impersonation login.
 * Called from admin/controller/sale/customer.php login()
 * Never linked from any customer-facing page.
 */
class ControllerAccountTokenLogin extends Controller {
	/** Error array Placeholder */

    public function index() {
        // Must be HTTPS if store requires it
        if ($this->config->get('config_secure') && !$this->request->isSecure()) {
            $this->redirect($this->url->link('account/token_login', 'token=' . $this->request->get['token'], 'SSL'), 301);
        }

        $token = isset($this->request->get['token']) ? trim($this->request->get['token']) : '';

        if (!$token) {
            $this->redirect($this->url->link('account/login', '', 'SSL'));
            return;
        }

        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomerByToken($token);

        if (!$customer_info) {
            // Token not found or already used
            $this->redirect($this->url->link('account/login', '', 'SSL'));
            return;
        }

        // Clear any existing session state
        $this->customer->logout();
        $this->cart->clear();

        $session_keys = [
            'wishlist', 'shipping_address_id', 'shipping_country_id',
            'shipping_zone_id', 'shipping_postcode', 'shipping_method',
            'shipping_methods', 'payment_address_id', 'payment_country_id',
            'payment_zone_id', 'payment_method', 'payment_methods',
            'comment', 'order_id', 'coupon', 'reward', 'voucher', 'vouchers'
        ];

        foreach ($session_keys as $key) {
            unset($this->session->data[$key]);
        }

        // Attempt login
        if (!$this->customer->loginByToken($customer_info['email'])) {
            $this->redirect($this->url->link('account/login', '', 'SSL'));
            return;
        }

        // Burn the token immediately
        $this->model_account_customer->editToken($customer_info['customer_id'], '');

        // Populate address-based tax session data
        $this->load->model('account/address');

        $address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

        if ($address_info) {
            if ($this->config->get('config_tax_customer') === 'shipping') {
                $this->session->data['shipping_country_id'] = $address_info['country_id'];
                $this->session->data['shipping_zone_id'] = $address_info['zone_id'];
                $this->session->data['shipping_postcode'] = $address_info['postcode'];
            }

            if ($this->config->get('config_tax_customer') === 'payment') {
                $this->session->data['payment_country_id'] = $address_info['country_id'];
                $this->session->data['payment_zone_id'] = $address_info['zone_id'];
            }
        }

        // Go straight to the account dashboard
        $this->redirect($this->url->link('account/account', '', 'SSL'));
    }
}
