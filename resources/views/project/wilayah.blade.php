@extends('layouts.app')

@section('content')

<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-city"></i>
                </span>
                Wilayah Indonesia
            </h3>
        </div>

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Pilih Wilayah</h4>

                <div class="row g-3">

                    <div class="col-md-3">
                        <label>Provinsi</label>
                        <select id="provinsi" class="form-control">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Kota</label>
                        <select id="kota" class="form-control">
                            <option value="">Pilih Kota</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Kecamatan</label>
                        <select id="kecamatan" class="form-control">
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Kelurahan</label>
                        <select id="kelurahan" class="form-control">
                            <option value="">Pilih Kelurahan</option>
                        </select>
                    </div>

                </div>

                <hr class="my-4">

                <h4 class="card-title">Data Wilayah Dipilih</h4>

                <div class="table-responsive">

                    <table class="table table-bordered" id="tableWilayah">

                        <thead class="table-light">
                            <tr>
                                <th>Provinsi</th>
                                <th>Kota</th>
                                <th>Kecamatan</th>
                                <th>Kelurahan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>

    @endsection

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('assets/js/wilayah.js') }}"></script>
    @endpush