<?php

/**
 * Keypair handling for the Platform Connector.
 *
 * Generates a fresh RSA-2048 keypair on demand. The private key is stored
 * encrypted at rest (AES-256-GCM, key derived from SECURE_AUTH_KEY), the
 * public key stays plain — it's meant to be shared with the platform.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector_Keypair
{
    // Namespace mixed into the KDF so the same WP salt can't accidentally
    // decrypt data from another feature that follows the same pattern.
    const KDF_CONTEXT = 'dailybuddy_pc_v1';

    public function __construct()
    {
        // No hooks — keypair is created on demand by the connect flow.
    }

    /**
     * Make sure a keypair exists. Generates one on first call.
     *
     * @return true|WP_Error
     */
    public function ensure_keypair()
    {
        if (get_option(Dailybuddy_Platform_Connector::OPT_PUBLIC_KEY) && get_option(Dailybuddy_Platform_Connector::OPT_PRIVATE_KEY)) {
            return true;
        }

        if (! function_exists('openssl_pkey_new')) {
            return new WP_Error('no_openssl', __('OpenSSL PHP extension is required.', 'dailybuddy'));
        }

        $res = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));

        if (! $res) {
            return new WP_Error('keygen_failed', __('Could not generate keypair.', 'dailybuddy'));
        }

        $private_pem = '';
        if (! openssl_pkey_export($res, $private_pem)) {
            return new WP_Error('key_export_failed', __('Could not export private key.', 'dailybuddy'));
        }

        $details = openssl_pkey_get_details($res);
        if (! $details || empty($details['key'])) {
            return new WP_Error('key_details_failed', __('Could not read public key.', 'dailybuddy'));
        }
        $public_pem = $details['key'];

        $encrypted = $this->encrypt($private_pem);
        if (is_wp_error($encrypted)) {
            return $encrypted;
        }

        update_option(Dailybuddy_Platform_Connector::OPT_PRIVATE_KEY, $encrypted, false);
        update_option(Dailybuddy_Platform_Connector::OPT_PUBLIC_KEY,  $public_pem,  false);

        return true;
    }

    /**
     * Public key in PEM format. Empty string until generated.
     */
    public function get_public_key()
    {
        return (string) get_option(Dailybuddy_Platform_Connector::OPT_PUBLIC_KEY, '');
    }

    /**
     * Sign a message with the stored private key (SHA-256).
     *
     * @return string base64 signature, empty on failure.
     */
    public function sign($message)
    {
        $stored = get_option(Dailybuddy_Platform_Connector::OPT_PRIVATE_KEY, '');
        if (! $stored) {
            return '';
        }

        $private_pem = $this->decrypt($stored);
        if (is_wp_error($private_pem) || ! $private_pem) {
            return '';
        }

        $key = openssl_pkey_get_private($private_pem);
        if (! $key) {
            return '';
        }

        $signature = '';
        $ok = openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256);

        return $ok ? base64_encode($signature) : '';
    }

    /**
     * Delete the stored keypair (used on disconnect).
     */
    public function delete()
    {
        delete_option(Dailybuddy_Platform_Connector::OPT_PRIVATE_KEY);
        delete_option(Dailybuddy_Platform_Connector::OPT_PUBLIC_KEY);
    }

    /**
     * Derive a 32-byte AES key from SECURE_AUTH_KEY. If the salt is missing
     * (extremely rare — WP always defines it), we refuse rather than fall
     * back to a weak default.
     */
    private function derive_key()
    {
        if (! defined('SECURE_AUTH_KEY') || '' === SECURE_AUTH_KEY) {
            return new WP_Error('no_salt', __('SECURE_AUTH_KEY is not set in wp-config.php.', 'dailybuddy'));
        }
        return hash('sha256', SECURE_AUTH_KEY . '|' . self::KDF_CONTEXT, true);
    }

    /**
     * AES-256-GCM encrypt. Returns JSON string {iv, tag, data} (all b64).
     *
     * @return string|WP_Error
     */
    private function encrypt($plaintext)
    {
        $key = $this->derive_key();
        if (is_wp_error($key)) {
            return $key;
        }

        $iv  = random_bytes(12);
        $tag = '';
        $ct  = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $ct) {
            return new WP_Error('encrypt_failed', __('Encrypt failed.', 'dailybuddy'));
        }

        return wp_json_encode(array(
            'v'    => 1,
            'iv'   => base64_encode($iv),
            'tag'  => base64_encode($tag),
            'data' => base64_encode($ct),
        ));
    }

    /**
     * @return string|WP_Error plaintext
     */
    private function decrypt($payload)
    {
        $key = $this->derive_key();
        if (is_wp_error($key)) {
            return $key;
        }

        $parts = json_decode((string) $payload, true);
        if (! is_array($parts) || empty($parts['iv']) || empty($parts['tag']) || empty($parts['data'])) {
            return new WP_Error('bad_payload', __('Encrypted payload is malformed.', 'dailybuddy'));
        }

        $iv  = base64_decode($parts['iv'],  true);
        $tag = base64_decode($parts['tag'], true);
        $ct  = base64_decode($parts['data'], true);

        if (false === $iv || false === $tag || false === $ct) {
            return new WP_Error('bad_b64', __('Encrypted payload is corrupted.', 'dailybuddy'));
        }

        $plain = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (false === $plain) {
            return new WP_Error('decrypt_failed', __('Decrypt failed (wrong key or tampered data).', 'dailybuddy'));
        }

        return $plain;
    }
}
