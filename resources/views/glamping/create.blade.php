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
                            <label class="small text-muted">Saldo</label>
                            <div class="input-group">
                                <input type="text" class="form-control token-input" id="token_display_{{ $location->id }}"
                                    data-location="{{ $location->id }}" placeholder="Saldo saat ini">
                                <input type="hidden" class="token-value" name="readings[{{ $index }}][token_value]"
                                    id="token_{{ $location->id }}" value="{{ old('readings.' . $index . '.token_value') }}">
                            </div>
                            <small class="text-muted">Input angka mentah (÷100) atau langsung desimal</small>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted">Isi Ulang <span class="badge badge-secondary">Opsional</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control topup-input" id="topup_display_{{ $location->id }}"
                                    data-location="{{ $location->id }}" placeholder="Jika ada pengisian">
                                <input type="hidden" class="topup-value" name="readings[{{ $index }}][top_up_amount]"
                                    id="topup_{{ $location->id }}" value="{{ old('readings.' . $index . '.top_up_amount') }}">
                            </div>
                            <small class="text-muted">Kosongkan jika tidak ada isi ulang</small>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted">Total Saldo</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light total-saldo" id="total_{{ $location->id }}"
                                    readonly disabled placeholder="Auto">
                            </div>
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

@section('js')
<script>
    $(document).ready(function () {
        // Function to calculate and display total saldo
        function calculateTotal(locationId) {
            var tokenVal = parseFloat($('#token_' + locationId).val()) || 0;
            var topupVal = parseFloat($('#topup_' + locationId).val()) || 0;
            var total = tokenVal + topupVal;
            
            if (total > 0) {
                $('#total_' + locationId).val(total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            } else {
                $('#total_' + locationId).val('');
            }
        }

        // Smart input handler for sisa saldo
        $('.token-input').on('input', function () {
            var inputVal = $(this).val();
            var locationId = $(this).data('location');

            if (inputVal === '') {
                $('#token_' + locationId).val('');
                calculateTotal(locationId);
                return;
            }

            var decimalValue;

            if (inputVal.includes('.') || inputVal.includes(',')) {
                decimalValue = parseFloat(inputVal.replace(',', '.'));
            } else {
                var rawValue = inputVal.replace(/[^0-9]/g, '');
                decimalValue = parseInt(rawValue) / 100;
            }

            if (isNaN(decimalValue)) {
                return;
            }

            $('#token_' + locationId).val(decimalValue.toFixed(2));
            calculateTotal(locationId);
        });

        // Smart input handler for isi ulang
        $('.topup-input').on('input', function () {
            var inputVal = $(this).val();
            var locationId = $(this).data('location');

            if (inputVal === '') {
                $('#topup_' + locationId).val('');
                calculateTotal(locationId);
                return;
            }

            var decimalValue;

            if (inputVal.includes('.') || inputVal.includes(',')) {
                decimalValue = parseFloat(inputVal.replace(',', '.'));
            } else {
                var rawValue = inputVal.replace(/[^0-9]/g, '');
                decimalValue = parseInt(rawValue) / 100;
            }

            if (isNaN(decimalValue)) {
                return;
            }

            $('#topup_' + locationId).val(decimalValue.toFixed(2));
            calculateTotal(locationId);
        });

        // Reformat on blur for sisa saldo
        $('.token-input').on('blur', function () {
            var locationId = $(this).data('location');
            var hiddenVal = parseFloat($('#token_' + locationId).val());
            if (!isNaN(hiddenVal) && hiddenVal > 0) {
                $(this).val(hiddenVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });

        // Reformat on blur for isi ulang
        $('.topup-input').on('blur', function () {
            var locationId = $(this).data('location');
            var hiddenVal = parseFloat($('#topup_' + locationId).val());
            if (!isNaN(hiddenVal) && hiddenVal > 0) {
                $(this).val(hiddenVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
    });
</script>
@stop