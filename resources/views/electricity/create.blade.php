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
                                    <input type="text" class="form-control current-input"
                                        id="current_display_{{ $location->id }}"
                                        data-previous="{{ $previousValues[$location->id] ?? 0 }}"
                                        data-location="{{ $location->id }}" 
                                        placeholder="Contoh: 5349400 → 53,494.00">
                                    <input type="hidden" class="current-value" 
                                        name="readings[{{ $index }}][current_value]" 
                                        id="current_{{ $location->id }}"
                                        value="{{ old('readings.' . $index . '.current_value') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">kWh</span>
                                    </div>
                                </div>
                                <small class="text-muted">Input angka mentah (÷100) atau langsung desimal</small>
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
        // Smart meter input handler - supports both raw (÷100) and direct decimal
        $('.current-input').on('input', function () {
            var inputVal = $(this).val();
            var locationId = $(this).data('location');
            var previousVal = parseFloat($(this).data('previous')) || 0;
            
            if (inputVal === '') {
                $('#current_' + locationId).val('');
                $('#usage_' + locationId).val('');
                $(this).removeClass('is-invalid');
                return;
            }
            
            var decimalValue;
            
            // Check if input contains decimal separator (. or ,)
            if (inputVal.includes('.') || inputVal.includes(',')) {
                // Direct decimal input - parse as-is
                decimalValue = parseFloat(inputVal.replace(',', '.'));
            } else {
                // Raw integer input - divide by 100
                var rawValue = inputVal.replace(/[^0-9]/g, '');
                decimalValue = parseInt(rawValue) / 100;
            }
            
            if (isNaN(decimalValue)) {
                return;
            }
            
            // Store decimal value in hidden field
            $('#current_' + locationId).val(decimalValue.toFixed(2));
            
            // Calculate usage
            var usage = decimalValue - previousVal;
            
            if (decimalValue >= previousVal && decimalValue > 0) {
                $('#usage_' + locationId).val(usage.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $(this).removeClass('is-invalid');
            } else if (decimalValue > 0) {
                $('#usage_' + locationId).val('Error');
                $(this).addClass('is-invalid');
            } else {
                $('#usage_' + locationId).val('');
                $(this).removeClass('is-invalid');
            }
        });
        
        // Reformat on blur
        $('.current-input').on('blur', function () {
            var locationId = $(this).data('location');
            var hiddenVal = parseFloat($('#current_' + locationId).val());
            if (!isNaN(hiddenVal) && hiddenVal > 0) {
                $(this).val(hiddenVal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
        });
    });
</script>
@stop