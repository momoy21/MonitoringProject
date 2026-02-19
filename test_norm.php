<?php
// Test normalization logic
$testValues = ['5001101', '6007101', '4311001', '0004311001', '0006007101'];

foreach ($testValues as $v) {
    $stripped = ltrim($v, '0');
    $normalized = str_pad($stripped, 10, '0', STR_PAD_LEFT);
    if (strlen($v) === 10) {
        $normalized = $v;
    }
    echo "Original: [{$v}] -> ltrim: [{$stripped}] -> str_pad: [{$normalized}]" . PHP_EOL;
}
