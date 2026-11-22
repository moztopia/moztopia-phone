<?php

declare(strict_types=1);

namespace Moztopia\Phone;

use Illuminate\Support\ServiceProvider;

/**
 * Register the SDK and bundled validation messages through Laravel discovery.
 */

final class PhoneServiceProvider extends ServiceProvider
{
    /**
     * Share one service between injection, the facade and Laravel adapters.
     */

    public function register(): void
    {
        $this->app->singleton(PhoneService::class, static fn (): PhoneService => new PhoneService());
    }

    /**
     * Make default and application-overridden messages available without publishing.
     */

    public function boot(): void
    {
        $this->loadTranslationsFrom(dirname(__DIR__) . '/lang', 'moztopia-phone');
    }
}
