<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\TokenReading;
use App\Models\UtilityCategory;
use Illuminate\Database\Seeder;

class TokenReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $glamping = UtilityCategory::where('slug', 'glamping_token')->first();
        if (!$glamping)
            return;

        $irwansyah1 = Location::where('utility_category_id', $glamping->id)
            ->where('name', 'Irwansyah 1 / Marigold 6')->first();
        $irwansyah2 = Location::where('utility_category_id', $glamping->id)
            ->where('name', 'Irwansyah 2 / Marigold 5')->first();
        $irwansyah3 = Location::where('utility_category_id', $glamping->id)
            ->where('name', 'Irwansyah 3 / Marigold 3')->first();
        $irwansyah4 = Location::where('utility_category_id', $glamping->id)
            ->where('name', 'Irwansyah 4 / Marigold 2')->first();
        $irwansyah5 = Location::where('utility_category_id', $glamping->id)
            ->where('name', 'Irwansyah 5 / Marigold 1')->first();

        if (!$irwansyah1 || !$irwansyah2 || !$irwansyah3 || !$irwansyah4 || !$irwansyah5)
            return;

        // Data from Token Marigold Glamping image
        // [date, irwansyah1, irwansyah1_color, irwansyah2, irwansyah2_color, irwansyah3, irwansyah3_color, irwansyah4, irwansyah4_color, marigold1, marigold1_color]
        $tokenData = [
            ['2026-02-02', 243.65, null, 160.51, null, 172.05, null, 270.18, null, 1887, null],
            ['2026-02-03', 210.27, null, 160.96, null, 158.10, null, 239.06, null, 785.6, null],
            ['2026-02-04', 204.27, null, 155.79, null, 151.29, null, 254.58, null, 71.40, null],
            ['2026-02-05', 157.53, null, 122.10, null, 126.38, null, 206.96, null, 40.60, null],
            ['2026-02-06', 189.39, 'H', 73.93, 'H', 81.69, 'H', 199.58, 'H', 33.52, 'H'],
            ['2026-02-07', 75.44, null, 310.35, null, 116.25, null, 189.77, null, 269.91, null],
            ['2026-02-09', 169.91, null, 302.30, null, 115.43, null, 15.001, null, 218.69, null],
            ['2026-02-10', 159.52, null, 301.57, null, 114.76, null, 144.99, null, 216.09, null],
            ['2026-02-11', 169.46, null, 304.92, null, 114.15, null, 134.44, null, 265.92, null],
            ['2026-02-13', 146.56, null, 301.42, null, 89.11, null, 124.44, null, 209.52, null],
            ['2026-02-14', 146.90, null, 304.37, null, 81.30, null, 99.83, null, 207.24, null],
            ['2026-02-17', 77.89, null, 236.95, null, 12.34, null, 62.90, null, 99.02, null],
            ['2026-02-18', 99.87, null, 236.90, null, 239.38, null, 295.06, null, 84.33, null],
            ['2026-02-19', 77.72, null, 236.85, null, 214.86, null, 283.50, null, 89.28, null],
            ['2026-02-21', 96.04, null, 200.03, null, 215.73, null, 293.08, null, 79.99, null],
        ];

        foreach ($tokenData as $data) {
            // Irwansyah 1
            if ($data[1] !== null) {
                TokenReading::firstOrCreate(
                    ['location_id' => $irwansyah1->id, 'reading_date' => $data[0]],
                    [
                        'token_value' => $data[1],
                        'indicator_color' => $data[2],
                    ]
                );
            }

            // Irwansyah 2
            if ($data[3] !== null) {
                TokenReading::firstOrCreate(
                    ['location_id' => $irwansyah2->id, 'reading_date' => $data[0]],
                    [
                        'token_value' => $data[3],
                        'indicator_color' => $data[4],
                    ]
                );
            }

            // Irwansyah 3
            if ($data[5] !== null) {
                TokenReading::firstOrCreate(
                    ['location_id' => $irwansyah3->id, 'reading_date' => $data[0]],
                    [
                        'token_value' => $data[5],
                        'indicator_color' => $data[6],
                    ]
                );
            }

            // Irwansyah 4
            if ($data[7] !== null) {
                TokenReading::firstOrCreate(
                    ['location_id' => $irwansyah4->id, 'reading_date' => $data[0]],
                    [
                        'token_value' => $data[7],
                        'indicator_color' => $data[8],
                    ]
                );
            }

            // Irwansyah 5 / Marigold 1
            if ($data[9] !== null) {
                TokenReading::firstOrCreate(
                    ['location_id' => $irwansyah5->id, 'reading_date' => $data[0]],
                    [
                        'token_value' => $data[9],
                        'indicator_color' => $data[10],
                    ]
                );
            }
        }
    }
}
