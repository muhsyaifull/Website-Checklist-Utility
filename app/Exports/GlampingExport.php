<?php

namespace App\Exports;

use App\Models\Location;
use App\Models\TokenReading;
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

class GlampingExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $month;
    protected $year;
    protected $locations;
    protected $readings;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

        $category = UtilityCategory::where('slug', 'glamping_token')->first();
        $this->locations = Location::where('utility_category_id', $category->id)->get();

        $this->readings = TokenReading::whereIn('location_id', $this->locations->pluck('id'))
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

            $row = [$no++, $date->format('j F Y')];

            foreach ($this->locations as $location) {
                $reading = $dayReadings->where('location_id', $location->id)->first();

                if ($reading) {
                    // Previous balance
                    $row[] = number_format($reading->previous_balance, 0);
                    // Top up amount
                    $row[] = $reading->top_up_amount > 0 ? number_format($reading->top_up_amount, 0) : '';
                    // Current balance  
                    $row[] = number_format($reading->current_balance, 0);
                    // Daily usage
                    $row[] = $reading->daily_usage > 0 ? number_format($reading->daily_usage, 0) : '';
                    // Status indicator
                    $status = '';
                    if ($reading->current_balance > 50000) {
                        $status = 'HIJAU';
                    } elseif ($reading->current_balance >= 25000) {
                        $status = 'KUNING';
                    } else {
                        $status = 'MERAH';
                    }
                    $row[] = $status;
                } else {
                    // Empty cells for this location
                    $row = array_merge($row, ['', '', '', '', '']);
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['No', 'Date (DD/MM/YY)'];

        foreach ($this->locations as $location) {
            $headings[] = $location->name . ' - Previous Balance';
            $headings[] = $location->name . ' - Top Up';
            $headings[] = $location->name . ' - Current Balance';
            $headings[] = $location->name . ' - Daily Usage';
            $headings[] = $location->name . ' - Status';
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header
        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
        $sheet->setCellValue('A1', 'Token Marigold Glamping');

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

                // Center align most columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $lastColumn = $sheet->getHighestColumn();
                $sheet->getStyle('C:' . $lastColumn)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Color-code status cells
                $rowCount = $sheet->getHighestRow();
                for ($row = 4; $row <= $rowCount; $row++) {
                    $statusColumns = [];
                    $colIndex = 3; // Starting from column C
    
                    foreach ($this->locations as $location) {
                        $statusColumns[] = $sheet->getCell([$colIndex + 4, $row]); // Status column for each location
                        $colIndex += 5; // Move to next location (5 columns per location)
                    }

                    foreach ($statusColumns as $cell) {
                        $value = $cell->getValue();
                        if ($value === 'HIJAU') {
                            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                            $cell->getStyle()->getFill()->getStartColor()->setRGB('09b809');
                        } elseif ($value === 'KUNING') {
                            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                            $cell->getStyle()->getFill()->getStartColor()->setRGB('dada09');
                        } elseif ($value === 'MERAH') {
                            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                            $cell->getStyle()->getFill()->getStartColor()->setRGB('e61111');
                        }
                    }
                }
            }
        ];
    }

    public function title(): string
    {
        $monthName = Carbon::create()->month($this->month)->format('F');
        return "Glamping {$monthName} {$this->year}";
    }
}