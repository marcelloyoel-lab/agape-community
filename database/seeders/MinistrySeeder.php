<?php

namespace Database\Seeders;

use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ministries = [
            [
                'name' => 'MC',
                'display_order' => 1,
                'allow_multiple_members' => false,
            ],
            [
                'name' => 'Pelayan Firman',
                'display_order' => 2,
                'allow_multiple_members' => false,
            ],
            [
                'name' => 'Music',
                'display_order' => 3,
                'allow_multiple_members' => true,
            ],
            [
                'name' => 'Multimedia',
                'display_order' => 4,
                'allow_multiple_members' => false,
            ],
        ];

        foreach ($ministries as $ministry) {
            Ministry::updateOrCreate(
                ['name' => $ministry['name']],
                [
                    'display_order' => $ministry['display_order'],
                    'allow_multiple_members' => $ministry['allow_multiple_members'],
                    'is_active' => true,
                ]
            );
        }
    }
}