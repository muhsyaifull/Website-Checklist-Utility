@extends('adminlte::page')

@section('title', 'Dashboard Monitoring')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-tachometer-alt"></i> <span class="d-none d-sm-inline">Dashboard Monitoring
            Listrik & Air</span><span class="d-sm-none">Dashboard</span></h1>
    <form action="{{ route('dashboard') }}" method="GET" class="d-flex flex-wrap gap-1">
        <select name="month" class="form-control form-control-sm mr-1 mb-1 mb-md-0" style="width: auto;">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                </option>
            @endfor
        </select>
        <select name="year" class="form-control form-control-sm mr-1 mb-1 mb-md-0" style="width: auto;">
            @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fas fa-filter"></i> <span class="d-none d-sm-inline">Filter</span>
        </button>
    </form>
</div>
@stop

@section('content')
<!-- Summary Cards -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($electricityTotal ?? 0, 2) }}</h3>
                <p>Total Listrik (kWh)</p>
            </div>
            <div class="icon">
                <i class="fas fa-bolt"></i>
            </div>
            <a href="{{ route('electricity.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($waterTotal ?? 0, 2) }}</h3>
                <p>Total Air (m³)</p>
            </div>
            <div class="icon">
                <i class="fas fa-tint"></i>
            </div>
            <a href="{{ route('water.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($glampingTotal ?? 0, 2) }}</h3>
                <p>Total Token Glamping</p>
            </div>
            <div class="icon">
                <i class="fas fa-campground"></i>
            </div>
            <a href="{{ route('glamping.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line text-warning"></i>
                    Penggunaan Listrik Harian - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                    {{ $year }}
                </h3>
            </div>
            <div class="card-body">
                <canvas id="electricityChart" style="min-height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line text-info"></i>
                    Penggunaan Air Harian - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                    {{ $year }}
                </h3>
            </div>
            <div class="card-body">
                <canvas id="waterChart" style="min-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Usage by Location -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt text-warning"></i>
                    Penggunaan Listrik per Lokasi
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th class="text-right">Total (kWh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($electricityByLocation as $item)
                            <tr>
                                <td>{{ $item->location->name }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total_usage, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-right">{{ number_format($electricityTotal ?? 0, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tint text-info"></i>
                    Penggunaan Air per Lokasi
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th class="text-right">Total (m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waterByLocation as $item)
                            <tr>
                                <td>{{ $item->location->name }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total_usage, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-right">{{ number_format($waterTotal ?? 0, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Glamping Token by Location -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-campground text-success"></i>
                    Total Token Glamping per Lokasi
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th>Kode Meter</th>
                            <th class="text-right">Total Token</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($glampingByLocation as $item)
                            <tr>
                                <td>{{ $item->location->name }}</td>
                                <td>{{ $item->location->meter_code ?? '-' }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total_tokens, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="2">Total</th>
                            <th class="text-right">{{ number_format($glampingTotal ?? 0, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Recap -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt text-warning"></i>
                    Rekap Bulanan Listrik {{ $year }}
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-right">Total (kWh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($electricityMonthlyRecap as $item)
                            <tr class="{{ $item->month == $month ? 'table-active' : '' }}">
                                <td>{{ \Carbon\Carbon::create()->month($item->month)->translatedFormat('F') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt text-info"></i>
                    Rekap Bulanan Air {{ $year }}
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-right">Total (m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waterMonthlyRecap as $item)
                            <tr class="{{ $item->month == $month ? 'table-active' : '' }}">
                                <td>{{ \Carbon\Carbon::create()->month($item->month)->translatedFormat('F') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Responsive Small Box */
    .small-box h3 {
        font-size: 2.2rem;
    }

    @media (max-width: 576px) {
        .small-box h3 {
            font-size: 1.5rem;
        }

        .small-box p {
            font-size: 0.85rem;
        }

        .small-box .icon {
            font-size: 50px;
        }

        .content-header h1 {
            font-size: 1.3rem;
        }
    }

    /* Responsive Cards */
    .card-title {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .card-title {
            font-size: 0.9rem;
        }

        .table td,
        .table th {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
    }

    /* Better touch targets */
    @media (max-width: 576px) {
        .btn {
            padding: 0.5rem 1rem;
        }

        .form-control-sm {
            min-height: 38px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Electricity Chart
    const electricityCtx = document.getElementById('electricityChart').getContext('2d');
    new Chart(electricityCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($electricityDailyData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray()) !!},
            datasets: [{
                label: 'Penggunaan Listrik (kWh)',
                data: {!! json_encode($electricityDailyData->pluck('total')->toArray()) !!},
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Water Chart
    const waterCtx = document.getElementById('waterChart').getContext('2d');
    new Chart(waterCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($waterDailyData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray()) !!},
            datasets: [{
                label: 'Penggunaan Air (m³)',
                data: {!! json_encode($waterDailyData->pluck('total')->toArray()) !!},
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@stop