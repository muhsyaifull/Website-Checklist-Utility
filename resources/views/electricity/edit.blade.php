@extends('adminlte::page')

@section('title', 'Edit Data Listrik')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h1 class="mb-2 mb-md-0"><i class="fas fa-bolt text-warning"></i> Edit Data Listrik</h1>
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

<form action="{{ route('electricity.update', $date) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Location Cards --}}
    <div class="row">
        @foreach($locations as $index => $location)
            @php
                $reading = $readings[$location->id] ?? null;
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-warning card-outline mb-3">
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
                                    <span class="input-group-text">kWh</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="small text-muted">Current</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control current-value"
                                    name="readings[{{ $index }}][current_value]" id="current_{{ $location->id }}"
                                    data-previous="{{ $previousValues[$location->id] ?? 0 }}"
                                    data-location="{{ $location->id }}" placeholder="Meter saat ini"
                                    min="{{ $previousValues[$location->id] ?? 0 }}"
                                    value="{{ old('readings.' . $index . '.current_value', $reading?->current_value) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">kWh</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-0">
                            <label class="small text-muted">Usage</label>
                            <div class="input-group">
                                <input type="text" class="form-control usage-display bg-light" id="usage_{{ $location->id }}"
                                    readonly disabled
                                    value="{{ $reading?->daily_usage ? number_format($reading->daily_usage, 2) : '' }}">
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

    {{-- Action Buttons --}}
    <div class="card">
        <div class="card-body d-flex flex-column flex-sm-row justify-content-between">
            <a href="{{ route('electricity.index') }}" class="btn btn-secondary mb-2 mb-sm-0">
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