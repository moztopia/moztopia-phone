<?php

declare(strict_types=1);

namespace Moztopia\Phone\Tests;

use Moztopia\Phone\PhoneServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Supply a minimal Laravel host for package integration tests.
 */

abstract class TestCase extends Orchestra
{
    /**
     * Register explicitly for Testbench; real discovery is verified separately.
     */

    protected function getPackageProviders($app): array
    {
        return [PhoneServiceProvider::class];
    }

    /**
     * Keep synthetic database fixtures isolated in memory.
     */

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
