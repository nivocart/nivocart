<?php
/**
 * Library Class Mail
 *
 * @package NivoCart
 */
class Mail {
	protected string $to = '';
	protected string $from = '';
	protected string $sender = '';
	protected string $reply_to = '';
	protected string $subject = '';
	protected string $text = '';
	protected string $html = '';

	protected array $attachments = [];

	public string $protocol = 'mail';
	public string $smtp_hostname = '';
	public string $smtp_username = '';
	public string $smtp_password = '';
	public int $smtp_port = 25;
	public int $smtp_timeout = 5;
	public bool $smtp_fallback = false;
	public $verp = false;
	public string $parameter = '';

	/**
	 * Site-wide defaults, filled once at start-up by Mail::configure() from the
	 * Settings > Mail tab. Every "new Mail()" in the code base picks them up.
	 *
	 * @var array<string, mixed>
	 */
	protected static array $defaults = [];

	/**
	 * Read the Mail settings from the Config object and keep them as the defaults
	 * for every Mail object created afterwards. Falls back to PHP mail() when SMTP
	 * is not selected or the SMTP details are incomplete.
	 *
	 * @param object $config
	 *
	 * @return void
	 */
	public static function configure(object $config): void {
		self::$defaults = [];

		if ($config->get('config_mail_protocol') !== 'smtp') {
			return;
		}

		$hostname = trim((string)$config->get('config_mail_smtp_hostname'));
		$username = trim((string)$config->get('config_mail_smtp_username'));
		$password = html_entity_decode((string)$config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');

		if (($hostname === '') || ($username === '') || ($password === '')) {
			return;
		}

		$encryption = $config->get('config_mail_smtp_encryption');

		if ($encryption === 'ssl') {
			$hostname = 'ssl://' . $hostname;
		} elseif ($encryption === 'tls') {
			$hostname = 'tls://' . $hostname;
		}

		$port = (int)$config->get('config_mail_smtp_port');

		self::$defaults = [
			'protocol'      => 'smtp',
			'smtp_hostname' => $hostname,
			'smtp_username' => $username,
			'smtp_password' => $password,
			'smtp_port'     => $port ? $port : 465,
			'smtp_timeout'  => 10,
			'smtp_fallback' => true
		];
	}

	/**
	 * Constructor
	 *
	 * @param array<string, mixed> $config
	 */
	public function __construct(array $config = []) {
		foreach (self::$defaults as $key => $value) {
			$this->$key = $value;
		}

		foreach ($config as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Set To
	 *
	 * @param string $to
	 *
	 * @return void
	 */
	public function setTo($to): void {
		$this->to = $to;
	}

	/**
	 * Set From
	 *
	 * @param string $from
	 *
	 * @return void
	 */
	public function setFrom(string $from): void {
		$this->from = $from;
	}

	/**
	 * Set Sender
	 *
	 * @param string $sender
	 *
	 * @return void
	 */
	public function setSender(string $sender): void {
		$this->sender = $sender;
	}

	/**
	 * Set Reply To
	 *
	 * @param string $reply_to
	 *
	 * @return void
	 */
	public function setReplyTo(string $reply_to): void {
		$this->reply_to = $reply_to;
	}

	/**
	 * Set Subject
	 *
	 * @param string $subject
	 *
	 * @return void
	 */
	public function setSubject(string $subject): void {
		$this->subject = $subject;
	}

	/**
	 * Set Text
	 *
	 * @param string $text
	 *
	 * @return void
	 */
	public function setText(string $text): void {
		$this->text = $text;
	}

	/**
	 * Set Html
	 *
	 * @param string $html
	 *
	 * @return void
	 */
	public function setHtml(string $html): void {
		$this->html = $html;
	}

	/**
	 * Add Attachment
	 *
	 * @param string $filename
	 *
	 * @return void
	 */
	public function addAttachment(string $filename): void {
		$this->attachments[] = $filename;
	}

	/**
	 * Send
	 */
	public function send() {
		if (!$this->to) {
			throw new \Exception('Error: E-Mail to required!');
		}

		if (!$this->from) {
			throw new \Exception('Error: E-Mail from required!');
		}

		if (!$this->sender) {
			throw new \Exception('Error: E-Mail sender required!');
		}

		if (!$this->subject) {
			throw new \Exception('Error: E-Mail subject required!');
		}

		if ((!$this->text) && (!$this->html)) {
			throw new \Exception('Error: E-Mail message required!');
		}

		if (is_array($this->to)) {
			$to = implode(',', $this->to);
		} else {
			$to = $this->to;
		}

		$from = $this->from;
		$reply_to = $this->reply_to;
		$reply_name = $this->reply_to ? $this->reply_to : $this->sender;

		// With SMTP the message must be sent as the mailbox that is logged in, otherwise SPF/DKIM
		// do not line up with the From address. Replies still go to the address the code asked for.
		if (($this->protocol === 'smtp') && filter_var($this->smtp_username, FILTER_VALIDATE_EMAIL)) {
			if (!$reply_to && (strcasecmp($from, $this->smtp_username) !== 0)) {
				$reply_to = $from;
				$reply_name = $this->sender;
			}

			$from = $this->smtp_username;
		}

		if (!$reply_to) {
			$reply_to = $from;
			$reply_name = $this->sender;
		}

		$boundary = '----=_NextPart_' . bin2hex(random_bytes(16));

		$message = $this->buildMessage($boundary);

		if ($this->protocol === 'smtp') {
			try {
				$this->sendSmtp($from, $to, $this->buildHeader($boundary, true, $to, $from, $reply_to, $reply_name), $message);

				return;
			} catch (\Exception $e) {
				if (!$this->smtp_fallback) {
					throw $e;
				}

				// Never lose a customer email because SMTP is down: log it and use PHP mail() instead.
				error_log('Mail SMTP failed, falling back to mail(): ' . $e->getMessage());
			}
		}

		ini_set('sendmail_from', $from);

		if ($this->parameter) {
			mail($to, '=?UTF-8?B?' . base64_encode($this->subject) . '?=', $message, $this->buildHeader($boundary, false, $to, $from, $reply_to, $reply_name), $this->parameter);
		} else {
			mail($to, '=?UTF-8?B?' . base64_encode($this->subject) . '?=', $message, $this->buildHeader($boundary, false, $to, $from, $reply_to, $reply_name));
		}
	}

	/**
	 * Build the message headers.
	 *
	 * @param string $boundary
	 * @param bool   $with_recipient SMTP needs the To and Subject headers, mail() adds them itself.
	 * @param string $to
	 * @param string $from
	 * @param string $reply_to
	 * @param string $reply_name
	 *
	 * @return string
	 */
	private function buildHeader(string $boundary, bool $with_recipient, string $to, string $from, string $reply_to, string $reply_name): string {
		$header = 'MIME-Version: 1.0' . PHP_EOL;

		if ($with_recipient) {
			$header .= 'To: <' . $to . '>' . PHP_EOL;
			$header .= 'Subject: =?UTF-8?B?' . base64_encode($this->subject) . '?=' . PHP_EOL;
		}

		$header .= 'Date: ' . date('D, d M Y H:i:s O') . PHP_EOL;
		$header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $from . '>' . PHP_EOL;
		$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($reply_name) . '?= <' . $reply_to . '>' . PHP_EOL;
		$header .= 'Return-Path: ' . $from . PHP_EOL;
		$header .= 'X-Mailer: PHP/' . phpversion() . PHP_EOL;
		$header .= 'Content-Type: multipart/related; boundary="' . $boundary . '"' . PHP_EOL . PHP_EOL;

		return $header;
	}

	/**
	 * Build the message body: plain text, HTML and attachments.
	 *
	 * @param string $boundary
	 *
	 * @return string
	 */
	private function buildMessage(string $boundary): string {
		if (!$this->html) {
			$message = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
			$message .= $this->text . PHP_EOL;
		} else {
			$message = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . PHP_EOL . PHP_EOL;
			$message .= '--' . $boundary . '_alt' . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

			// A real plain text version (built from the HTML) scores better with spam filters than a stub.
			$message .= ($this->text ? $this->text : $this->htmlToText($this->html)) . PHP_EOL;

			$message .= '--' . $boundary . '_alt' . PHP_EOL;
			$message .= 'Content-Type: text/html; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
			$message .= $this->html . PHP_EOL;
			$message .= '--' . $boundary . '_alt--' . PHP_EOL;
		}

		foreach ($this->attachments as $attachment) {
			if (file_exists($attachment)) {
				$handle = fopen($attachment, 'r');

				$content = fread($handle, filesize($attachment));

				fclose($handle);

				$message .= '--' . $boundary . PHP_EOL;
				$message .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . PHP_EOL;
				$message .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
				$message .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . PHP_EOL;
				$message .= 'Content-ID: <' . basename(urlencode($attachment)) . '>' . PHP_EOL;
				$message .= 'X-Attachment-Id: ' . basename(urlencode($attachment)) . PHP_EOL . PHP_EOL;
				$message .= chunk_split(base64_encode($content));
			}
		}

		return $message . '--' . $boundary . '--' . PHP_EOL;
	}

	/**
	 * Turn an HTML email into readable plain text.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	private function htmlToText(string $html): string {
		$html = preg_replace('#<(head|style|script)[^>]*>.*?</\1>#is', '', $html);

		$html = preg_replace_callback('#<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', function ($match) {
			$label = trim(strip_tags($match[2]));

			if ($label === '') {
				return '';
			}

			return ($label === $match[1]) ? $label : $label . ' (' . $match[1] . ')';
		}, $html);

		// Line breaks in the HTML source are only white space, the tags decide where the lines break.
		$html = preg_replace('/\s*[\r\n]+\s*/', ' ', $html);
		$html = preg_replace('#<\s*br\s*/?>|</(p|div|tr|h[1-6]|li|table)>#i', "\n", $html);

		$text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
		$text = preg_replace("/[ \t\xC2\xA0]+/", ' ', $text);
		$text = preg_replace("/ *\n */", "\n", $text);
		$text = preg_replace("/\n{3,}/", "\n\n", $text);

		return trim($text);
	}

	/**
	 * Read one (possibly multi line) SMTP reply.
	 *
	 * @param resource $handle
	 *
	 * @return string
	 */
	private function smtpRead($handle): string {
		$reply = '';

		while (($line = fgets($handle, 515)) !== false) {
			$reply .= $line;

			// "250 text" or a bare "250" ends the reply, "250-text" means more lines follow.
			if ((substr($line, 3, 1) === ' ') || (strlen(rtrim($line)) === 3)) {
				break;
			}
		}

		return $reply;
	}

	/**
	 * Send one SMTP command and check the reply code.
	 *
	 * @param resource         $handle
	 * @param string           $command
	 * @param array<int, int>  $codes   Accepted reply codes.
	 * @param string           $error
	 *
	 * @return string
	 */
	private function smtpCommand($handle, string $command, array $codes, string $error): string {
		fputs($handle, $command . "\r\n");

		$reply = $this->smtpRead($handle);

		// The reply code is a string, so compare it as an integer.
		if (!in_array((int)substr($reply, 0, 3), $codes, true)) {
			throw new \Exception('Error: ' . $error . ' (' . trim($reply) . ')');
		}

		return $reply;
	}

	/**
	 * Send a message through the SMTP server.
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $header
	 * @param string $message
	 *
	 * @return void
	 */
	private function sendSmtp(string $from, string $to, string $header, string $message): void {
		$hostname = $this->smtp_hostname;
		$starttls = false;

		if (substr($hostname, 0, 3) === 'tls') {
			$hostname = substr($hostname, 6);
			$starttls = true;
		}

		$handle = fsockopen($hostname, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

		if (!$handle) {
			throw new \Exception('Error: ' . $errstr . ' (' . $errno . ')');
		}

		try {
			if (substr(PHP_OS, 0, 3) !== 'WIN') {
				stream_set_timeout($handle, $this->smtp_timeout, 0);
			}

			// Server greeting
			$reply = $this->smtpRead($handle);

			if ((int)substr($reply, 0, 3) !== 220) {
				throw new \Exception('Error: Server greeting not accepted! (' . trim($reply) . ')');
			}

			$host = getenv('SERVER_NAME') ? getenv('SERVER_NAME') : (gethostname() ? gethostname() : 'localhost');

			$this->smtpCommand($handle, 'EHLO ' . $host, [250], 'EHLO not accepted from server!');

			if ($starttls) {
				$this->smtpCommand($handle, 'STARTTLS', [220], 'STARTTLS not accepted from server!');

				if (!stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
					throw new \Exception('Error: Could not start TLS encryption!');
				}

				$this->smtpCommand($handle, 'EHLO ' . $host, [250], 'EHLO not accepted from server!');
			}

			if (!empty($this->smtp_username) && !empty($this->smtp_password)) {
				$this->smtpCommand($handle, 'AUTH LOGIN', [334], 'AUTH LOGIN not accepted from server!');
				$this->smtpCommand($handle, base64_encode($this->smtp_username), [334], 'Username not accepted from server!');
				$this->smtpCommand($handle, base64_encode($this->smtp_password), [235], 'Password not accepted from server!');
			}

			$this->smtpCommand($handle, 'MAIL FROM: <' . $from . '>' . ($this->verp ? 'XVERP' : ''), [250], 'MAIL FROM not accepted from server!');

			$recipients = is_array($this->to) ? $this->to : [$this->to];

			foreach ($recipients as $recipient) {
				$this->smtpCommand($handle, 'RCPT TO: <' . $recipient . '>', [250, 251], 'RCPT TO not accepted from server!');
			}

			$this->smtpCommand($handle, 'DATA', [354], 'DATA not accepted from server!');

			// According to rfc 821 we should not send more than 1000 including the CRLF
			$data = str_replace(["\r\n", "\r"], "\n", $header . $message);

			foreach (explode("\n", $data) as $line) {
				// str_split() returns an empty array for an empty line since PHP 8.2, which would drop
				// the blank line that separates the headers from the body.
				foreach (($line === '') ? [''] : str_split($line, 998) as $result) {
					// A line that starts with a dot must be doubled, or the server reads it as the end of the message.
					if (substr($result, 0, 1) === '.') {
						$result = '.' . $result;
					}

					fputs($handle, $result . "\r\n");
				}
			}

			$this->smtpCommand($handle, '.', [250], 'Message not accepted from server!');

			fputs($handle, 'QUIT' . "\r\n");
		} finally {
			fclose($handle);
		}
	}
}
