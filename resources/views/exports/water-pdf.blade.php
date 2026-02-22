<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Water Meter Reading Data</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 5px;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 10px;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .header-group {
            background-color: #e0e0e0;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            padding-right: 50px;
        }

        .footer p {
            margin: 3px 0;
        }

        .signature-space {
            height: 40px;
        }
    </style>
</head>

<body>
    <h1>Water Meter Reading Data</h1>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2" style="width: 100px;">Date (DD/MM/YY)</th>
                <th rowspan="2" style="width: 100px;">Previous Meter<br>Reading (m3)</th>
                <th colspan="{{ count($locations) }}" class="header-group">Meter Reading (m3)</th>
                <th rowspan="2" style="width: 80px;">Daily Water Use<br>(m3)</th>
            </tr>
            <tr>
                @foreach($locations as $location)
                    <th>{{ $location->name }}</th>
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
                    $previousValue = '';
                    $dailyUsage = 0;

                    // Get first reading of the day for previous value
                    $firstReading = $dayReadings->first();
                    if ($firstReading) {
                        $previousValue = number_format($firstReading->previous_value, 0);
                    }

                    // Sum daily usage
                    $dailyUsage = $dayReadings->sum('daily_usage');
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left">{{ $date->format('j F Y') }}</td>
                    <td>{{ $previousValue }}</td>
                    @foreach($locations as $location)
                        @php
                            $reading = $dayReadings->where('location_id', $location->id)->first();
                        @endphp
                        <td>{{ $reading ? number_format($reading->current_value, 0) : '' }}</td>
                    @endforeach
                    <td>{{ $dailyUsage > 0 ? number_format($dailyUsage, 0) : '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer">
        <p>Diketahui,</p>
        <div class="signature-space"></div>
        <p><strong>Dept Engineering</strong></p>
    </div>
</body>

</html>