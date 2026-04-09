<?php
/**
 * app/Helpers/ThumbnailHelper.php
 *
 * Usage in Blade templates:
 *   <img src="{{ ThumbnailHelper::url($location->thumbNailUrl) }}" alt="...">
 *
 * Register in config/app.php aliases:
 *   'ThumbnailHelper' => App\Helpers\ThumbnailHelper::class,
 *
 * OR use the helper function thumbnail_url() registered via a service provider.
 */

namespace App\Helpers;

class ThumbnailHelper
{
    /**
     * Normalize a thumbnail URL from the database:
     * - Decodes percent-encoding (%20 → space)
     * - Replaces spaces in the filename with underscores
     * - Lowercases the filename
     * - Rewrites cross-origin upload URLs to use APP_URL so they load correctly
     *   regardless of which port the backend API runs on.
     *
     * @param  string|null $rawUrl  Raw URL from database (may have spaces, wrong origin)
     * @return string               Safe URL ready for use in <img src="">
     */
    public static function url(?string $rawUrl): string
    {
        if (empty($rawUrl)) {
            return '';
        }

        $url = trim($rawUrl);

        // Decode any existing percent-encoding first
        $url = rawurldecode($url);

        // Normalize the filename portion only (last path segment)
        $parts = explode('/', $url);
        $filename = array_pop($parts);
        $filename = strtolower(preg_replace('/\s+/', '_', $filename));
        $parts[] = $filename;
        $url = implode('/', $parts);

        // Re-encode only the filename (spaces → %20 is handled; underscores are URL-safe)
        // At this point filename should have no spaces, so rawurlencode is not needed.

        // For absolute upload URLs pointing to a different origin (e.g. localhost:3000),
        // rewrite to APP_URL so the browser can load them from the same host as the page.
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsedScheme = parse_url($url, PHP_URL_SCHEME);
            $parsedHost   = parse_url($url, PHP_URL_HOST);
            $parsedPort   = parse_url($url, PHP_URL_PORT);
            $parsedPath   = parse_url($url, PHP_URL_PATH) ?? '';

            $appUrl    = rtrim(config('app.url', ''), '/');
            $appHost   = parse_url($appUrl, PHP_URL_HOST);
            $appPort   = parse_url($appUrl, PHP_URL_PORT);

            $urlOrigin = $parsedScheme . '://' . $parsedHost . ($parsedPort ? ':' . $parsedPort : '');

            // If origin doesn't match app URL, check if it's an upload path and rewrite
            if ($urlOrigin !== $appUrl && str_contains($parsedPath, '/uploads/')) {
                // Use the API_BASE env variable if set; otherwise fall back to app URL
                $apiBase = rtrim(env('API_BASE_URL', $appUrl), '/');
                return $apiBase . $parsedPath;
            }
        }

        return $url;
    }

    /**
     * Return the URL or a fallback placeholder path if the URL is empty.
     *
     * @param  string|null $rawUrl
     * @param  string      $placeholder  Asset path relative to public/, e.g. 'assets/img/placeholder.jpg'
     * @return string
     */
    public static function urlOrPlaceholder(?string $rawUrl, string $placeholder = ''): string
    {
        $resolved = static::url($rawUrl);
        if ($resolved !== '') {
            return $resolved;
        }
        return $placeholder ? asset($placeholder) : '';
    }
}