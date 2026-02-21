<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\MeterReading;
use App\Models\UtilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectricityController extends Controller
{
    /**
     * Display a listing of meter readings.
     */
    public function index(Request $request)
    {
        $category = UtilityCategory::where('slug', 'electricity')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $query = MeterReading::whereIn('location_id', $locations->pluck('id'))
            ->with('location')
            ->orderBy('reading_date', 'desc');

        // Filter by month and year if provided
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('reading_date', $request->month)
                ->whereYear('reading_date', $request->year);
        }

        // Group readings by date for display
        $readings = $query->get()->groupBy(function ($item) {
            return $item->reading_date->format('Y-m-d');
        });

        return view('electricity.index', compact('readings', 'locations'));
    }

    /**
     * Show the form for creating a new reading.
     */
    public function create()
    {
        $category = UtilityCategory::where('slug', 'electricity')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        // Get previous values for each location
        $previousValues = [];
        foreach ($locations as $location) {
            $lastReading = MeterReading::where('location_id', $location->id)
                ->orderBy('reading_date', 'desc')
                ->first();
            $previousValues[$location->id] = $lastReading?->current_value;
        }

        return view('electricity.create', compact('locations', 'previousValues'));
    }

    /**
     * Store a newly created reading in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reading_date' => 'required|date',
            'readings' => 'required|array',
            'readings.*.location_id' => 'required|exists:locations,id',
            'readings.*.current_value' => 'nullable|numeric|min:0',
        ]);

        $readingDate = $request->reading_date;

        DB::beginTransaction();

        try {
            foreach ($request->readings as $readingData) {
                if ($readingData['current_value'] === null) {
                    continue;
                }

                $locationId = $readingData['location_id'];
                $currentValue = $readingData['current_value'];

                // Get previous reading
                $previousReading = MeterReading::where('location_id', $locationId)
                    ->where('reading_date', '<', $readingDate)
                    ->orderBy('reading_date', 'desc')
                    ->first();

                $previousValue = $previousReading?->current_value;

                // Validate current >= previous
                if ($previousValue !== null && $currentValue < $previousValue) {
                    DB::rollBack();
                    return back()->withErrors([
                        'readings' => "Nilai meter saat ini tidak boleh lebih kecil dari nilai sebelumnya untuk lokasi tersebut."
                    ])->withInput();
                }

                // Calculate daily usage
                $dailyUsage = $previousValue !== null ? $currentValue - $previousValue : null;

                MeterReading::updateOrCreate(
                    [
                        'location_id' => $locationId,
                        'reading_date' => $readingDate,
                    ],
                    [
                        'previous_value' => $previousValue,
                        'current_value' => $currentValue,
                        'daily_usage' => $dailyUsage,
                        'created_by' => auth()->id(),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('electricity.index')
                ->with('success', 'Data pembacaan meter listrik berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified reading.
     */
    public function edit(string $date)
    {
        $category = UtilityCategory::where('slug', 'electricity')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $readings = MeterReading::whereIn('location_id', $locations->pluck('id'))
            ->whereDate('reading_date', $date)
            ->get()
            ->keyBy('location_id');

        // Get previous values for each location
        $previousValues = [];
        foreach ($locations as $location) {
            $lastReading = MeterReading::where('location_id', $location->id)
                ->where('reading_date', '<', $date)
                ->orderBy('reading_date', 'desc')
                ->first();
            $previousValues[$location->id] = $lastReading?->current_value;
        }

        return view('electricity.edit', compact('locations', 'readings', 'date', 'previousValues'));
    }

    /**
     * Update the specified reading in storage.
     */
    public function update(Request $request, string $date)
    {
        $request->validate([
            'readings' => 'required|array',
            'readings.*.location_id' => 'required|exists:locations,id',
            'readings.*.current_value' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->readings as $readingData) {
                $locationId = $readingData['location_id'];
                $currentValue = $readingData['current_value'];

                // Get previous reading
                $previousReading = MeterReading::where('location_id', $locationId)
                    ->where('reading_date', '<', $date)
                    ->orderBy('reading_date', 'desc')
                    ->first();

                $previousValue = $previousReading?->current_value;

                // Validate current >= previous
                if ($currentValue !== null && $previousValue !== null && $currentValue < $previousValue) {
                    DB::rollBack();
                    return back()->withErrors([
                        'readings' => "Nilai meter saat ini tidak boleh lebih kecil dari nilai sebelumnya."
                    ])->withInput();
                }

                if ($currentValue === null) {
                    // Delete if exists
                    MeterReading::where('location_id', $locationId)
                        ->whereDate('reading_date', $date)
                        ->delete();
                } else {
                    $dailyUsage = $previousValue !== null ? $currentValue - $previousValue : null;

                    MeterReading::updateOrCreate(
                        [
                            'location_id' => $locationId,
                            'reading_date' => $date,
                        ],
                        [
                            'previous_value' => $previousValue,
                            'current_value' => $currentValue,
                            'daily_usage' => $dailyUsage,
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->route('electricity.index')
                ->with('success', 'Data pembacaan meter listrik berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified reading from storage.
     */
    public function destroy(string $date)
    {
        $category = UtilityCategory::where('slug', 'electricity')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        MeterReading::whereIn('location_id', $locations->pluck('id'))
            ->whereDate('reading_date', $date)
            ->delete();

        return redirect()->route('electricity.index')
            ->with('success', 'Data pembacaan meter listrik berhasil dihapus.');
    }
}
