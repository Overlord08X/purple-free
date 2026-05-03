@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Kantin')

@section('content')

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> <span class="page-title-icon bg-gradient-primary text-white me-2"> <i class="mdi mdi-cart"></i> </span> Kantin </h3>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Transaksi Penjualan</h4>

                <ul class="nav nav-tabs mb-3" id="kantinTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="barang-tab" data-bs-toggle="tab" data-bs-target="#barang-pane" type="button" role="tab">Barang</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu-pane" type="button" role="tab">Menu Vendor</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="barang-pane" role="tabpanel">
                        <h5>Daftar Barang</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover" id="tableBarang">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($barangs as $barang)
                                    <tr>
                                        <td>{{ $barang->idbarang }}</td>
                                        <td>{{ $barang->nama_barang }}</td>
                                        <td>Rp {{ number_format($barang->harga_barang, 0, ',', '.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary pilih-barang" data-type="barang" data-code="{{ $barang->idbarang }}" data-id="{{ $barang->idbarang }}" data-name="{{ $barang->nama_barang }}" data-price="{{ $barang->harga_barang }}">Pilih</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="menu-pane" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Filter Vendor</label>
                                <select id="filterVendorMenu" class="form-select">
                                    <option value="all">Semua Vendor</option>
                                    @foreach($menus->pluck('vendor')->filter()->unique('idvendor') as $vendor)
                                        <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <div class="alert alert-info mb-0 w-100">
                                    Scan barcode menu dari tag harga atau pilih langsung dari daftar vendor.
                                </div>
                            </div>
                        </div>
                        <h5>Daftar Menu</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover" id="tableMenu">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Vendor</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menus as $menu)
                                    <tr data-vendor="{{ $menu->idvendor }}">
                                        <td>MNU{{ str_pad($menu->idmenu, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $menu->nama_menu }}</td>
                                        <td>{{ $menu->vendor->nama_vendor ?? '-' }}</td>
                                        <td>Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success pilih-menu" data-type="menu" data-code="MNU{{ str_pad($menu->idmenu, 5, '0', STR_PAD_LEFT) }}" data-id="{{ $menu->idmenu }}" data-name="{{ $menu->nama_menu }}" data-price="{{ $menu->harga }}" data-vendor="{{ $menu->vendor->nama_vendor ?? '-' }}">Pilih</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 mt-4">
                    <div class="col-md-2"> <input type="text" id="idBarang" class="form-control" placeholder="Kode Barang / Menu"> </div>
                    <div class="col-md-3"> <input type="text" id="namaBarang" class="form-control" readonly placeholder="Nama Barang / Menu"> </div>
                    <div class="col-md-2"> <input type="text" id="hargaBarang" class="form-control" readonly placeholder="Harga"> </div>
                    <div class="col-md-2"> <input type="number" id="jumlah" class="form-control" value="1"> </div>
                    <div class="col-md-2"> <button type="button" id="btnTambah" class="btn btn-gradient-primary btn-block" disabled> <span id="textTambah">Tambahkan</span> <span id="spinnerTambah" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span> </button> </div>
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
                        <h4> Total : <span class="badge badge-gradient-success p-2"> Rp <span id="total">0</span> </span> </h4>
                    </div>
                    <div class="col-md-6 text-end"> <button type="button" id="btnBayar" class="btn btn-gradient-primary me-2"> <span id="textBayarAxios"> <i class="mdi mdi-cash"></i> Lanjutkan Pembayaran </span> <span id="spinnerBayarAxios" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span> </button> </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/kantin.js') }}"></script>
@endpush