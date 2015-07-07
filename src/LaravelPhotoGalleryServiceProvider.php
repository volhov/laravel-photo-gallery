<?php

namespace JeroenG\LaravelPhotoGallery;

use Illuminate\Support\ServiceProvider;

class LaravelPhotoGalleryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $resources = realpath(__DIR__.'/../resources');

        $this->loadViewsFrom($resources.'/views', 'gallery');
        $this->loadTranslationsFrom($resources.'/lang', 'gallery');

        $this->publishes([
            $resources.'/views' => base_path('resources/views/vendor/gallery')
        ], 'views');

        $this->publishes([
            $resources.'/config/gallery.php' => config_path('gallery.php')
        ], 'config');

        $this->publishes([
            $resources.'/migrations' => $this->app->databasePath().'/migrations',
        ], 'migrations');

        $this->publishes([
            $resources.'/assets' => public_path('vendor/gallery'),
        ], 'assets');

        if(config('gallery.routes')) {
            if (! $this->app->routesAreCached()) {
                require $resources.'/routes.php';
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $resources = realpath(__DIR__.'/../resources');
        $this->mergeConfigFrom($resources.'/config/gallery.php', 'gallery');
        $this->bindBindings();
        $this->commands(['JeroenG\LaravelPhotoGallery\Console\GalleryClearCommand']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['gallery'];
    }

    public function bindBindings()
    {
        // Bind the facade
        $this->app->bind('gallery', function(){
            return new Services\GalleryService();
        });

        if(config('gallery.driver') == 'eloquent') {
            // When using 'AlbumRepository', Laravel automatically uses the EloquentAlbumRepository
            $this->app->bind('JeroenG\LaravelPhotoGallery\Contracts\AlbumRepository','JeroenG\LaravelPhotoGallery\Repositories\EloquentAlbumRepository');
            // The same for Photos
            $this->app->bind('JeroenG\LaravelPhotoGallery\Contracts\PhotoRepository', 'JeroenG\LaravelPhotoGallery\Repositories\EloquentPhotoRepository');
        } else {
            throw new \Exception("Invalid gallery driver.");
        }
    }

}