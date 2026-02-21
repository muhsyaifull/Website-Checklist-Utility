@extends('adminlte::page')

@section('title', 'Checklist Listrik')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-bolt text-warning"></i> Checklist Listrik</h1>
    <a href="{{ route('electricity.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Tambah Data</span><span
            class="d-sm-none">Tambah</span>
    </a>
</div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data Pembacaan Meter Listrik</h3>
        <div class="card-tools">
            <form action="{{ route('electricity.index') }}" method="GET" class="d-flex flex-wrap">
                <select name="month" class="form-control form-control-sm mr-1 mb-1"
                    style="width: auto; min-width: 100px;">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-control form-control-sm mr-1 mb-1" style="width: auto;">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-sm btn-default mb-1">
                    <i class="fas fa-filter"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Desktop Table View --}}
    <div class="card-body table-responsive p-0 d-none d-lg-block">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    @foreach($locations as $location)
                        <th colspan="3" class="text-center bg-light">{{ $location->name }}</th>
                    @endforeach
                    <th>Aksi</th>
                </tr>
                <tr>
                    <th></th>
                    @foreach($locations as $location)
                        <th class="text-center">Previous</th>
                        <th class="text-center">Current</th>
                        <th class="text-center">Usage</th>
                    @endforeach
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($readings as $date => $dateReadings)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                        @foreach($locations as $location)
                            @php
                                $reading = $dateReadings->where('location_id', $location->id)->first();
                            @endphp
                            <td class="text-center">
                                {{ $reading?->previous_value ? number_format($reading->previous_value, 2) : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $reading?->current_value ? number_format($reading->current_value, 2) : '-' }}
                            </td>
                            <td class="text-center font-weight-bold">
                                @if($reading?->daily_usage !== null)
                                    <span class="text-{{ $reading->daily_usage > 50 ? 'danger' : 'success' }}">
                                        {{ number_format($reading->daily_usage, 2) }} kWh
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('electricity.edit', $date) }}" class="btn btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('electricity.destroy', $date) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + (count($locations) * 3) + 1 }}" class="text-center text-muted">
                            Belum ada data pembacaan meter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="card-body d-lg-none">
        @forelse($readings as $date => $dateReadings)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <strong><i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('electricity.edit', $date) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('electricity.destroy', $date) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        @foreach($locations as $location)
                            @php
                                $reading = $dateReadings->where('location_id', $location->id)->first();
                            @endphp
                            <tr class="bg-secondary text-white">
                                <th colspan="2" class="py-1 px-2">{{ $location->name }}</th>
                            </tr>
                            <tr>
                                <td class="py-1 px-2" style="width: 40%;">Previous</td>
                                <td class="py-1 px-2">
                                    {{ $reading?->previous_value ? number_format($reading->previous_value, 2) : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 px-2">Current</td>
                                <td class="py-1 px-2">
                                    {{ $reading?->current_value ? number_format($reading->current_value, 2) : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 px-2">Usage</td>
                                <td class="py-1 px-2 font-weight-bold">
                                    @if($reading?->daily_usage !== null)
                                        <span class="text-{{ $reading->daily_usage > 50 ? 'danger' : 'success' }}">
                                            {{ number_format($reading->daily_usage, 2) }} kWh
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Belum ada data pembacaan meter.</p>
            </div>
        @endforelse
    </div>
</div>
@stop

@section('css')
<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    @media (max-width: 991px) {
        .card-tools {
            width: 100%;
            margin-top: 0.5rem;
        }

        .card-tools form {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .content-header h1 {
            font-size: 1.3rem;
        }
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>
@stop