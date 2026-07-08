<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Support Contact Information
    |--------------------------------------------------------------------------
    |
    | These values are pulled from your .env file and used across the portal
    | for the contact form, landing page, and profile placeholders.
    |
    */

    'email' => env('SUPPORT_EMAIL', 'nanno@temadigital.my'),

    'sftp_host' => (function() {
        // First try the configured SFTP delivery host
        $host = env('SYSTEM_SSH_HOST', env('SFTP_DELIVERY_HOST'));
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP)) {
            // If empty or if it's an IP address, check the app url host
            $host = parse_url(env('APP_URL', 'https://dataportal.temadigital.my'), PHP_URL_HOST);
        }
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP)) {
            // If still an IP address or empty, check HTTP_HOST superglobal
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'dataportal.temadigital.my';
        }
        // If it's still a raw IP (e.g. localhost, local IP or admin IP), fallback to the public domain
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP) || $host === 'localhost' || $host === '127.0.0.1') {
            $host = 'dataportal.temadigital.my';
        }
        return $host;
    })(),
];
