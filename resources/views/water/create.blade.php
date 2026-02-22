@extends('adminlte::page')

@section('title', 'Tambah Data Air')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-tint text-info"></i> Tambah Data Pembacaan Air</h1>
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

<form action="{{ route('water.store') }}" method="POST">
    @csrf
    
    {{-- Date Input Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="form-group mb-0">
                <label for="reading_date"><i class="fas fa-calendar-alt mr-1"></i> Tanggal</label>
                <input type="date" class="form-control" id="reading_date" name="reading_date"
                    value="{{ old('reading_date', date('Y-m-d')) }}" required style="max-width: 200px;">
            </div>
        </div>
    </div>

    {{-- Location Cards --}}
    <div class="row">
        @foreach($locations as $index => $location)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-info card-outline mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> {{ $location->name }}</h3>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="readings[{{ $index }}][location_id]" value="{{ $location->id }}">
                        
                        <div class="form-group">
                            <label class="small text-muted">Previous</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light"
                                    value="{{ $previousValues[$location->id] ? number_format($previousValues[$location->id], 2) : '-' }}"
                                    readonly disabled>
                                <div class="input-group-append">
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="small text-muted">Current</label>
                            <div class="input-group">
                                <input type="text" class="form-control current-input"
                                    id="current_display_{{ $location->id }}"
                                    data-previous="{{ $previousValues[$location->id] ?? 0 }}"
                                    data-location="{{ $location->id }}" 
                                    placeholder="Contoh: 1341300 → 13,413.00">
                                <input type="hidden" class="current-value" 
                                    name="readings[{{ $index }}][current_value]" 
                                    id="current_{{ $location->id }}"
                                    value="{{ old('readings.' . $index . '.current_value') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                            <small class="text-muted">Input angka mentah (÷100) atau langsung desimal</small>
                        </div>
                        
                        <div class="form-group mb-0">
                            <label class="small text-muted">Usage</label>
                            <div class="input-group">
                                <input type="text" class="form-control usage-display bg-light" id="usage_{{ $location->id }}"
                                    readonly disabled placeholder="Auto">
                                <div class="input-group-append">
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Action Buttons --}}
    <div class="card">
        <div class="card-body d-flex flex-column flex-sm-row justify-content-between">
            <a href="{{ route('water.index') }}" class="btn btn-secondary mb-2 mb-sm-0">
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