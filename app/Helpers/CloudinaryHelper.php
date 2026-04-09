<?php

namespace App\Helpers;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryHelper
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey    = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');

        $this->cloudinary = new Cloudinary(
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $this->cloudName,
                    'api_key'    => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                ],
                'url' => [
                    'secure' => true
                ]
            ])
        );
    }

    /**
     * Upload a pin image to Cloudinary.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string  Secure Cloudinary image URL
     */
    public function uploadPinImage($file): string
    {
        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder'          => 'pin-images',
                'resource_type'   => 'image',
                'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
            ]
        );

        return $result['secure_url'];
    }

    /**
     * Delete a pin image from Cloudinary.
     *
     * @param  string  $publicId
     * @return void
     */
    public function deletePinImage(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }

    /**
     * Extract Cloudinary public ID from a full URL.
     *
     * @param  string  $url
     * @return string
     */
    public function getPublicIdFromUrl(string $url): string
    {
        $path           = parse_url($url, PHP_URL_PATH);
        $parts          = explode('/upload/', $path);
        $withoutVersion = preg_replace('/^v\d+\//', '', $parts[1] ?? '');

        return substr($withoutVersion, 0, strrpos($withoutVersion, '.'));
    }
}