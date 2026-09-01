<?php
/**
 * Class HmrcMtd
 *
 * HMRC Making Tax Digital API Client.
 * Handles OAuth 2.0 token flow, mandatory fraud prevention headers,
 * and authenticated GET / POST calls to HMRC's sandbox and production APIs.
 *
 * Loaded on-demand by modification controllers via:
 *   require_once DIR_SYSTEM . 'library/hmrc_mtd.php';
 *   $hmrc = new HmrcMtd($client_id, $client_secret, $sandbox);
 *
 * @package NivoCart
 */
class HmrcMtd {
    // -----------------------------------------------------------------------
    // Endpoints
    // -----------------------------------------------------------------------
    const AUTH_URL = 'https://www.tax.service.gov.uk/oauth/authorize';
    const TOKEN_SANDBOX = 'https://test-api.service.hmrc.gov.uk/oauth/token';
    const TOKEN_PRODUCTION = 'https://api.service.hmrc.gov.uk/oauth/token';
    const API_SANDBOX = 'https://test-api.service.hmrc.gov.uk';
    const API_PRODUCTION = 'https://api.service.hmrc.gov.uk';

    // MTD module version — included in Gov-Vendor-Version fraud header
    const MOD_VERSION = '1.0.0';

    // -----------------------------------------------------------------------
    // Properties
    // -----------------------------------------------------------------------
    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;

    // -----------------------------------------------------------------------
    // Constructor
    // -----------------------------------------------------------------------
    public function __construct(string $client_id, string $client_secret, bool $sandbox = true) {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->sandbox = $sandbox;
    }

    // -----------------------------------------------------------------------
    // OAuth 2.0
    // -----------------------------------------------------------------------

    /**
     * Build the HMRC Government Gateway authorisation URL.
     * The merchant is redirected here to grant access.
     *
     * @param string $scope        e.g. 'write:vat read:vat'
     * @param string $state        CSRF nonce — store in DB before redirecting
     * @param string $redirect_uri Must match the URI registered on HMRC Developer Hub
     */
    public function getAuthorisationUrl(string $scope, string $state, string $redirect_uri): string {
        return self::AUTH_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'scope'         => $scope,
            'state'         => $state,
            'redirect_uri'  => $redirect_uri,
        ]);
    }

    /**
     * Exchange an authorisation code for access + refresh tokens.
     * Called by the catalog OAuth callback controller.
     *
     * @return array ['access_token', 'refresh_token', 'expires_in', ...] or ['error' => ...]
     */
    public function exchangeCodeForTokens(string $code, string $redirect_uri): array {
        return $this->tokenRequest([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirect_uri,
        ]);
    }

    /**
     * Refresh an expired access token silently.
     *
     * @return array ['access_token', 'refresh_token', 'expires_in', ...] or ['error' => ...]
     */
    public function refreshAccessToken(string $refresh_token): array {
        return $this->tokenRequest([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * Check whether a stored access token has expired.
     * Applies a 5-minute buffer so tokens are refreshed before they actually expire.
     */
    public function isTokenExpired(string $expires_at): bool {
        return strtotime($expires_at) <= (time() + 300);
    }

    // -----------------------------------------------------------------------
    // API calls
    // -----------------------------------------------------------------------

    /**
     * Authenticated GET request to an HMRC API endpoint.
     *
     * @param string $endpoint     e.g. '/organisations/vat/{vrn}/obligations'
     * @param string $access_token Current OAuth access token
     * @param string $username     Admin username — used in fraud prevention headers
     * @param array  $params       Query string parameters
     * @return array Decoded JSON response, always including 'http_code'
     */
    public function get(string $endpoint, string $access_token, string $username, array $params = []): array {
        $url = $this->getBaseUrl() . $endpoint;

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $this->call($url, 'GET', $access_token, $username);
    }

    /**
     * Authenticated POST request to an HMRC API endpoint.
     *
     * @param string $endpoint     e.g. '/organisations/vat/{vrn}/returns'
     * @param string $access_token Current OAuth access token
     * @param string $username     Admin username — used in fraud prevention headers
     * @param array  $payload      Request body (will be JSON-encoded)
     * @return array Decoded JSON response, always including 'http_code'
     */
    public function post(string $endpoint, string $access_token, string $username, array $payload): array {
        return $this->call($this->getBaseUrl() . $endpoint, 'POST', $access_token, $username, $payload);
    }

    // -----------------------------------------------------------------------
    // Fraud Prevention Headers (mandatory on every API call)
    // https://developer.service.hmrc.gov.uk/guides/fraud-prevention/
    // -----------------------------------------------------------------------

    /**
     * Assemble the full set of Gov-Client-* and Gov-Vendor-* fraud prevention headers.
     * HMRC will reject requests that are missing or malformed.
     */
    public function buildFraudHeaders(string $username): array {
        return [
            'Gov-Client-Connection-Method: OTHER_DIRECT',
            'Gov-Client-Device-ID: ' . $this->getDeviceId(),
            'Gov-Client-Timezone: UTC+00:00',
            'Gov-Client-User-IDs: adm=' . urlencode($username),
            'Gov-Client-User-Agent: ' . PHP_OS . '/' . PHP_VERSION . ' (NivoCart/' . VERSION . ')',
            'Gov-Client-Multi-Factor: type=OTHER&timestamp=' . urlencode(gmdate('Y-m-d\TH:i:s\Z')) . '&unique-ref=' . urlencode(substr(hash('sha256', $username . microtime(true)), 0, 32)),
            'Gov-Vendor-Product-Name: NivoCart HMRC MTD',
            'Gov-Vendor-Version: NivoCart-HMRC-MTD=' . self::MOD_VERSION,
            'Gov-Vendor-License-IDs: ',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Return the API base URL for the current mode (sandbox or production).
     */
    public function getBaseUrl(): string {
        return $this->sandbox ? self::API_SANDBOX : self::API_PRODUCTION;
    }

    /**
     * Return whether sandbox mode is active.
     */
    public function isSandbox(): bool {
        return $this->sandbox;
    }

    // -----------------------------------------------------------------------
    // Private
    // -----------------------------------------------------------------------

    /**
     * POST to the HMRC token endpoint (exchange or refresh).
     */
    private function tokenRequest(array $payload): array {
        $url = $this->sandbox ? self::TOKEN_SANDBOX : self::TOKEN_PRODUCTION;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        unset($ch);

        if ($error) {
            return ['error' => $error, 'http_code' => 0];
        }

        $data = json_decode($response, true) ?? [];
        $data['http_code'] = $httpCode;

        if ($httpCode !== 200) {
            $data['error'] = $data['error_description'] ?? $data['error'] ?? 'Token request failed (HTTP ' . $httpCode . ')';
        }

        return $data;
    }

    /**
     * Execute an authenticated cURL call with fraud prevention headers.
     */
    private function call(string $url, string $method, string $access_token, string $username, array $payload = []): array {
        $headers = array_merge($this->buildFraudHeaders($username), [
            'Authorization: Bearer ' . $access_token,
            'Accept: application/vnd.hmrc.1.0+json',
            'Content-Type: application/json',
        ]);

        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        unset($ch);

        if ($error) {
            return ['error' => $error, 'http_code' => 0];
        }

        $data = json_decode($response, true) ?? [];
        $data['http_code'] = $httpCode;

        if ($httpCode >= 400) {
            $data['error'] = $data['message'] ?? $data['code'] ?? 'HMRC API error (HTTP ' . $httpCode . ')';
        }

        return $data;
    }

    /**
     * Get or generate a stable UUID for this NivoCart installation.
     * Stored in system/cache so it persists across requests and deployments.
     * HMRC uses this to identify the originating device consistently.
     */
    private function getDeviceId(): string {
        $file = DIR_SYSTEM . 'cache/hmrc_device_id.txt';

        if (is_readable($file)) {
            $id = trim((string)file_get_contents($file));

            if (preg_match('/^[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/', $id)) {
                return $id;
            }
        }

        $id = sprintf(
            '%08x-%04x-4%03x-%04x-%012x',
            random_int(0, 0xFFFFFFFF),
            random_int(0, 0xFFFF),
            random_int(0, 0x0FFF),
            random_int(0x8000, 0xBFFF),
            random_int(0, 0xFFFFFFFFFFFF)
        );

        file_put_contents($file, $id, LOCK_EX);

        return $id;
    }
}
