@extends('adminlte::page')

@section('title', 'Edit Data Glamping')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-campground text-success"></i> Edit Data Glamping</h1>
    <span class="badge badge-secondary">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
</div>
@stop

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<form action="{{ route('glamping.update', $date) }}" method="POST">
    @csrf
    @method('PUT')

    <p class="text-muted small">Semua field bersifat opsional. Kosongkan jika tidak ada data.</p>

    {{-- Location Cards --}}
    <div class="row">
        @foreach($locations as $index => $location)
            @php
                $reading = $readings[$location->id] ?? null;
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-success card-outline mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title">
                            <i class="fas fa-campground mr-1"></i> {{ $location->name }}
                        </h3>
                        @if($location->meter_code)
                            <span class="float-right"><small class="text-muted">{{ $location->meter_code }}</small></span>
                        @endif
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="readings[{{ $index }}][location_id]" value="{{ $location->id }}">

                        <div class="form-group">
                            <label class="small text-muted">Token</label>
                            <input type="number" step="0.01" class="form-control" name="readings[{{ $index }}][token_value]"
                                placeholder="Nilai token"
                                value="{{ old('readings.' . $index . '.token_value', $reading?->token_value) }}">
                        </div>

                        <div class="form-group mb-0">
                            <label class="small text-muted">Warna Indikator</label>
                            @php
                                $currentColor = old('readings.' . $index . '.indicator_color', $reading?->indicator_color);
                            @endphp
                            <select class="form-control" name="readings[{{ $index }}][indicator_color]">
                                <option value="">-- Pilih Warna --</option>
                                <option value="H" {{ $currentColor == 'H' ? 'selected' : '' }}>H (Hijau)</option>
                                <option value="K" {{ $currentColor == 'K' ? 'selected' : '' }}>K (Kuning)</option>
                                <option value="M" {{ $currentColor == 'M' ? 'selected' : '' }}>M (Merah)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Action Buttons --}}
    <div class="card">
        <div class="card-body d-flex flex-column flex-sm-row justify-content-between">
            <a href="{{ route('glamping.index') }}" class="btn btn-secondary mb-2 mb-sm-0">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update
            </button>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
    @media (max-width: 576px) {
        .content-header h1 {
            font-size: 1.3rem;
        }
    }
</style>
@stop