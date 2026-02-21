@extends('adminlte::page')

@section('title', 'Tambah Data Listrik')

@section('content_header')
<h1><i class="fas fa-bolt text-warning"></i> <span class="d-none d-sm-inline">Tambah Data Pembacaan Listrik</span><span class="d-sm-none">Tambah Listrik</span></h1>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Input Meter Listrik</h3>
    </div>
    <form action="{{ route('electricity.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="reading_date">Tanggal</label>
                <input type="date" class="form-control" id="reading_date" name="reading_date"
                    value="{{ old('reading_date', date('Y-m-d')) }}" required style="max-width: 250px;">
            </div>

            <hr>
            <h5 class="mb-3">Pembacaan Meter per Lokasi</h5>

            @foreach($locations as $index => $location)
                <div class="card mb-3 border-left-warning">
                    <div class="card-header bg-light py-2">
                        <strong>{{ $location->name }}</strong>
                    </div>
                    <div class="card-body py-2">
                        <input type="hidden" name="readings[{{ $index }}][location_id]" value="{{ $location->id }}">
                        <div class="row">
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="small text-muted mb-1">Previous</label>
                                <input type="text" class="form-control form-control-sm bg-light"
                                    value="{{ $previousValues[$location->id] ? number_format($previousValues[$location->id], 2) : '-' }}"
                                    readonly disabled>
                            </div>
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="small text-muted mb-1">Current <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control current-value"
                                        name="readings[{{ $index }}][current_value]" id="current_{{ $location->id }}"
                                        data-previous="{{ $previousValues[$location->id] ?? 0 }}"
                                        data-location="{{ $location->id }}" placeholder="Meter saat ini"
                                        min="{{ $previousValues[$location->id] ?? 0 }}"
                                        value="{{ old('readings.' . $index . '.current_value') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">kWh</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="small text-muted mb-1">Usage</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control usage-display bg-light" id="usage_{{ $location->id }}"
                                        readonly disabled placeholder="Auto">
                                    <div class="input-group-append">
                                        <span class="input-group-text">kWh</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <a href="{{ route('electricity.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Kembali</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    
    @media (max-width: 576px) {
        .content-header h1 {
            font-size: 1.3rem;
        }
        .card-body {
            padding: 0.75rem;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.current-value').on('input', function () {
            var currentVal = parseFloat($(this).val()) || 0;
            var previousVal = parseFloat($(this).data('previous')) || 0;
            var locationId = $(this).data('location');
            var usage = currentVal - previousVal;

            if (currentVal >= previousVal && currentVal > 0) {
                $('#usage_' + locationId).val(usage.toFixed(2));
                $(this).removeClass('is-invalid');
            } else if (currentVal > 0) {
                $('#usage_' + locationId).val('Error');
                $(this).addClass('is-invalid');
            } else {
                $('#usage_' + locationId).val('');
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
@stop