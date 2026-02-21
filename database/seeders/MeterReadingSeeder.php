<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\MeterReading;
use App\Models\UtilityCategory;
use Illuminate\Database\Seeder;

class MeterReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Electricity Meter Reading Data from Image
        $this->seedElectricityData();

        // Seed Water Meter Reading Data from Image
        $this->seedWaterData();
    }

    private function seedElectricityData(): void
    {
        $electricity = UtilityCategory::where('slug', 'electricity')->first();
        if (!$electricity)
            return;

        $rumahAtsiri = Location::where('utility_category_id', $electricity->id)
            ->where('name', 'Rumah Atsiri')->first();
        $glamping = Location::where('utility_category_id', $electricity->id)
            ->where('name', 'Glamping')->first();

        if (!$rumahAtsiri || !$glamping)
            return;

        // Data from Electricity Meter RAI Reading Data image
        $electricityData = [
            // [date, rumah_atsiri_current, glamping_current]
            ['2026-02-01', 53203, 35895],
            ['2026-02-02', 53212, 35015],
            ['2026-02-03', 53221, 36036],
            ['2026-02-04', 53245, 36065],
            ['2026-02-05', 53261, 36089],
            ['2026-02-06', 53277, 36112],
            ['2026-02-07', 53291, 36137],
            ['2026-02-09', 53320, 36170],
            ['2026-02-10', 53334, 36193],
            ['2026-02-11', 53348, 36212],
            ['2026-02-12', 53358, 36235],
            ['2026-02-13', 53374, 36266],
            ['2026-02-14', 53389, 36291],
            ['2026-02-15', 53423, 36351],
            ['2026-02-17', 53440, 36374],
            ['2026-02-18', 53456, 36401],
            ['2026-02-19', 53469, 36416],
            ['2026-02-20', 53481, 36429],
            ['2026-02-21', 53494, 36449],
        ];

        $prevRumahAtsiri = null;
        $prevGlamping = null;

        foreach ($electricityData as $data) {
            // Rumah Atsiri
            MeterReading::firstOrCreate(
                ['location_id' => $rumahAtsiri->id, 'reading_date' => $data[0]],
                [
                    'previous_value' => $prevRumahAtsiri,
                    'current_value' => $data[1],
                    'daily_usage' => $prevRumahAtsiri !== null ? $data[1] - $prevRumahAtsiri : null,
                ]
            );
            $prevRumahAtsiri = $data[1];

            // Glamping
            MeterReading::firstOrCreate(
                ['location_id' => $glamping->id, 'reading_date' => $data[0]],
                [
                    'previous_value' => $prevGlamping,
                    'current_value' => $data[2],
                    'daily_usage' => $prevGlamping !== null ? $data[2] - $prevGlamping : null,
                ]
            );
            $prevGlamping = $data[2];
        }
    }

    private function seedWaterData(): void
    {
        $water = UtilityCategory::where('slug', 'water')->first();
        if (!$water)
            return;

        $sumurUtama = Location::where('utility_category_id', $water->id)
            ->where('name', 'Sumur Utama')->first();
        $pam = Location::where('utility_category_id', $water->id)
            ->where('name', 'PAM')->first();
        $marigold = Location::where('utility_category_id', $water->id)
            ->where('name', 'Marigold')->first();

        if (!$sumurUtama || !$pam || !$marigold)
            return;

        // Data from Water Meter Reading Data image
        $waterData = [
            // [date, sumur_utama, pam, marigold]
            ['2026-02-01', 13272, null, 4591],
            ['2026-02-02', 13272, null, 4592],
            ['2026-02-03', 13284, null, 4607],
            ['2026-02-04', 13286, 2999, 4608],
            ['2026-02-05', 13297, 3004, 4614],
            ['2026-02-06', 13304, 3012, 4620],
            ['2026-02-07', 13311, null, 4626],
            ['2026-02-09', 13333, 3034, 4637],
            ['2026-02-10', 13345, 3039, 4641],
            ['2026-02-11', 13350, 3043, 4646],
            ['2026-02-12', null, null, 4650],
            ['2026-02-13', 13317, 3054, 4658],
            ['2026-02-14', 13364, 2063, 4661],
            ['2026-02-15', 13385, 3074, 4674],
            ['2026-02-17', 13409, null, 4680],
            ['2026-02-18', 13413, 3099, 4686],
            ['2026-02-19', 13427, null, 4691],
            ['2026-02-20', 13437, 3293, 46445],
            ['2026-02-21', 13449, null, 4698],
        ];

        $prevSumur = null;
        $prevPam = null;
        $prevMarigold = null;

        foreach ($waterData as $data) {
            // Sumur Utama
            if ($data[1] !== null) {
                MeterReading::firstOrCreate(
                    ['location_id' => $sumurUtama->id, 'reading_date' => $data[0]],
                    [
                        'previous_value' => $prevSumur,
                        'current_value' => $data[1],
                        'daily_usage' => $prevSumur !== null ? $data[1] - $prevSumur : null,
                    ]
                );
                $prevSumur = $data[1];
            }

            // PAM
            if ($data[2] !== null) {
                MeterReading::firstOrCreate(
                    ['location_id' => $pam->id, 'reading_date' => $data[0]],
                    [
                        'previous_value' => $prevPam,
                        'current_value' => $data[2],
                        'daily_usage' => $prevPam !== null ? $data[2] - $prevPam : null,
                    ]
                );
                $prevPam = $data[2];
            }

            // Marigold
            if ($data[3] !== null) {
                MeterReading::firstOrCreate(
                    ['location_id' => $marigold->id, 'reading_date' => $data[0]],
                    [
                        'previous_value' => $prevMarigold,
                        'current_value' => $data[3],
                        'daily_usage' => $prevMarigold !== null ? $data[3] - $prevMarigold : null,
                    ]
                );
                $prevMarigold = $data[3];
            }
        }
    }
}
