<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use libphonenumber\Leniency;
use libphonenumber\PhoneNumberUtil;

require dirname(__DIR__) . '/vendor/autoload.php';

$util = PhoneNumberUtil::getInstance();
$number = '+1 650 253 0000';
$fixtures = [
    'ordinary' => ['Please call ' . $number . ' tomorrow.', 1],
    'no_numbers' => ['There are no telephone numbers here.', 0],
    'multiple' => [$number . ', +66 81 234 5678.', 2],
    'duplicates' => [$number . ', ' . $number, 2],
    'extensions' => [$number . ' ext. 0012', 1],
    'unicode' => ['ติดต่อ +６６ ８１ ２３４ ５６７８ ได้', 1],
    'punctuation' => ['[(' . $number . ')] / ' . $number, 2],
    'invalid_candidates' => [substr(str_repeat('12345 / 000000 / 99999; ', 800), 0, 16384), 0],
    'near_limit' => [str_repeat('a ', 8183) . $number, 1],
    'exact_limit' => [str_repeat('a', 16384), 0],
    'hundred' => [implode(', ', array_fill(0, 100, $number)), 100],
    'hundred_one' => [implode(', ', array_fill(0, 101, $number)), 'limit'],
    'oversized' => [str_repeat('a', 16385), 'limit'],
    'ambiguous' => [$number . $number, null],
];

$results = [];
foreach ($fixtures as $name => [$text, $expected]) {
    $start = hrtime(true);
    $memory = memory_get_usage(true);
    $count = 0;
    $exhausted = false;
    $outcome = 'complete';
    if (strlen($text) > 16384) {
        $outcome = 'limit';
    } else {
        foreach ($util->findNumbers($text, 'TH', Leniency::VALID(), PHP_INT_MAX) as $match) {
            if (!$util->isValidNumber($match->number())) {
                throw new RuntimeException('VALID matcher yielded an invalid number.');
            }
            $count++;
            if ($name === 'extensions' && $match->number()->getExtension() !== '0012') {
                throw new RuntimeException('Extension was not preserved.');
            }
            if ($count > 100) {
                $outcome = 'limit';
                break;
            }
        }
        $exhausted = $outcome === 'complete';
    }
    if (($expected === 'limit' && $outcome !== 'limit') || (is_int($expected) && ($outcome !== 'complete' || $count !== $expected))) {
        throw new RuntimeException('Unexpected extraction outcome for fixture: ' . $name);
    }
    $results[] = [
        'fixture' => $name,
        'bytes' => strlen($text),
        'matches_observed' => $count,
        'outcome' => $outcome,
        'exhausted' => $exhausted,
        'elapsed_ms' => round((hrtime(true) - $start) / 1e6, 3),
        'allocated_delta_bytes' => memory_get_usage(true) - $memory,
        'process_peak_bytes' => memory_get_peak_usage(true),
    ];
}

echo json_encode([
    'php' => PHP_VERSION,
    'engine' => InstalledVersions::getPrettyVersion('giggsey/libphonenumber-for-php'),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
