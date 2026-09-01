<?php
/**
 * Class ControllerModificationHmrcMtd (Catalog)
 *
 * Thin OAuth 2.0 callback receiver for HMRC Making Tax Digital.
 * HMRC redirects the merchant here after Government Gateway authentication.
 *
 * URL: index.php?route=modification/hmrc_mtd/callback
 *
 * Flow:
 *   1. Verify the 'state' parameter matches the nonce stored before the redirect.
 *   2. Exchange the authorisation code for access + refresh tokens.
 *   3. Persist tokens in DB.
 *   4. Show a success (or error) page with a link back to the admin panel.
 *
 * @package NivoCart
 */
class ControllerModificationHmrcMtd extends Controller {
    /**
     * OAuth callback entry point.
     * Registered as redirect URI: HTTPS_CATALOG . 'index.php?route=modification/hmrc_mtd/callback'
     */
    public function callback(): void {
        $this->load->model('modification/hmrc_mtd');

        $store_id = 0;

        // ---- 1. Check for error response from HMRC ---------------------------
        if (isset($this->request->get['error'])) {
            $this->renderResult(false, $this->request->get['error_description'] ?? $this->request->get['error']);
            return;
        }

        // ---- 2. Validate required parameters ---------------------------------
        $code = $this->request->get['code'] ?? '';
        $state = $this->request->get['state'] ?? '';

        if (!$code || !$state) {
            $this->renderResult(false, 'Missing code or state parameter.');
            return;
        }

        // ---- 3. Verify state nonce (CSRF protection) -------------------------
        $stored_state = $this->model_modification_hmrc_mtd->getSetting($store_id, 'oauth_state', '');

        if (!$stored_state || !hash_equals($stored_state, $state)) {
            $this->renderResult(false, 'State mismatch — possible CSRF attempt. Please try connecting again.');
            return;
        }

        // Consume the nonce immediately so it cannot be replayed
        $this->model_modification_hmrc_mtd->saveSetting($store_id, 'oauth_state', '');

        // ---- 4. Load settings and exchange code for tokens -------------------
        $settings = $this->model_modification_hmrc_mtd->getSettings($store_id);

        if (empty($settings['client_id']) || empty($settings['client_secret'])) {
            $this->renderResult(false, 'HMRC credentials are not configured. Please save your Client ID and Client Secret first.');
            return;
        }

        require_once DIR_SYSTEM . 'library/hmrc_mtd.php';

        $hmrc = new HmrcMtd($settings['client_id'], $settings['client_secret'], (bool)(int)($settings['sandbox'] ?? 1));

        $redirect_uri = HTTPS_CATALOG . 'index.php?route=modification/hmrc_mtd/callback';

        $tokens = $hmrc->exchangeCodeForTokens($code, $redirect_uri);

        // ---- 5. Handle API error ---------------------------------------------
        if (isset($tokens['error'])) {
            $this->renderResult(false, 'HMRC token exchange failed: ' . $tokens['error']);
            return;
        }

        // ---- 6. Persist tokens -----------------------------------------------
        $this->model_modification_hmrc_mtd->saveTokens($store_id, $tokens['access_token'], $tokens['refresh_token'] ?? '', (int)($tokens['expires_in'] ?? 14400));

        // ---- 7. Show success page --------------------------------------------
        $this->renderResult(true);
    }

    /**
     * Render a minimal success or error page with a link back to the admin panel.
     * No header/footer loaded — keeps the callback page lightweight.
     *
     * @param bool   $success
     * @param string $message  Optional error detail shown on failure
     */
    private function renderResult(bool $success, string $message = ''): void {
        $admin_url = HTTPS_SERVER . 'index.php?route=modification/hmrc_mtd';

        $store_name = $this->config->get('config_name') ?: 'NivoCart';

        if ($success) {
            $title = 'HMRC Connected Successfully';
            $body = '<p style="color:#5cb85c;font-size:1.1em;">&#10003; Your store is now connected to HMRC Making Tax Digital.</p>'
                    . '<p>You can close this window or return to the admin panel to fetch VAT obligations.</p>';
        } else {
            $title = 'HMRC Connection Error';
            $body = '<p style="color:#d9534f;font-size:1.1em;">&#10007; Could not connect to HMRC.</p>'
                    . '<p>' . htmlspecialchars($message) . '</p>'
                    . '<p>Please return to the admin panel, check your credentials, and try again.</p>';
        }

        $html = '<!DOCTYPE html>'
              . '<html><head><meta charset="utf-8" />'
              . '<title>' . htmlspecialchars($title) . ' &mdash; ' . htmlspecialchars($store_name) . '</title>'
              . '<style>'
              . 'body{font-family:Arial,sans-serif;margin:0;padding:40px;background:#f5f5f5;color:#333;}'
              . '.card{max-width:540px;margin:60px auto;background:#fff;border:1px solid #ddd;border-radius:4px;padding:30px 36px;}'
              . 'h1{font-size:1.3em;margin-top:0;}'
              . 'a.btn{display:inline-block;margin-top:18px;padding:9px 20px;background:#337ab7;color:#fff;text-decoration:none;border-radius:3px;}'
              . 'a.btn:hover{background:#286090;}'
              . '</style>'
              . '</head><body>'
              . '<div class="card">'
              . '<h1>' . htmlspecialchars($title) . '</h1>'
              . $body
              . '<p><a href="' . htmlspecialchars($admin_url) . '" class="btn">&larr; Return to HMRC MTD Settings</a></p>'
              . '</div>'
              . '</body></html>';

        $this->response->setOutput($html);
    }
}
