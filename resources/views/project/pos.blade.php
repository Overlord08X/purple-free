@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Point Of Sale')

@section('content')

<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cart"></i>
                </span>
                Point Of Sale
            </h3>
        </div>

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Transaksi Penjualan</h4>

                <div class="row mb-3">

                    <div class="col-md-2">
                        <input type="text" id="idBarang" class="form-control" placeholder="Kode Barang">
                    </div>

                    <div class="col-md-3">
                        <input type="text" id="namaBarang" class="form-control" readonly placeholder="Nama Barang">
                    </div>

                    <div class="col-md-2">
                        <input type="text" id="hargaBarang" class="form-control" readonly placeholder="Harga">
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="jumlah" class="form-control" value="1">
                    </div>

                    <div class="col-md-2">
                        <button id="btnTambah" class="btn btn-gradient-primary btn-block" disabled>

                            <span id="textTambah">Tambahkan</span>

                            <span id="spinnerTambah"
                                class="spinner-border spinner-border-sm ms-2"
                                style="display:none;"></span>

                        </button>
                    </div>

                </div>


                <h4 class="card-title mt-4">Daftar Item</h4>

                <div class="table-responsive">

                    <table class="table table-hover" id="tablePOS">

                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                    </table>

                </div>


                <div class="row mt-4">

                    <div class="col-md-6">

                        <h4>
                            Total :
                            <span class="badge badge-gradient-success p-2">
                                Rp <span id="total">0</span>
                            </span>
                        </h4>

                    </div>


                    <div class="col-md-6 text-end">

                        <button id="btnBayar" class="btn btn-gradient-success me-2">

                            <span id="textBayarAxios">
                                <i class="mdi mdi-cash"></i> Bayar (Axios)
                            </span>

                            <span id="spinnerBayarAxios"
                                class="spinner-border spinner-border-sm ms-2"
                                style="display:none;"></span>

                        </button>


                        <button id="btnBayarAjax" class="btn btn-gradient-primary">

                            <span id="textBayarAjax">
                                <i class="mdi mdi-cash"></i> Bayar (Ajax)
                            </span>

                            <span id="spinnerBayarAjax"
                                class="spinner-border spinner-border-sm ms-2"
                                style="display:none;"></span>

                        </button>

                    </div>

                </div>

            </div>
        </div>

    </div>


    @endsection

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/pos.js') }}"></script>
    @endpush