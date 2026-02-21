<?php

namespace Database\Seeders;

use App\Models\UtilityCategory;
use Illuminate\Database\Seeder;

class UtilityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electricity', 'slug' => 'electricity'],
            ['name' => 'Water', 'slug' => 'water'],
            ['name' => 'Glamping Token', 'slug' => 'glamping_token'],
        ];

        foreach ($categories as $category) {
            UtilityCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
