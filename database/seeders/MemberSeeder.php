<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Marcello',
                'gender' => 'male'
            ],
            [
                'name' => 'Marshan',
                'gender' => 'female'
            ],
            [
                'name' => 'Bpk Pdt. Royders',
                'gender' => 'male'
            ],
            [
                'name' => 'Cilly',
                'gender' => 'female'
            ],
            [
                'name' => 'Abel',
                'gender' => 'male'
            ],
            [
                'name' => 'Gici',
                'gender' => 'female'
            ],
        ];

        foreach ($members as $member) {
            Member::updateOrCreate(
                ['name' => $member['name']],
                [
                    'gender' => $member['gender']
                ]
            );
        }
    }
}
