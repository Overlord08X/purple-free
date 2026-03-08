@extends('layouts.app')

@section('title','Data Kota')

@push('styles')
<!-- Select2 CSS -->
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />

<style>
    /* Samakan tinggi select2 dengan bootstrap form-control */
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        padding: 4px 10px;
    }

    /* Posisi teks placeholder */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }

    /* Posisi icon dropdown */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endpush

@section('content')

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-city"></i>
                </span>
                Select Kota
            </h3>
        </div>
        <div class="row">
            <!-- CARD SELECT BIASA -->
            <div class="col-md-6 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Select Biasa</h4>
                        <div class="mb-3">
                            <label>Kota</label>
                            <input type="text" id="inputKota" class="form-control" placeholder="Masukkan nama kota">
                        </div>
                        <button class="btn btn-gradient-primary mb-3" id="btnTambahKota">
                            Tambahkan
                        </button>
                        <div class="mb-3">
                            <label>Select Kota</label>
                            <select id="selectKota" class="form-control">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                        <div>
                            <b>Kota Terpilih :</b>
                            <span id="kotaTerpilih"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD SELECT2 -->
            <div class="col-md-6 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Select2</h4>
                        <div class="mb-3">
                            <label>Kota</label>
                            <input type="text" id="inputKota2" class="form-control" placeholder="Masukkan nama kota">
                        </div>
                        <button class="btn btn-gradient-primary mb-3" id="btnTambahKota2">
                            Tambahkan
                        </button>
                        <div class="mb-3">
                            <label>Select Kota</label>
                            <select id="selectKota2" class="form-control">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                        <div>
                            <b>Kota Terpilih :</b>
                            <span id="kotaTerpilih2"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('scripts')
    <!-- Select2 JS -->
    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
    <!-- JS khusus halaman -->
    <script src="{{ asset('assets/js/kota.js') }}"></script>
    @endpush