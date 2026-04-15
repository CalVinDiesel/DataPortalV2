<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return in_array($user->role, ['admin', 'superadmin']);
        });

        Gate::define('superadmin', function (User $user) {
            return $user->role === 'superadmin';
        });

        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle'
        );

        \Illuminate\Support\Facades\Storage::extend('google', function ($app, $config) {
            $client = new \Google\Client();
            
            if (isset($config['serviceAccountJson']) && file_exists($config['serviceAccountJson'])) {
                $client->setAuthConfig($config['serviceAccountJson']);
            } else {
                if (isset($config['clientId'])) $client->setClientId($config['clientId']);
                if (isset($config['clientSecret'])) $client->setClientSecret($config['clientSecret']);
                if (isset($config['refreshToken'])) $client->refreshToken($config['refreshToken']);
            }

            $client->addScope(\Google\Service\Drive::DRIVE);
            $service = new \Google\Service\Drive($client);

            $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderId'] ?? '/');
            $filesystem = new \League\Flysystem\Filesystem($adapter);

            return new \Illuminate\Filesystem\FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}

