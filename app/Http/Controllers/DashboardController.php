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
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

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

        $glampingTotal = TokenReading::whereIn('location_id', $glampingLocations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->sum('token_value');

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

        // Monthly recap
        $electricityMonthlyRecap = MeterReading::select(
            DB::raw('MONTH(reading_date) as month'),
            DB::raw('YEAR(reading_date) as year'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $electricityLocations->pluck('id'))
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('YEAR(reading_date)'), DB::raw('MONTH(reading_date)'))
            ->orderBy('month')
            ->get();

        $waterMonthlyRecap = MeterReading::select(
            DB::raw('MONTH(reading_date) as month'),
            DB::raw('YEAR(reading_date) as year'),
            DB::raw('SUM(daily_usage) as total')
        )
            ->whereIn('location_id', $waterLocations->pluck('id'))
            ->whereYear('reading_date', $year)
            ->groupBy(DB::raw('YEAR(reading_date)'), DB::raw('MONTH(reading_date)'))
            ->orderBy('month')
            ->get();

        return view('dashboard', compact(
            'month',
            'year',
            'electricityTotal',
            'electricityByLocation',
            'waterTotal',
            'waterByLocation',
            'glampingTotal',
            'glampingByLocation',
            'electricityDailyData',
            'waterDailyData',
            'electricityMonthlyRecap',
            'waterMonthlyRecap'
        ));
    }
}
