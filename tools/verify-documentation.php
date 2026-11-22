<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

$appDirectory = $argv[1] ?? throw new InvalidArgumentException('Supply an installed clean Laravel application directory.');
require $appDirectory . '/vendor/autoload.php';
$app = require $appDirectory . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$documents = array_slice($argv, 2) ?: ['README.md', 'AGENTS.md'];
$results = [];
foreach ($documents as $document) {
    $markdown = file_get_contents($document);
    if ($markdown === false) {
        throw new RuntimeException('Cannot read documentation.');
    }
    preg_match_all('/```php\R(.*?)```/s', $markdown, $matches);
    $examples = '';
    foreach ($matches[1] as $example) {
        $example = preg_replace('/^use [^;]+;\R/m', '', $example) ?? '';
        if (str_contains($example, 'protected function casts')) {
            $example = '$documentationModel = new class extends \\Illuminate\\Database\\Eloquent\\Model {' . $example . '};';
        }
        $examples .= $example . "\n";
    }
    $runner = static function (string $examples): void {
        eval('use Moztopia\\Phone\\Facades\\Phone; use Moztopia\\Phone\\Rules\\ValidPhone; use Moztopia\\Phone\\Casts\\E164Phone;' . $examples);
    };
    $runner($examples);
    $results[$document] = ['php_blocks_executed' => count($matches[1]), 'passed' => true];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
