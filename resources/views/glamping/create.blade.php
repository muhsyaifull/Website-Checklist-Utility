@extends('adminlte::page')

@section('title', 'Tambah Data Glamping')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-campground text-success"></i> Tambah Data Token Glamping</h1>
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

<form action="{{ route('glamping.store') }}" method="POST">
    @csrf

    {{-- Date Input Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="form-group mb-0">
                <label for="reading_date"><i class="fas fa-calendar-alt mr-1"></i> Tanggal</label>
                <input type="date" class="form-control" id="reading_date" name="reading_date"
                    value="{{ old('reading_date', date('Y-m-d')) }}" required style="max-width: 200px;">
            </div>
            <p class="text-muted small mt-2 mb-0">Semua field bersifat opsional. Kosongkan jika tidak ada data.</p>
        </div>
    </div>

    {{-- Location Cards --}}
    <div class="row">
        @foreach($locations as $index => $location)
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
                                placeholder="Nilai token" value="{{ old('readings.' . $index . '.token_value') }}">
                        </div>

                        <div class="form-group mb-0">
                            <label class="small text-muted">Warna Indikator</label>
                            <select class="form-control" name="readings[{{ $index }}][indicator_color]">
                                <option value="">-- Pilih Warna --</option>
                                <option value="H" {{ old('readings.' . $index . '.indicator_color') == 'H' ? 'selected' : '' }}>H (Hijau)</option>
                                <option value="K" {{ old('readings.' . $index . '.indicator_color') == 'K' ? 'selected' : '' }}>K (Kuning)</option>
                                <option value="M" {{ old('readings.' . $index . '.indicator_color') == 'M' ? 'selected' : '' }}>M (Merah)</option>
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
                <i class="fas fa-save"></i> Simpan
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