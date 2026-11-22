<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Moztopia\Phone\Casts\E164Phone;
use Moztopia\Phone\Facades\Phone;
use Moztopia\Phone\PhoneService;
use Moztopia\Phone\PhoneServiceProvider;
use Moztopia\Phone\Rules\ValidPhone;

$directory = $argv[1] ?? throw new InvalidArgumentException('Supply the clean Laravel application directory.');
require $directory . '/vendor/autoload.php';
$app = require $directory . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$checks = [
    'composer_discovery' => ($app->getLoadedProviders()[PhoneServiceProvider::class] ?? false),
    'thai_normalization' => Phone::normalize('0812345678', 'TH') === '+66812345678',
    'international_validity' => Phone::isValid('+16502530000'),
    'national_format' => Phone::format('0812345678', 'national', 'TH') === '081 234 5678',
    'extension_format' => Phone::format('+16502530000 ext. 0012', 'rfc3966') === 'tel:+1-650-253-0000;ext=0012',
    'shared_di' => $app->make(PhoneService::class) === Phone::getFacadeRoot(),
    'validation' => Validator::make(['phone' => '0812345678'], ['phone' => ['required', new ValidPhone(region: 'TH')]])->passes(),
    'nullable' => Validator::make(['phone' => null], ['phone' => ['nullable', new ValidPhone()]])->passes(),
    'required' => Validator::make([], ['phone' => ['required', new ValidPhone()]])->fails(),
    'translated_error' => Validator::make(['phone' => 'invalid'], ['phone' => [new ValidPhone()]])->errors()->first('phone') === 'The phone must be a valid telephone number.',
    'comparison' => Phone::equals('0812345678', '+66812345678', 'TH'),
    'extraction' => count(Phone::extract('Call +66812345678.')) === 1,
];

$model = new class () extends Model {
    /**
     * Add the documented cast to a synthetic existing host model.
     */

    protected function casts(): array
    {
        return ['phone' => E164Phone::class . ':TH'];
    }
};
$model->phone = '0812345678';
$checks['cast_assignment'] = $model->phone === '+66812345678';
$checks['cast_serialization'] = $model->toArray()['phone'] === '+66812345678';
$model->phone = null;
$checks['cast_null'] = $model->phone === null;
$model->setRawAttributes(['phone' => '+99912345678'], true);
$checks['structural_read'] = $model->phone === '+99912345678' && !Phone::isValid('+99912345678');
$checks['invalid_data'] = Phone::tryParse('not a phone') === null;

foreach ($checks as $name => $passed) {
    if ($passed !== true) {
        throw new RuntimeException('Clean application check failed: ' . $name);
    }
}

echo json_encode(['php' => PHP_VERSION, 'laravel' => $app->version(), 'checks' => $checks], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
