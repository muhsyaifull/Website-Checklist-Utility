<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\UtilityCategory;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Electricity locations
        $electricity = UtilityCategory::where('slug', 'electricity')->first();
        if ($electricity) {
            $electricityLocations = [
                ['name' => 'Rumah Atsiri', 'meter_code' => null, 'description' => 'Lokasi Rumah Atsiri'],
                ['name' => 'Glamping', 'meter_code' => null, 'description' => 'Lokasi Glamping'],
            ];

            foreach ($electricityLocations as $location) {
                Location::firstOrCreate(
                    ['utility_category_id' => $electricity->id, 'name' => $location['name']],
                    array_merge($location, ['utility_category_id' => $electricity->id])
                );
            }
        }

        // Water locations
        $water = UtilityCategory::where('slug', 'water')->first();
        if ($water) {
            $waterLocations = [
                ['name' => 'Sumur Utama', 'meter_code' => null, 'description' => 'Meter sumur utama'],
                ['name' => 'PAM', 'meter_code' => null, 'description' => 'Meter PAM'],
                ['name' => 'Marigold', 'meter_code' => null, 'description' => 'Meter Marigold'],
            ];

            foreach ($waterLocations as $location) {
                Location::firstOrCreate(
                    ['utility_category_id' => $water->id, 'name' => $location['name']],
                    array_merge($location, ['utility_category_id' => $water->id])
                );
            }
        }

        // Glamping Token locations
        $glamping = UtilityCategory::where('slug', 'glamping_token')->first();
        if ($glamping) {
            $glampingLocations = [
                ['name' => 'Irwansyah 1 / Marigold 6', 'meter_code' => '14491784816', 'description' => 'Irwansyah 1 - Marigold 6'],
                ['name' => 'Irwansyah 2 / Marigold 5', 'meter_code' => '14491784832', 'description' => 'Irwansyah 2 - Marigold 5'],
                ['name' => 'Irwansyah 3 / Marigold 3', 'meter_code' => '14491789831', 'description' => 'Irwansyah 3 - Marigold 3'],
                ['name' => 'Irwansyah 4 / Marigold 2', 'meter_code' => '14491784733', 'description' => 'Irwansyah 4 - Marigold 2'],
                ['name' => 'Irwansyah 5 / Marigold 1', 'meter_code' => '14491789757', 'description' => 'Irwansyah 5 - Marigold 1'],
            ];

            foreach ($glampingLocations as $location) {
                Location::firstOrCreate(
                    ['utility_category_id' => $glamping->id, 'name' => $location['name']],
                    array_merge($location, ['utility_category_id' => $glamping->id])
                );
            }
        }
    }
}
