<?php

namespace App\Exports;

use App\Models\Location;
use App\Models\MeterReading;
use App\Models\UtilityCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ElectricityExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $month;
    protected $year;
    protected $locations;
    protected $readings;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

        $category = UtilityCategory::where('slug', 'electricity')->first();
        $this->locations = Location::where('utility_category_id', $category->id)->get();

        $this->readings = MeterReading::whereIn('location_id', $this->locations->pluck('id'))
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->orderBy('reading_date')
            ->get()
            ->groupBy(function ($item) {
                return $item->reading_date->format('Y-m-d');
            })
            ->flatten();
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function array(): array
    {
        $data = [];
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $no = 1;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayReadings = $this->readings->filter(function ($reading) use ($dateStr) {
                return $reading->reading_date->format('Y-m-d') === $dateStr;
            });

            $previousValue = '';
            $dailyUsage = 0;

            $firstReading = $dayReadings->first();
            if ($firstReading) {
                $previousValue = number_format($firstReading->previous_value, 0);
            }

            $dailyUsage = $dayReadings->sum('daily_usage');

            $row = [
                $no++,
                $date->format('j F Y'),
                $previousValue,
            ];

            foreach ($this->locations as $location) {
                $reading = $dayReadings->where('location_id', $location->id)->first();
                $row[] = $reading ? number_format($reading->current_value, 0) : '';
            }

            $row[] = $dailyUsage > 0 ? number_format($dailyUsage, 0) : '';
            $data[] = $row;
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['No', 'Date (DD/MM/YY)', 'Previous Meter Reading (kWh)'];

        foreach ($this->locations as $location) {
            $headings[] = $location->name;
        }

        $headings[] = 'Daily Electricity Use (kWh)';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header
        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
        $sheet->setCellValue('A1', 'Electricity Meter RAI Reading Data');

        // Style for title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Style for headers
        $headerRange = 'A3:' . $sheet->getHighestColumn() . '3';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'F0F0F0']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Add borders to all data
                $dataRange = 'A3:' . $sheet->getHighestColumn() . $sheet->getHighestRow();
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ]);

                // Center align number columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $lastColumn = $sheet->getHighestColumn();
                $sheet->getStyle('C:' . $lastColumn)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }

    public function title(): string
    {
        $monthName = Carbon::create()->month($this->month)->format('F');
        return "Electricity {$monthName} {$this->year}";
    }
}