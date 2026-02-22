<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Token Marigold Glamping</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            margin: 5px;
        }

        h1 {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
            font-size: 7px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 7px;
        }

        .location-header {
            background-color: #e0e0e0;
        }

        .text-left {
            text-align: left;
        }

        .indicator-red {
            background-color: #e61111;
        }

        .indicator-yellow {
            background-color: #dada09;
        }

        .indicator-green {
            background-color: #09b809;
        }

        .text-hijau {
            font-weight: bold;
            color: #00cc1b;
        }

        .text-kuning {
            font-weight: bold;
            color: #b8a200;
        }

        .text-merah {
            font-weight: bold;
            color: #cc0000;
        }

        .has-topup {
            color: #00cc1b;
            font-weight: bold;
        }

        .footer {
            margin-top: 10px;
            text-align: right;
            padding-right: 30px;
        }

        .footer p {
            margin: 3px 0;
            font-size: 9px;
        }

        .signature-space {
            height: 35px;
        }

        .small-text {
            font-size: 6px;
            color: #666;
        }
    </style>
</head>

<body>
    <h1>Token Marigold Glamping</h1>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 70px;">Tanggal</th>
                @foreach($locations as $location)
                    <th colspan="2" class="location-header">
                        {{ $location->name }}<br>
                        <span class="small-text">{{ $location->meter_code }}</span>
                    </th>
                @endforeach
            </tr>
            <tr>
                @foreach($locations as $location)
                    <th style="width: 60px;">Saldo</th>
                    <th style="width: 45px;">Indikator</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $startDate = \Carbon\Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                $no = 1;
            @endphp

            @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                @php
                    $dateStr = $date->format('Y-m-d');
                    $dayReadings = $readings->filter(function ($reading) use ($dateStr) {
                        return $reading->reading_date->format('Y-m-d') === $dateStr;
                    });
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left">{{ $date->format('j F Y') }}</td>
                    @foreach($locations as $location)
                        @php
                            $reading = $dayReadings->where('location_id', $location->id)->first();
                            $sisaSaldo = $reading?->token_value ?? 0;
                            $isiUlang = $reading?->top_up_amount ?? 0;
                            $totalSaldo = $sisaSaldo + $isiUlang;
                            $hasTopUp = $isiUlang > 0;
                            
                            $indicatorClass = '';
                            $indicatorText = '';
                            $textClass = '';
                            if ($reading && $reading->indicator_color) {
                                $color = strtoupper(trim($reading->indicator_color));
                                if ($color == 'H' || $color == 'HIJAU' || $color == 'GREEN') {
                                    $indicatorClass = 'indicator-green';
                                    $indicatorText = 'H';
                                    $textClass = 'text-hijau';
                                } elseif ($color == 'K' || $color == 'KUNING' || $color == 'YELLOW') {
                                    $indicatorClass = 'indicator-yellow';
                                    $indicatorText = 'K';
                                    $textClass = 'text-kuning';
                                } elseif ($color == 'M' || $color == 'MERAH' || $color == 'RED') {
                                    $indicatorClass = 'indicator-red';
                                    $indicatorText = 'M';
                                    $textClass = 'text-merah';
                                }
                            }
                        @endphp
                        <td class="{{ $hasTopUp ? 'has-topup' : '' }}" style="font-weight: bold;">
                            {{ $totalSaldo > 0 ? number_format($totalSaldo, 2) : '' }}
                        </td>
                        <td class="{{ $textClass }}">
                            {{ $indicatorText }}
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0;">
                <td colspan="2" style="text-align: right; font-weight: bold;">Total Isi Ulang:</td>
                @foreach($locations as $location)
                    @php
                        $totalTopUp = $readings->where('location_id', $location->id)->sum('top_up_amount');
                    @endphp
                    <td class="has-topup">
                        {{ $totalTopUp > 0 ? '+' . number_format($totalTopUp, 2) : '-' }}
                    </td>
                    <td>-</td>
                @endforeach
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Diketahui,</p>
        <div class="signature-space"></div>
        <p><strong>Dept Engineering</strong></p>
    </div>
</body>

</html>