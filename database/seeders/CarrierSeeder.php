<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'FastShip',
                'code' => 'FSH',
                'contact_email' => 'ops@fastship.com',
                'contact_phone' => '+1-800-555-0101',
                'is_active' => true,
            ],
            [
                'name' => 'CargoPro',
                'code' => 'CGP',
                'contact_email' => 'support@cargopro.com',
                'contact_phone' => '+1-800-555-0202',
                'is_active' => true,
            ],
            [
                'name' => 'Logistics Partners',
                'code' => 'LGP',
                'contact_email' => 'dispatch@logisticspartners.com',
                'contact_phone' => '+65-6555-0303',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::updateOrCreate(
                ['code' => $carrier['code']],
                $carrier
            );
        }
    }
}

