<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Position::firstOrCreate(
          ['name' => 'Governing Board'],
          [
            'slug' => 'gb',
            'num_to_select' => 15,
            'is_active' => 1,
            'is_default' => 1,
          ]
        );
        \App\Models\Position::firstOrCreate(
          ['name' => 'Diaconate - Men'],
          [
            'slug' => 'diam',
            'num_to_select' => 12,
            'is_active' => 1,
            'is_default' => 0,
          ]
        );
        \App\Models\Position::firstOrCreate(
          ['name' => 'Diaconate - Women'],
          [
            'slug' => 'diaw',
            'num_to_select' => 12,
            'is_active' => 1,
            'is_default' => 0,
          ]
        );
    }
}
