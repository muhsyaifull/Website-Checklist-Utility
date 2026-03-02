<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\MeterReading;
use App\Models\TokenReading;
use App\Models\UtilityCategory;
use App\Exports\ElectricityExport;
use App\Exports\WaterExport;
use App\Exports\GlampingExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export electricity data to PDF.
     */
    public function electricityPdf(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $category = UtilityCategory::where('slug', 'electricity')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $readings = MeterReading::whereIn('location_id', $locations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->orderBy('reading_date')
            ->get()
            ->groupBy(function ($item) {
                return $item->reading_date->format('Y-m-d');
            })
            ->flatten();

        $pdf = Pdf::loadView('exports.electricity-pdf', [
            'locations' => $locations,
            'readings' => $readings,
            'month' => $month,
            'year' => $year,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Electricity_Report_{$monthName}_{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export water data to PDF.
     */
    public function waterPdf(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $category = UtilityCategory::where('slug', 'water')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $readings = MeterReading::whereIn('location_id', $locations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->orderBy('reading_date')
            ->get()
            ->groupBy(function ($item) {
                return $item->reading_date->format('Y-m-d');
            })
            ->flatten();

        $pdf = Pdf::loadView('exports.water-pdf', [
            'locations' => $locations,
            'readings' => $readings,
            'month' => $month,
            'year' => $year,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Water_Report_{$monthName}_{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export glamping token data to PDF.
     */
    public function glampingPdf(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $locations = Location::where('utility_category_id', $category->id)->get();

        $readings = TokenReading::whereIn('location_id', $locations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->orderBy('reading_date')
            ->get()
            ->groupBy(function ($item) {
                return $item->reading_date->format('Y-m-d');
            })
            ->flatten();

        $pdf = Pdf::loadView('exports.glamping-pdf', [
            'locations' => $locations,
            'readings' => $readings,
            'month' => $month,
            'year' => $year,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Glamping_Token_Report_{$monthName}_{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export electricity data to Excel.
     */
    public function electricityExcel(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Electricity_Report_{$monthName}_{$year}.xlsx";

        return Excel::download(new ElectricityExport($month, $year), $filename);
    }

    /**
     * Export water data to Excel.
     */
    public function waterExcel(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Water_Report_{$monthName}_{$year}.xlsx";

        return Excel::download(new WaterExport($month, $year), $filename);
    }

    /**
     * Export glamping token data to Excel.
     */
    public function glampingExcel(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $monthName = Carbon::create()->month($month)->format('F');
        $filename = "Glamping_Token_Report_{$monthName}_{$year}.xlsx";

        return Excel::download(new GlampingExport($month, $year), $filename);
    }
}
