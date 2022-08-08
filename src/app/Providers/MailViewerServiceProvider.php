<?php

namespace MasterRO\MailViewer\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use MasterRO\MailViewer\Commands\PruneCommand;
use MasterRO\MailViewer\Commands\PublishCommand;
use MasterRO\MailViewer\Listeners\MailLogger;
use MasterRO\MailViewer\Services\Logger;
use MasterRO\MailViewer\Traits\PublishesMigrations;

class MailViewerServiceProvider extends EventServiceProvider
{
    use PublishesMigrations;
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageSending::class => [
            MailLogger::class,
        ],
    ];

    public function register()
    {
        parent::register();

        $this->app->singleton(Logger::class);

        $this->commands([
            PublishCommand::class,
            PruneCommand::class,
        ]);
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->publish();

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/mail-viewer.php', 'mail-viewer'
        );

        $this->loadRoutesFrom(__DIR__ . '/../../resources/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'mail-viewer');

        $this->registerMigrations(__DIR__.'/../../database/migrations');
    }

    /**
     * Publish config, views and assets
     */
    protected function publish()
    {
        $this->publishes([
            __DIR__ . '/../../config/mail-viewer.php' => config_path('mail-viewer.php'),
        ], 'mail-viewer-config');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/mail-viewer'),
        ], ['mail-viewer-views']);

        $this->publishes([
            __DIR__ . '/../../public/' => public_path('vendor/mail-viewer'),
        ], ['mail-viewer-assets', 'laravel-assets']);

    }
}
