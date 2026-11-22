<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Moztopia\Phone\Casts\E164Phone;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;
use Moztopia\Phone\Exceptions\InvalidRegionException;
use Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException;
use Moztopia\Phone\Facades\Phone;
use Moztopia\Phone\Internal\LibPhoneNumberAdapter;
use Moztopia\Phone\PhoneService;
use Moztopia\Phone\PhoneServiceProvider;
use Moztopia\Phone\Rules\ValidPhone;

/**
 * Represent a synthetic consumer model without package-owned migrations.
 */

class PhoneContact extends Model
{
    protected $table = 'contacts';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Configure the optional cast using the documented string parameter.
     */

    protected function casts(): array
    {
        return ['phone' => E164Phone::class . ':TH'];
    }
}

/**
 * Exercise actual container constructor injection.
 */

final class InjectedPhoneConsumer
{
    /**
     * Receive the SDK using ordinary application constructor injection.
     */

    public function __construct(public readonly PhoneService $phone)
    {
    }
}

it('shares the discovered service binding with the facade and constructor injection', function () {
    $service = $this->app->make(PhoneService::class);
    expect($this->app->make(PhoneService::class))->toBe($service)
        ->and(Phone::getFacadeRoot())->toBe($service)
        ->and(Phone::normalize('0812345678', 'TH'))->toBe('+66812345678')
        ->and(class_exists('Phone', false))->toBeFalse();
    $consumer = $this->app->make(InjectedPhoneConsumer::class);
    expect($consumer->phone)->toBe($service)
        ->and($consumer->phone->isValid('+16502530000', 'TH'))->toBeTrue()
        ->and(Phone::format(number: '0812345678', format: 'e164', region: 'TH'))->toBe('+66812345678');
});

it('uses conventional required, nullable and non-implicit validation', function (string $presence, array $input, bool $passes) {
    $rules = $presence === '' ? [] : [$presence];
    $rules[] = new ValidPhone(region: 'TH');
    expect(Validator::make($input, ['phone' => $rules])->passes())->toBe($passes);
})->with([
    ['required', [], false], ['required', ['phone' => ''], false], ['required', ['phone' => null], false],
    ['required', ['phone' => '0812345678'], true], ['required', ['phone' => 'nope'], false],
    ['nullable', [], true], ['nullable', ['phone' => null], true], ['nullable', ['phone' => ''], true],
    ['nullable', ['phone' => '+16502530000'], true], ['nullable', ['phone' => 'bad'], false],
    ['', [], true], ['', ['phone' => ''], true], ['', ['phone' => 'bad'], false],
]);

it('distinguishes Laravel facade forwarding from a direct strict service caller', function () {
    expect(Phone::isValid(812345678, 'TH'))->toBe(Phone::isValid('812345678', 'TH'));
    $service = $this->app->make(PhoneService::class);
    expect(fn () => $service->isValid(812345678, 'TH'))->toThrow(TypeError::class);
});

it('rejects framework mixed values without scalar coercion', function (mixed $value) {
    $validator = Validator::make(['phone' => $value], ['phone' => [new ValidPhone(region: 'TH')]]);
    expect($validator->fails())->toBeTrue();
    $model = new PhoneContact();
    expect(fn () => $model->phone = $value)->toThrow(InvalidPhoneNumberException::class);
})->with([812345678, 812345678.0, true, false, [1], new stdClass()]);

it('enforces strict mobile classification and validates configuration early', function () {
    expect(Validator::make(['phone' => '+66812345678'], ['phone' => [new ValidPhone(mobileOnly: true)]])->passes())->toBeTrue();
    $validator = Validator::make(['phone' => '+16502530000'], ['phone' => [new ValidPhone(mobileOnly: true)]]);
    expect($validator->fails())->toBeTrue()->and($validator->errors()->first('phone'))->toContain('mobile');
    expect(fn () => new ValidPhone(region: 'ZZ'))->toThrow(InvalidRegionException::class)
        ->and(fn () => new E164Phone('ZZ'))->toThrow(InvalidRegionException::class)
        ->and(fn () => new E164Phone('TH', 'extra'))->toThrow(InvalidRegionException::class);
});

it('resolves default input-free messages and standard host translation overrides', function () {
    $validator = Validator::make(['phone' => 'Private invalid input'], ['phone' => [new ValidPhone()]]);
    expect($validator->errors()->first('phone'))->toBe('The phone must be a valid telephone number.')
        ->not->toContain('Private');

    $directory = sys_get_temp_dir() . '/moztopia-phone-lang-' . bin2hex(random_bytes(8));
    mkdir($directory . '/vendor/moztopia-phone/en', 0777, true);
    file_put_contents($directory . '/vendor/moztopia-phone/en/validation.php', "<?php return ['invalid' => 'Choose a valid phone.'];");
    $this->beforeApplicationDestroyed(fn () => (new \Illuminate\Filesystem\Filesystem())->deleteDirectory($directory));
    $this->app->useLangPath($directory);
    (new PhoneServiceProvider($this->app))->boot();
    foreach (['translator', 'translation.loader', 'validator'] as $binding) {
        $this->app->forgetInstance($binding);
    }
    Validator::clearResolvedInstance('validator');
    $validator = Validator::make(['phone' => 'invalid'], ['phone' => [new ValidPhone()]]);
    expect($validator->errors()->first('phone'))->toBe('Choose a valid phone.');
});

it('honors request middleware transformations while leaving presence to Laravel', function () {
    Route::post('/phone-validation', function (Request $request) {
        return $request->validate(['phone' => ['nullable', new ValidPhone(region: 'TH')]]);
    })->middleware([TrimStrings::class, ConvertEmptyStringsToNull::class]);
    Route::post('/required-phone', function (Request $request) {
        return $request->validate(['phone' => ['required', new ValidPhone(region: 'TH')]]);
    })->middleware([TrimStrings::class, ConvertEmptyStringsToNull::class]);
    $this->postJson('/phone-validation', ['phone' => '   '])->assertOk()->assertJson(['phone' => null]);
    $this->postJson('/required-phone', ['phone' => '   '])->assertUnprocessable()->assertJsonValidationErrors('phone');
    $this->postJson('/required-phone', ['phone' => ' 0812345678 '])->assertOk();
});

it('normalizes assignment and preserves null through database and serialization round trips', function () {
    Schema::create('contacts', function (Blueprint $table) {
        $table->id();
        $table->string('phone')->nullable();
    });
    $contact = PhoneContact::create(['phone' => '0812345678']);
    expect($contact->fresh()->phone)->toBe('+66812345678')
        ->and($contact->toArray()['phone'])->toBe('+66812345678')
        ->and(json_decode($contact->toJson(), true)['phone'])->toBe('+66812345678');
    $contact->phone = '+66812345678';
    expect($contact->isDirty('phone'))->toBeFalse();
    $contact->phone = null;
    $contact->save();
    expect($contact->fresh()->phone)->toBeNull()->and($contact->toArray()['phone'])->toBeNull();
    DB::table('contacts')->where('id', $contact->id)->update(['phone' => '+99912345678']);
    expect($contact->fresh()->phone)->toBe('+99912345678')->and(Phone::isValid('+99912345678'))->toBeFalse();
});

it('rejects invalid, extension-bearing and context-free assignments', function () {
    foreach (['', ' ', 'invalid', '+99912345678'] as $value) {
        $model = new PhoneContact();
        expect(fn () => $model->phone = $value)->toThrow(InvalidPhoneNumberException::class);
    }
    expect(fn () => $model->phone = '+66812345678 ext 1')->toThrow(UnrepresentablePhoneNumberException::class);
    $cast = new E164Phone();
    expect($cast->set(new PhoneContact(), 'phone', '+66812345678', []))->toBe('+66812345678');
    expect(fn () => $cast->set(new PhoneContact(), 'phone', '0812345678', []))->toThrow(InvalidPhoneNumberException::class);
});

it('rejects malformed stored values using absolute anchors without repair', function (mixed $value) {
    $model = new PhoneContact();
    $model->setRawAttributes(['phone' => $value], true);
    expect(fn () => $model->phone)->toThrow(InvalidPhoneNumberException::class)
        ->and(fn () => $model->toArray())->toThrow(InvalidPhoneNumberException::class);
    expect($model->getRawOriginal('phone'))->toBe($value);
})->with([
    "+66812345678\n", ' +66812345678', '+66812345678 ', '++66812345678', '+66812345678x',
    '66812345678', '+1', '+1234567890123456', '+６６８１２３４５６７８', "+66\n812345678", "\n+66812345678", 66812345678,
]);

it('keeps stored reads independent of simulated numbering metadata changes', function () {
    $valid = true;
    $util = Mockery::mock(\libphonenumber\PhoneNumberUtil::getInstance())->makePartial();
    $util->shouldReceive('isValidNumber')->andReturnUsing(function () use (&$valid) {
        return $valid;
    });
    $this->app->instance(PhoneService::class, new PhoneService(new LibPhoneNumberAdapter($util)));
    Phone::clearResolvedInstance(PhoneService::class);
    $model = new PhoneContact(['phone' => '+66812345678']);
    $valid = false;
    expect($model->phone)->toBe('+66812345678')->and($model->toArray()['phone'])->toBe('+66812345678')
        ->and(json_decode($model->toJson(), true)['phone'])->toBe('+66812345678')
        ->and(Phone::isValid('+66812345678'))->toBeFalse();
    expect(fn () => $model->phone = '+66812345678')->toThrow(InvalidPhoneNumberException::class);
});
