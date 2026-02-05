<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SAPImportService;

$service = app(SAPImportService::class);

$reflection = new ReflectionClass($service);

// Test 1: parseDate format M/D/YYYY
$parseDate = $reflection->getMethod('parseDate');
$parseDate->setAccessible(true);

echo "=== Test parseDate ===" . PHP_EOL;
$tests = [
    '1/31/2025',    // Excel M/D/YYYY
    '12/5/2025',    // Excel M/D/YYYY
    '01/31/2025',   // Excel MM/DD/YYYY
    '20260106',     // SAP YYYYMMDD
    '06/01/2026',   // Could be dd/mm/yyyy or mm/dd/yyyy
];

foreach ($tests as $date) {
    $result = $parseDate->invoke($service, $date);
    echo "$date -> " . ($result ?? 'NULL') . PHP_EOL;
}

// Test 2: normalizeAmountForValidation
$normalize = $reflection->getMethod('normalizeAmountForValidation');
$normalize->setAccessible(true);

echo PHP_EOL . "=== Test normalizeAmountForValidation ===" . PHP_EOL;
$amounts = [
    '228,511,960',       // Excel: comma as thousand
    '-1,190,341,370',    // Excel: negative with comma thousand
    '1234,56',           // European: comma as decimal
    '926790',            // Plain number
    '66894380-',         // SAP trailing minus - setelah cleanup akan jadi -66894380
    '1.234.567',         // Dot as thousand
    '1.234,56',          // European full format
];

foreach ($amounts as $amount) {
    // Clean seperti di validateRowData
    $cleaned = preg_replace('/[^\d\-\.,]/', '', $amount);
    if (substr($cleaned, -1) === '-') {
        $cleaned = '-' . substr($cleaned, 0, -1);
    }
    
    $result = $normalize->invoke($service, $cleaned);
    echo "$amount (cleaned: $cleaned) -> $result" . PHP_EOL;
}

echo PHP_EOL . "=== Test cost element normalization ===" . PHP_EOL;
$costElements = ['5101148', '0005101148', '6001113', '0006001113'];
foreach ($costElements as $ce) {
    $normalized = str_pad(ltrim($ce, '0'), 10, '0', STR_PAD_LEFT);
    // Jika sudah 10 digit, gunakan asli
    if (strlen($ce) === 10) {
        $normalized = $ce;
    }
    echo "$ce -> $normalized" . PHP_EOL;
}

echo PHP_EOL . "All tests completed!" . PHP_EOL;
