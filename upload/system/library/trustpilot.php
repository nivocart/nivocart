<?php
/**
 * Library Class Trustpilot
 *
 * Sends review invitation requests to the Trustpilot Automatic Feedback Service (AFS).
 * The request is a normal email sent to the store's unique AFS address. The customer's
 * name, email address and order number travel in a JSON snippet inside the email body.
 * Trustpilot then sends the real invitation to the customer, using the delay, templates
 * and reminders configured in the Trustpilot account.
 *
 * Works on every Trustpilot plan (AFS is included in the Free plan) and needs no API key.
 * Used by both Catalog (order confirm / update) and Admin (order history).
 *
 * @package NivoCart
 */
class Trustpilot {
	private $registry;
	private $db;
	private $config;

	/**
	 * Constructor
	 *
	 * @param object $registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
	}

	/**
	 * Is the integration switched on and configured?
	 *
	 * @return bool
	 */
	public function isEnabled(): bool {
		return (bool)$this->config->get('trustpilot_status') && emailIsValid((string)$this->config->get('trustpilot_afs_email'));
	}

	/**
	 * Send the invitation request for an order. Safe to call more than once for the
	 * same order: an order that was already sent successfully is never sent again.
	 *
	 * @param int $order_id
	 *
	 * @return string disabled | duplicate | missing | skipped | sent | failed
	 */
	public function invite(int $order_id): string {
		if (!$this->isEnabled()) {
			return 'disabled';
		}

		$sent_query = $this->db->query("SELECT `invitation_id` FROM `" . DB_PREFIX . "trustpilot_invitation` WHERE `order_id` = '" . (int)$order_id . "' AND `status` = 'sent' LIMIT 1");

		if ($sent_query->row) {
			return 'duplicate';
		}

		$order_query = $this->db->query("SELECT `order_id`, `firstname`, `lastname`, `email` FROM `" . DB_PREFIX . "order` WHERE `order_id` = '" . (int)$order_id . "'");

		if (!$order_query->row) {
			return 'missing';
		}

		$order = $order_query->row;

		$email = trim((string)$order['email']);
		$name = trim(html_entity_decode($order['firstname'] . ' ' . $order['lastname'], ENT_QUOTES, 'UTF-8'));

		if (!emailIsValid($email)) {
			$this->record($order_id, (string)$order_id, 'skipped', 'No valid customer email address');

			return 'skipped';
		}

		try {
			$this->send($email, $name, (string)$order_id);

			$this->record($order_id, (string)$order_id, 'sent', '');

			return 'sent';
		} catch (\Throwable $e) {
			$this->record($order_id, (string)$order_id, 'failed', $e->getMessage());

			return 'failed';
		}
	}

	/**
	 * Send a test invitation request. This is a real invitation: Trustpilot will email
	 * the given address and it counts towards the monthly invitation limit.
	 *
	 * @param string $email
	 *
	 * @throws \Exception when the integration is not enabled or the email cannot be sent
	 *
	 * @return void
	 */
	public function sendTest(string $email): void {
		if (!$this->isEnabled()) {
			throw new \Exception('Trustpilot is not enabled or the AFS email address is missing.');
		}

		$reference = 'TEST-' . date('YmdHis');

		try {
			$this->send($email, 'Test Customer', $reference);

			$this->record(0, $reference, 'sent', 'Test invitation');
		} catch (\Throwable $e) {
			$this->record(0, $reference, 'failed', $e->getMessage());

			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Build and send the AFS email.
	 *
	 * @param string $email
	 * @param string $name
	 * @param string $reference
	 *
	 * @return void
	 */
	private function send(string $email, string $name, string $reference): void {
		$store_name = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$from = trim((string)$this->config->get('trustpilot_sender_email'));

		if (!emailIsValid($from)) {
			$from = (string)$this->config->get('config_email');
		}

		// JSON_HEX_TAG and JSON_HEX_AMP keep customer supplied text from ever closing the script tag
		$json = json_encode([
			'recipientName'  => $name,
			'recipientEmail' => $email,
			'referenceId'    => $reference
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);

		$subject = $store_name . ' - Review invitation request for order ' . $reference;

		$text = 'Review invitation request for order ' . $reference . '. This message is read by Trustpilot and is not sent to the customer.';

		$html = '<html><body>';
		$html .= '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
		$html .= '<script type="application/json+trustpilot">' . $json . '</script>';
		$html .= '</body></html>';

		$mail = new Mail();
		$mail->setTo(trim((string)$this->config->get('trustpilot_afs_email')));
		$mail->setFrom($from);
		$mail->setSender($store_name);
		$mail->setSubject($subject);
		$mail->setText($text);
		$mail->setHtml($html);
		$mail->send();
	}

	/**
	 * Record the outcome. A failed or skipped order is updated in place when it is tried
	 * again, so each order has at most one row for its latest attempt.
	 *
	 * @param int    $order_id  0 for a test invitation
	 * @param string $reference
	 * @param string $status    sent | failed | skipped
	 * @param string $message
	 *
	 * @return void
	 */
	private function record(int $order_id, string $reference, string $status, string $message): void {
		$message = mb_substr($message, 0, 255, 'UTF-8');

		$existing_id = 0;

		if ($order_id) {
			$query = $this->db->query("SELECT `invitation_id` FROM `" . DB_PREFIX . "trustpilot_invitation` WHERE `order_id` = '" . (int)$order_id . "' AND `status` != 'sent' ORDER BY `invitation_id` DESC LIMIT 1");

			if ($query->row) {
				$existing_id = (int)$query->row['invitation_id'];
			}
		}

		if ($existing_id) {
			$this->db->query("UPDATE `" . DB_PREFIX . "trustpilot_invitation` SET `status` = '" . $this->db->escape($status) . "', `message` = '" . $this->db->escape($message) . "', `date_added` = NOW() WHERE `invitation_id` = '" . (int)$existing_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "trustpilot_invitation` SET `order_id` = '" . (int)$order_id . "', `reference` = '" . $this->db->escape($reference) . "', `status` = '" . $this->db->escape($status) . "', `message` = '" . $this->db->escape($message) . "', `date_added` = NOW()");
		}
	}
}
