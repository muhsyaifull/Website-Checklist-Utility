<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\MeterReading;
use App\Models\TokenReading;
use App\Models\UtilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $month = (int) $request->get('month', date('n')); // Current month (1-12)
        $year = (int) $request->get('year', date('Y'));   // Current year

        // Get electricity data
        $electricityCategory = UtilityCategory::where('slug', 'electricity')->first();
        $electricityLocations = Location::where('utility_category_id', $electricityCategory->id)->get();

        $electricityTotal = MeterReading::whereIn('location_id', $electricityLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->sum('daily_usage');

        $electricityByLocation = MeterReading::select('location_id', DB::raw('SUM(daily_usage) as total_usage'))
            ->whereIn('location_id', $electricityLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy('location_id')
            ->with('location')
            ->get();

        // Get water data
        $waterCategory = UtilityCategory::where('slug', 'water')->first();
        $waterLocations = Location::where('utility_category_id', $waterCategory->id)->get();

        $waterTotal = MeterReading::whereIn('location_id', $waterLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->sum('daily_usage');

        $waterByLocation = MeterReading::select('location_id', DB::raw('SUM(daily_usage) as total_usage'))
            ->whereIn('location_id', $waterLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy('location_id')
            ->with('location')
            ->get();

        // Get glamping token data
        $glampingCategory = UtilityCategory::where('slug', 'glamping_token')->first();
        $glampingLocations = Location::where('utility_category_id', $glampingCategory->id)->get();

        // Get the latest reading for each glamping location
        $latestGlampingReadings = [];
        $glampingStats = [];

        foreach ($glampingLocations as $location) {
            // Get latest reading for current month
            $latestReading = TokenReading::where('location_id', $location->id)
                ->whereMonth('reading_date', $month)
                ->whereYear('reading_date', $year)
                ->orderBy('reading_date', 'desc')
                ->first();

            // Get first reading of current month
            $firstReadingThisMonth = TokenReading::where('location_id', $location->id)
                ->whereMonth('reading_date', $month)
                ->whereYear('reading_date', $year)
                ->orderBy('reading_date', 'asc')
                ->first();

            // If no data exists for this month, set all values to 0
            if (!$latestReading && !$firstReadingThisMonth) {
                $glampingStats[$location->id] = [
                    'location' => $location,
                    'saldo_sekarang' => 0,
                    'saldo_terpakai' => 0,
                    'total_topup' => 0,
                ];
                continue;
            }

            // Get the last reading from previous month (to determine starting balance)
            $lastReadingPreviousMonth = TokenReading::where('location_id', $location->id)
                ->where(function ($query) use ($month, $year) {
                    $query->whereYear('reading_date', '<', $year)
                        ->orWhere(function ($q) use ($month, $year) {
                            $q->whereYear('reading_date', $year)
                                ->whereMonth('reading_date', '<', $month);
                        });
                })
                ->orderBy('reading_date', 'desc')
                ->first();

            $saldoSekarang = $latestReading
                ? ($latestReading->token_value + ($latestReading->top_up_amount ?? 0))
                : 0;

            // Get total top-up for the month
            $totalTopUp = TokenReading::where('location_id', $location->id)
                ->whereMonth('reading_date', $month)
                ->whereYear('reading_date', $year)
                ->sum('top_up_amount');

            // Calculate starting balance (saldo awal)
            $saldoAwal = 0;
            if ($lastReadingPreviousMonth) {
                $saldoAwal = $lastReadingPreviousMonth->token_value + ($lastReadingPreviousMonth->top_up_amount ?? 0);
            } elseif ($firstReadingThisMonth) {
                $saldoAwal = $firstReadingThisMonth->token_value;
            }

            // Calculate used balance using formula:
            $saldoTerpakai = $saldoAwal + $totalTopUp - $saldoSekarang;
            if ($saldoTerpakai < 0)
                $saldoTerpakai = 0;

            $glampingStats[$location->id] = [
                'location' => $location,
                'saldo_sekarang' => $saldoSekarang,
                'saldo_terpakai' => $saldoTerpakai,
                'total_topup' => $totalTopUp,
            ];
        }

        // Sort glamping stats by location name for consistent display
        $glampingStats = collect($glampingStats)->sortBy(function ($item) {
            return $item['location']->name;
        })->values()->all();

        $glampingTotal = collect($glampingStats)->sum('saldo_sekarang');
        $glampingTerpakai = collect($glampingStats)->sum('saldo_terpakai');
        $glampingTopUpTotal = collect($glampingStats)->sum('total_topup');

        $glampingByLocation = TokenReading::select('location_id', DB::raw('SUM(token_value) as total_tokens'))
            ->whereIn('location_id', $glampingLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy('location_id')
            ->with('location')
            ->get();

        // Daily chart data for electricity
        $electricityDailyData = MeterReading::select(
            DB::raw('DATE(reading_date) as date'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $electricityLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('DATE(reading_date)'))
            ->orderBy('date')
            ->get();

        // Daily chart data for water
        $waterDailyData = MeterReading::select(
            DB::raw('DATE(reading_date) as date'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $waterLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('DATE(reading_date)'))
            ->orderBy('date')
            ->get();

        // Monthly recap - only show data for the selected month
        $electricityMonthlyRecap = MeterReading::select(
            DB::raw('MONTH(reading_date) as month'),
            DB::raw('YEAR(reading_date) as year'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $electricityLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('YEAR(reading_date)'), DB::raw('MONTH(reading_date)'))
            ->orderByRaw('MONTH(reading_date) ASC')
            ->get();

        $waterMonthlyRecap = MeterReading::select(
            DB::raw('MONTH(reading_date) as month'),
            DB::raw('YEAR(reading_date) as year'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $waterLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('YEAR(reading_date)'), DB::raw('MONTH(reading_date)'))
            ->orderByRaw('MONTH(reading_date) ASC')
            ->get();

        return view('dashboard', compact(
            'month',
            'year',
            'electricityTotal',
            'electricityByLocation',
            'waterTotal',
            'waterByLocation',
            'glampingTotal',
            'glampingTerpakai',
            'glampingTopUpTotal',
            'glampingStats',
            'glampingByLocation',
            'electricityDailyData',
            'waterDailyData',
            'electricityMonthlyRecap',
            'waterMonthlyRecap'
        ));
    }
}
