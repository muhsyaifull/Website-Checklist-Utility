<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\TokenReading;
use App\Models\UtilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlampingController extends Controller
{
    /**
     * Display a listing of token readings.
     */
    public function index(Request $request)
    {
        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $query = TokenReading::whereIn('location_id', $locations->pluck('id'))
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

        return view('glamping.index', compact('readings', 'locations'));
    }

    /**
     * Show the form for creating a new reading.
     */
    public function create()
    {
        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        return view('glamping.create', compact('locations'));
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
            'readings.*.token_value' => 'nullable|numeric|min:0',
            'readings.*.top_up_amount' => 'nullable|numeric|min:0',
            'readings.*.indicator_color' => 'nullable|string|max:50',
        ]);

        $readingDate = $request->reading_date;

        DB::beginTransaction();

        try {
            foreach ($request->readings as $readingData) {
                $locationId = $readingData['location_id'];
                $tokenValue = $readingData['token_value'] ?? null;
                $topUpAmount = $readingData['top_up_amount'] ?? null;
                $indicatorColor = $readingData['indicator_color'] ?? null;

                // Only create if at least one value is provided
                if ($tokenValue !== null || $topUpAmount !== null || $indicatorColor !== null) {
                    TokenReading::updateOrCreate(
                        [
                            'location_id' => $locationId,
                            'reading_date' => $readingDate,
                        ],
                        [
                            'token_value' => $tokenValue,
                            'top_up_amount' => $topUpAmount,
                            'indicator_color' => $indicatorColor,
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->route('glamping.index')
                ->with('success', 'Data token glamping berhasil disimpan.');
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
        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $readings = TokenReading::whereIn('location_id', $locations->pluck('id'))
            ->whereDate('reading_date', $date)
            ->get()
            ->keyBy('location_id');

        return view('glamping.edit', compact('locations', 'readings', 'date'));
    }

    /**
     * Update the specified reading in storage.
     */
    public function update(Request $request, string $date)
    {
        $request->validate([
            'readings' => 'required|array',
            'readings.*.location_id' => 'required|exists:locations,id',
            'readings.*.token_value' => 'nullable|numeric|min:0',
            'readings.*.top_up_amount' => 'nullable|numeric|min:0',
            'readings.*.indicator_color' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->readings as $readingData) {
                $locationId = $readingData['location_id'];
                $tokenValue = $readingData['token_value'] ?? null;
                $topUpAmount = $readingData['top_up_amount'] ?? null;
                $indicatorColor = $readingData['indicator_color'] ?? null;

                if ($tokenValue === null && $topUpAmount === null && ($indicatorColor === null || $indicatorColor === '')) {
                    // Delete if exists and all values are null
                    TokenReading::where('location_id', $locationId)
                        ->whereDate('reading_date', $date)
                        ->delete();
                } else {
                    TokenReading::updateOrCreate(
                        [
                            'location_id' => $locationId,
                            'reading_date' => $date,
                        ],
                        [
                            'token_value' => $tokenValue,
                            'top_up_amount' => $topUpAmount,
                            'indicator_color' => $indicatorColor,
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->route('glamping.index')
                ->with('success', 'Data token glamping berhasil diperbarui.');
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
        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        TokenReading::whereIn('location_id', $locations->pluck('id'))
            ->whereDate('reading_date', $date)
            ->delete();

        return redirect()->route('glamping.index')
            ->with('success', 'Data token glamping berhasil dihapus.');
    }
}
