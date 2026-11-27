<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Asia Tenggara
            [
                'name' => 'Port of Tanjung Priok (Jakarta)',
                'country' => 'Indonesia',
                'location_code' => 'IDJKT',
                'latitude' => -6.1017270,
                'longitude' => 106.8687360,
            ],
            [
                'name' => 'Port of Singapore',
                'country' => 'Singapore',
                'location_code' => 'SGSIN',
                'latitude' => 1.2644630,
                'longitude' => 103.8222090,
            ],
            [
                'name' => 'Port Klang',
                'country' => 'Malaysia',
                'location_code' => 'MYPKG',
                'latitude' => 3.0000000,
                'longitude' => 101.4000000,
            ],

            // Eropa
            [
                'name' => 'Port of Rotterdam',
                'country' => 'Netherlands',
                'location_code' => 'NLRTM',
                'latitude' => 51.9080000,
                'longitude' => 4.4780000,
            ],
            [
                'name' => 'Port of Hamburg',
                'country' => 'Germany',
                'location_code' => 'DEHAM',
                'latitude' => 53.5330000,
                'longitude' => 9.9670000,
            ],

            // Amerika
            [
                'name' => 'Port of Los Angeles',
                'country' => 'United States',
                'location_code' => 'USLAX',
                'latitude' => 33.7360000,
                'longitude' => -118.2640000,
            ],
            [
                'name' => 'Port of New York / New Jersey',
                'country' => 'United States',
                'location_code' => 'USNYC',
                'latitude' => 40.6690000,
                'longitude' => -74.0450000,
            ],

            // Australia
            [
                'name' => 'Port of Melbourne',
                'country' => 'Australia',
                'location_code' => 'AUMEL',
                'latitude' => -37.8300000,
                'longitude' => 144.9210000,
            ],
            [
                'name' => 'Port of Sydney',
                'country' => 'Australia',
                'location_code' => 'AUSYD',
                'latitude' => -33.8530000,
                'longitude' => 151.2110000,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['location_code' => $location['location_code']],
                $location
            );
        }
    }
}

