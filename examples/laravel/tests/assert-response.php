<?php

declare(strict_types=1);

$response = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$case = $argv[1];
$data = $response['data'] ?? [];

$check = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

switch ($case) {
    case 'discovery':
        $check(($data['package_discovered'] ?? false) === true, 'Provider was not auto-discovered.');
        break;
    case 'basic':
        $check(($data['normalized'] ?? null) === '+66812345678', 'Thai normalization failed.');
        $check(($data['valid'] ?? false) === true, 'International validity failed.');
        $check(($data['possible'] ?? false) === true, 'Possibility failed.');
        $check(($data['national'] ?? null) === '081 234 5678', 'National format failed.');
        $check(($data['country'] ?? null) === 'TH', 'Country identification failed.');
        $check(($data['calling_code'] ?? null) === 66, 'Calling code failed.');
        $check(($data['type'] ?? null) === 'mobile', 'Type classification failed.');
        $check(($data['equal'] ?? false) === true, 'Semantic equality failed.');
        $check(array_key_exists('invalid_parse', $data) && $data['invalid_parse'] === null, 'Safe parsing failed.');
        $check(($data['with_extension'] ?? null) === [
            'main_number' => '+16502530000',
            'extension' => '0012',
            'uri' => 'tel:+1-650-253-0000;ext=0012',
        ], 'Extension preservation failed.');
        $check(($data['extracted'] ?? null) === [
            'tel:+66-81-234-5678',
            'tel:+1-650-253-0000;ext=0012',
        ], 'Ordered extraction failed.');
        break;
    case 'stored':
    case 'retrieved':
    case 'optional':
    case 'mobile':
        $check(preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $data['id'] ?? '') === 1, 'Expected UUIDv7.');
        $check(($data['phone'] ?? null) === ($case === 'mobile' ? '+16502530000' : '+66812345678'), 'Eloquent normalization/read failed.');
        $check(array_key_exists('mobile', $data), 'Nullable cast field is missing.');
        $check($data['mobile'] === ($case === 'mobile' ? '+66812345678' : null), 'Nullable/mobile cast failed.');
        if ($case === 'stored') {
            echo $data['id'];
        }
        break;
    case 'invalid-phone':
    case 'invalid-mobile':
        $attribute = $case === 'invalid-mobile' ? 'mobile' : 'phone';
        $check(isset($response['errors'][$attribute]), 'Expected ordinary Laravel field errors.');
        $check(! isset($response['exception']) && ! isset($response['trace']), 'Validation response exposes internals.');
        break;
    default:
        throw new RuntimeException('Unknown HTTP test case.');
}
