<?php
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
	public $verp = false;
	public string $parameter = '';

	/**
	 * Constructor
	 *
	 * @param array<string, mixed> $value
	 */
	public function __construct(array $config = []) {
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

		$boundary = '----=_NextPart_' . md5((string)time());

		$header = 'MIME-Version: 1.0' . PHP_EOL;

		if ($this->protocol != 'mail') {
			$header .= 'To: <' . $to . '>' . PHP_EOL;
			$header .= 'Subject: =?UTF-8?B?' . base64_encode($this->subject) . '?=' . PHP_EOL;
		}

		$header .= 'Date: ' . date('D, d M Y H:i:s O') . PHP_EOL;
		$header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . PHP_EOL;

		if (!$this->reply_to) {
			$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . PHP_EOL;
		} else {
			$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->reply_to) . '?= <' . $this->reply_to . '>' . PHP_EOL;
		}

		$header .= 'Return-Path: ' . $this->from . PHP_EOL;
		$header .= 'X-Mailer: PHP/' . phpversion() . PHP_EOL;
		$header .= 'Content-Type: multipart/related; boundary="' . $boundary . '"' . PHP_EOL . PHP_EOL;

		if (!$this->html) {
			$message = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
			$message .= $this->text . PHP_EOL;
		} else {
			$message  = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . PHP_EOL . PHP_EOL;
			$message .= '--' . $boundary . '_alt' . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

			if ($this->text) {
				$message .= $this->text . PHP_EOL;
			} else {
				$message .= 'This is a HTML email and your email client software does not support HTML email!' . PHP_EOL;
			}

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

		$message .= '--' . $boundary . '--' . PHP_EOL;

		if ($this->protocol == 'mail') {
			ini_set('sendmail_from', $this->from);

			if ($this->parameter) {
				mail($to, '=?UTF-8?B?' . base64_encode($this->subject) . '?=', $message, $header, $this->parameter);
			} else {
				mail($to, '=?UTF-8?B?' . base64_encode($this->subject) . '?=', $message, $header);
			}

		} elseif ($this->protocol == 'smtp') {
			if (substr($this->smtp_hostname, 0, 3) == 'tls') {
				$hostname = substr($this->smtp_hostname, 6);
			} else {
				$hostname = $this->smtp_hostname;
			}

			$handle = fsockopen($hostname, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

			if (!$handle) {
				throw new \Exception('Error: ' . $errstr . ' (' . $errno . ')');
			} else {
				if (substr(PHP_OS, 0, 3) != 'WIN') {
					socket_set_timeout($handle, $this->smtp_timeout, 0);
				}

				while ($line = fgets($handle, 515)) {
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					//some SMTP servers respond with 220 code before responding with 250. hence, we need to ignore 220 response string
					if (substr($reply, 0, 3) == 220 && substr($line, 3, 1) == ' ') {
						$reply = '';
						continue;

					} else if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
					throw new \Exception('Error: EHLO not accepted from server!');
				}

				if (substr($this->smtp_hostname, 0, 3) == 'tls') {
					fputs($handle, 'STARTTLS' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 220) {
						throw new \Exception('Error: STARTTLS not accepted from server!');
					}

					stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
				}

				if (!empty($this->smtp_username)  && !empty($this->smtp_password)) {
					fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
						throw new \Exception('Error: EHLO not accepted from server!');
					}

					fputs($handle, 'AUTH LOGIN' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
						throw new \Exception('Error: AUTH LOGIN not accepted from server!');
					}

					fputs($handle, base64_encode($this->smtp_username) . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
						throw new \Exception('Error: Username not accepted from server!');
					}

					fputs($handle, base64_encode($this->smtp_password) . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 235) {
						throw new \Exception('Error: Password not accepted from server!');
					}

				} else {
					fputs($handle, 'HELO ' . getenv('SERVER_NAME') . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
						throw new \Exception('Error: HELO not accepted from server!');
					}
				}

				if ($this->verp) {
					fputs($handle, 'MAIL FROM: <' . $this->from . '>XVERP' . "\r\n");
				} else {
					fputs($handle, 'MAIL FROM: <' . $this->from . '>' . "\r\n");
				}

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
					throw new \Exception('Error: MAIL FROM not accepted from server!');
				}

				if (!is_array($this->to)) {
					fputs($handle, 'RCPT TO: <' . $this->to . '>' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
						throw new \Exception('Error: RCPT TO not accepted from server!');
					}

				} else {
					foreach ($this->to as $recipient) {
						fputs($handle, 'RCPT TO: <' . $recipient . '>' . "\r\n");

						$reply = '';

						while ($line = fgets($handle, 515)) {
							$reply .= $line;

							if (substr($line, 3, 1) == ' ') {
								break;
							}
						}

						if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
							throw new \Exception('Error: RCPT TO not accepted from server!');
						}
					}
				}

				fputs($handle, 'DATA' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 354) {
					throw new \Exception('Error: DATA not accepted from server!');
				}

				// According to rfc 821 we should not send more than 1000 including the CRLF
				$message = str_replace("\r\n", "\n", $header . $message);
				$message = str_replace("\r", "\n", $message);

				$lines = explode("\n", $message);

				foreach ($lines as $line) {
					$results = str_split($line, 998);

					foreach ($results as $result) {
						if (substr(PHP_OS, 0, 3) != 'WIN') {
							fputs($handle, $result . "\r\n");
						} else {
							fputs($handle, str_replace("\n", "\r\n", $result) . "\r\n");
						}
					}
				}

				fputs($handle, '.' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
					throw new \Exception('Error: DATA not accepted from server!');
				}

				fputs($handle, 'QUIT' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 221) {
					throw new \Exception('Error: QUIT not accepted from server!');
				}

				fclose($handle);
			}
		}
	}
}
