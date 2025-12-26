<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Court;

$courts = Court::all();
echo 'Total courts: ' . $courts->count() . PHP_EOL;
foreach($courts as $court) {
    echo '- ' . $court->name . ' (Status: ' . $court->status . ', Lat: ' . ($court->latitude ?: 'null') . ', Lng: ' . ($court->longitude ?: 'null') . ')' . PHP_EOL;
}

// Update courts with coordinates for Ho Chi Minh City
$coordinates = [
    ['name' => 'Sân cầu AE Pickelball', 'lat' => 10.7769, 'lng' => 106.7009, 'address' => '123 Đường ABC, Quận 1, TP.HCM'],
    ['name' => 'Court B', 'lat' => 10.8019, 'lng' => 106.7392, 'address' => '456 Đường XYZ, Quận 2, TP.HCM'],
    ['name' => 'Dâu Tây Đà Lạt', 'lat' => 11.9404, 'lng' => 108.4583, 'address' => '789 Đường DEF, Đà Lạt, Lâm Đồng'],
];

foreach($coordinates as $coord) {
    $court = Court::where('name', $coord['name'])->first();
    if($court) {
        $court->update([
            'latitude' => $coord['lat'],
            'longitude' => $coord['lng'],
            'address' => $coord['address']
        ]);
        echo 'Updated ' . $court->name . ' with coordinates' . PHP_EOL;
    }
}

echo PHP_EOL . 'After updates:' . PHP_EOL;
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
