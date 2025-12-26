<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Court;

$courts = Court::where('status', 'available')
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

echo 'Found ' . $courts->count() . ' courts with coordinates:' . PHP_EOL;
foreach($courts as $court) {
    echo '- ' . $court->name . ' (' . $court->latitude . ', ' . $court->longitude . ')' . PHP_EOL;
    echo '  Address: ' . ($court->address ?: $court->location) . PHP_EOL;
    echo '  Price: ' . number_format($court->price_per_hour) . ' VND/hour' . PHP_EOL;
    echo PHP_EOL;
}
