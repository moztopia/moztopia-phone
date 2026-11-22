<?php

declare(strict_types=1);

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Moztopia\Phone\PhoneServiceProvider;

Route::get('/', fn () => new JsonResource([
    'example' => 'Moztopia Phone / Laravel',
    'package_discovered' => app()->getLoadedProviders()[PhoneServiceProvider::class] ?? false,
    'operations' => '/examples/basic',
    'contacts' => '/examples/contacts',
]));

require __DIR__.'/basic.php';
require __DIR__.'/validation-and-casting.php';
