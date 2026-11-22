<?php

declare(strict_types=1);

use libphonenumber\PhoneNumberUtil;
use Moztopia\Phone\Exceptions\ExtractionLimitException;
use Moztopia\Phone\Exceptions\InvalidFormatException;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;
use Moztopia\Phone\Exceptions\InvalidRegionException;
use Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException;
use Moztopia\Phone\Internal\LibPhoneNumberAdapter;
use Moztopia\Phone\ParsedPhoneNumber;
use Moztopia\Phone\PhoneService;

beforeEach(function () {
    $this->phone = new PhoneService();
});

it('implements the exact public method and named-argument contract', function () {
    $contract = [
        'parse' => [['number', 'region'], ParsedPhoneNumber::class],
        'tryParse' => [['number', 'region'], '?' . ParsedPhoneNumber::class],
        'normalize' => [['number', 'region'], 'string'],
        'isValid' => [['number', 'region'], 'bool'],
        'isPossible' => [['number', 'region'], 'bool'],
        'format' => [['number', 'format', 'region'], 'string'],
        'country' => [['number', 'region'], '?string'],
        'callingCode' => [['number', 'region'], 'int'],
        'type' => [['number', 'region'], 'string'],
        'equals' => [['left', 'right', 'region'], 'bool'],
        'extract' => [['text', 'region'], 'array'],
    ];
    foreach ($contract as $name => [$parameters, $return]) {
        $method = new ReflectionMethod(PhoneService::class, $name);
        expect(array_map(fn ($p) => $p->getName(), $method->getParameters()))->toBe($parameters)
            ->and((string) $method->getReturnType())->toBe($return);
    }
    expect($this->phone->format(number: '0812345678', format: 'e164', region: 'TH'))->toBe('+66812345678');
});

it('supports verified example numbers across eight regions', function (string $region) {
    $util = PhoneNumberUtil::getInstance();
    $example = $util->getExampleNumber($region);
    $national = $util->format($example, \libphonenumber\PhoneNumberFormat::NATIONAL);
    $e164 = $util->format($example, \libphonenumber\PhoneNumberFormat::E164);
    expect($this->phone->normalize($national, $region))->toBe($e164)
        ->and($this->phone->normalize($e164))->toBe($e164)
        ->and($this->phone->isPossible($national, $region))->toBeTrue()
        ->and($this->phone->country($e164))->toBe($region)
        ->and($this->phone->callingCode($e164))->toBe($util->getCountryCodeForRegion($region));
})->with(['TH', 'US', 'GB', 'AU', 'DE', 'IN', 'JP', 'BR']);

it('normalizes context and prefixes without restricting nationality', function () {
    expect($this->phone->normalize('0812345678', 'th'))->toBe('+66812345678')
        ->and($this->phone->normalize('001 650 253 0000', 'GB'))->toBe('+16502530000')
        ->and($this->phone->normalize('011 66 81 234 5678', 'US'))->toBe('+66812345678')
        ->and($this->phone->isValid('+16502530000', 'TH'))->toBeTrue()
        ->and($this->phone->tryParse('0812345678'))->toBeNull();
});

it('rejects invalid syntax without extracting or fixing it', function (string $input) {
    expect($this->phone->isValid($input, 'TH'))->toBeFalse()
        ->and($this->phone->isPossible($input, 'TH'))->toBeFalse()
        ->and($this->phone->tryParse($input, 'TH'))->toBeNull()
        ->and($this->phone->equals($input, '+66812345678', 'TH'))->toBeFalse();
    expect(fn () => $this->phone->parse($input, 'TH'))->toThrow(InvalidPhoneNumberException::class);
})->with([
    '', ' ', 'Call me at +66812345678', '1-800-FLOWERS', '+66812345678 / +16502530000',
    '+66812345678 +16502530000', '(0812345678', '081)2345678', '08()12345678',
    '((081))2345678', '+66--812345678', 'tel:0812345678;phone-context=+66',
    'tel:+66812345678;foo=123', '+66812345678,123', '*123#', '911', '+99912345678',
    '1', str_repeat('1', 257), "+66\n812345678", "+66\u{200B}812345678", "\xFF+66812345678",
]);

it('validates developer arguments before invalid telephone data', function (string $region) {
    foreach (['parse', 'tryParse', 'normalize', 'isValid', 'isPossible', 'country', 'callingCode', 'type', 'extract'] as $method) {
        expect(fn () => $this->phone->$method('', $region))->toThrow(InvalidRegionException::class);
    }
    expect(fn () => $this->phone->equals('', '', $region))->toThrow(InvalidRegionException::class)
        ->and(fn () => $this->phone->format('', 'national', $region))->toThrow(InvalidRegionException::class);
})->with(['', 'ZZ', '001', 'USA', 'Thailand', ' TH']);

it('checks formats independently', function () {
    expect(fn () => $this->phone->format('', 'NATIONAL'))->toThrow(InvalidFormatException::class);
    foreach (['country', 'callingCode', 'type', 'normalize', 'format'] as $method) {
        expect(fn () => $this->phone->$method('invalid'))->toThrow(InvalidPhoneNumberException::class);
    }
});

it('normalizes supported Unicode decimal digits without silently dropping others', function () {
    expect($this->phone->normalize('+６６ ８１ ２３４ ５６７８'))->toBe('+66812345678')
        ->and($this->phone->normalize('+٦٦ ٨١ ٢٣٤ ٥٦٧٨'))->toBe('+66812345678')
        ->and($this->phone->normalize('+۶۶ ۸۱ ۲۳۴ ۵۶۷۸'))->toBe('+66812345678')
        ->and($this->phone->normalize("+66\u{00A0}81\u{202F}234 5678"))->toBe('+66812345678');
});

it('preserves extensions and makes lossless conversion explicit', function (string $suffix) {
    $input = '+1 650 253 0000' . $suffix;
    $p = $this->phone->parse($input);
    expect($p->extension())->toBe('0012')->and($p->e164())->toBe('+16502530000')
        ->and($p->national())->toContain('0012')->and($p->international())->toContain('0012')
        ->and($p->rfc3966())->toEndWith(';ext=0012');
    expect($this->phone->parse($p->rfc3966())->extension())->toBe('0012')
        ->and($this->phone->equals($input, $p->rfc3966()))->toBeTrue()
        ->and($this->phone->equals($input, '+16502530000'))->toBeFalse()
        ->and($this->phone->equals($input, '+16502530000 ext 12'))->toBeFalse();
    expect(fn () => $this->phone->normalize($input))->toThrow(UnrepresentablePhoneNumberException::class)
        ->and(fn () => $this->phone->format($input, 'e164'))->toThrow(UnrepresentablePhoneNumberException::class);
    foreach (['national', 'international', 'rfc3966'] as $format) {
        expect($this->phone->format($input, $format))->toContain('0012');
    }
})->with([' ext 0012', ' ext. 0012', 'x0012', '#0012']);

it('supports exactly the documented telephone URI subset', function () {
    expect($this->phone->normalize('tel:+16502530000'))->toBe('+16502530000')
        ->and($this->phone->parse('tel:+16502530000;ext=012')->extension())->toBe('012')
        ->and($this->phone->isValid('tel:+16502530000;ext=abc'))->toBeFalse();
});

it('preserves significant leading zeros and rejects suffix equality', function () {
    $example = PhoneNumberUtil::getInstance()->getExampleNumber('IT');
    $number = '+39' . PhoneNumberUtil::getInstance()->getNationalSignificantNumber($example);
    expect($this->phone->normalize($number))->toStartWith('+390')
        ->and($this->phone->equals('0812345678', '+66812345678', 'TH'))->toBeTrue()
        ->and($this->phone->equals('2530000', '+16502530000', 'US'))->toBeFalse();
});

it('distinguishes possible-invalid numbers from valid and local-only numbers', function () {
    expect($this->phone->isPossible('+12001230101'))->toBeTrue()
        ->and($this->phone->isValid('+12001230101'))->toBeFalse()
        ->and($this->phone->isPossible('2530000', 'US'))->toBeFalse();
});

it('classifies conservatively and handles non-geographic and shared calling codes', function () {
    expect($this->phone->parse('+66812345678')->isMobile())->toBeTrue()
        ->and($this->phone->type('+16502530000'))->toBe('fixed_line_or_mobile')
        ->and($this->phone->parse('+16502530000')->isMobile())->toBeFalse()
        ->and($this->phone->country('+80012345678'))->toBeNull()
        ->and($this->phone->callingCode('+80012345678'))->toBe(800)
        ->and($this->phone->country('+18002345678'))->toBeNull();
});

it('maps every applicable upstream number type to a stable string', function () {
    $util = PhoneNumberUtil::getInstance();
    foreach (['TH', 'US', 'GB', 'DE', 'JP', 'BR'] as $region) {
        foreach ($util->getSupportedTypesForRegion($region) as $type) {
            $example = $util->getExampleNumberForType($region, $type);
            if ($example !== null && $util->isValidNumber($example)) {
                $number = $util->format($example, \libphonenumber\PhoneNumberFormat::E164);
                expect($this->phone->type($number))->toBe(strtolower($util->getNumberType($example)->name));
            }
        }
    }
});

it('snapshots values immutably and rejects unrepresentable main numbers', function () {
    $p = $this->phone->parse('+66812345678');
    expect((new ReflectionClass($p))->isReadOnly())->toBeTrue();
    expect(fn () => $p->extension = '5')->toThrow(Error::class);
    $oversized = new ParsedPhoneNumber('+491234567890123456', 'national', 'international', 'tel:+49-1234567890123456', 'DE', 49, 'fixed_line', null);
    expect(fn () => $oversized->e164())->toThrow(UnrepresentablePhoneNumberException::class);
});

it('exposes no raw input or upstream exception chain on expected errors', function () {
    try {
        $this->phone->parse('Private +99912345678');
    } catch (InvalidPhoneNumberException $e) {
        expect($e->getMessage())->not->toContain('99912345678')->and($e->getPrevious())->toBeNull();
    }
    $util = Mockery::mock(PhoneNumberUtil::class);
    $util->shouldReceive('parse')->andThrow(new RuntimeException('unexpected'));
    $service = new PhoneService(new LibPhoneNumberAdapter($util));
    expect(fn () => $service->tryParse('+66812345678'))->toThrow(RuntimeException::class, 'unexpected');
});

it('documents strict and weak scalar caller behavior', function () {
    expect(fn () => $this->phone->isValid(812345678))->toThrow(TypeError::class);
    $weak = require dirname(__DIR__) . '/Fixtures/weak-caller.php';
    expect($weak($this->phone, 812345678))->toBe($this->phone->isValid('812345678', 'TH'));
});

it('extracts upstream matches in occurrence order with repeats and extensions', function () {
    $matches = $this->phone->extract('Call +66812345678, +16502530000 ext. 0012, +66812345678.');
    expect($matches)->toHaveCount(3)->and(array_keys($matches))->toBe([0, 1, 2])
        ->and(array_map(fn ($p) => $p->e164(), $matches))->toBe(['+66812345678', '+16502530000', '+66812345678'])
        ->and($matches[1]->extension())->toBe('0012')
        ->and($this->phone->extract('There are no numbers here.'))->toBe([]);
});

it('enforces extraction byte and qualifying-match limits without returning partial results', function () {
    expect($this->phone->extract(str_repeat('a', 16384)))->toBe([])
        ->and($this->phone->extract(implode(', ', array_fill(0, 100, '+66812345678'))))->toHaveCount(100);
    expect(fn () => $this->phone->extract(str_repeat('a', 16385)))->toThrow(ExtractionLimitException::class)
        ->and(fn () => $this->phone->extract(implode(', ', array_fill(0, 101, '+66812345678'))))->toThrow(ExtractionLimitException::class)
        ->and(fn () => $this->phone->extract("bad\xFF"))->toThrow(InvalidPhoneNumberException::class);
});

it('enforces byte limits before Unicode normalization or trimming', function () {
    $atLimit = str_repeat(' ', 244) . '+66812345678';
    expect(strlen($atLimit))->toBe(256)
        ->and($this->phone->normalize($atLimit))->toBe('+66812345678')
        ->and($this->phone->isValid(' ' . $atLimit))->toBeFalse()
        ->and($this->phone->equals('+66812345678', ' ' . $atLimit))->toBeFalse();
    $text = str_repeat('ก', 5461) . 'a';
    expect(strlen($text))->toBe(16384)->and($this->phone->extract($text))->toBe([]);
    expect(fn () => $this->phone->extract($text . 'a'))->toThrow(ExtractionLimitException::class);
});

it('keeps metadata-valid overlength identities separate from E164 representability', function () {
    $util = Mockery::mock(PhoneNumberUtil::getInstance())->makePartial();
    $util->shouldReceive('isValidNumber')->andReturnTrue();
    $adapter = new LibPhoneNumberAdapter($util);
    $service = new PhoneService($adapter);
    $number = '+4912345678901234';
    expect($service->parse($number))->toBeInstanceOf(ParsedPhoneNumber::class)
        ->and($service->equals($number, $number))->toBeTrue();
    expect(fn () => $service->normalize($number))->toThrow(UnrepresentablePhoneNumberException::class)
        ->and(fn () => $service->format($number, 'e164'))->toThrow(UnrepresentablePhoneNumberException::class);
});

it('filters strict match substrings and consumes the actual upstream matching policy', function () {
    $text = 'Invalid 00000, valid +66812345678, unicode +６６８１２３４５６７８.';
    $expected = [];
    foreach (PhoneNumberUtil::getInstance()->findNumbers($text, 'TH', \libphonenumber\Leniency::VALID(), PHP_INT_MAX) as $match) {
        $parsed = $this->phone->tryParse($match->rawString(), 'TH');
        if ($parsed !== null) {
            $expected[] = $parsed->rfc3966();
        }
    }
    expect(array_map(fn ($p) => $p->rfc3966(), $this->phone->extract($text, 'TH')))->toBe($expected);
});

it('propagates iterator failures even after accumulating a valid match', function () {
    $util = PhoneNumberUtil::getInstance();
    $matcher = Mockery::mock(\libphonenumber\PhoneNumberMatcher::class);
    $matcher->shouldReceive('rewind')->once();
    $matcher->shouldReceive('valid')->andReturnTrue();
    $matcher->shouldReceive('current')->andReturn(new \libphonenumber\PhoneNumberMatch(0, '+66812345678', $util->parse('+66812345678')));
    $matcher->shouldReceive('key')->andReturn(0);
    $matcher->shouldReceive('next')->andThrow(new RuntimeException('matcher failed'));
    $proxy = Mockery::mock($util)->makePartial();
    $proxy->shouldReceive('findNumbers')->andReturn($matcher);
    $service = new PhoneService(new LibPhoneNumberAdapter($proxy));
    expect(fn () => $service->extract('+66812345678'))->toThrow(RuntimeException::class, 'matcher failed');
});
