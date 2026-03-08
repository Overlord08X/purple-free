@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-package-variant"></i>
                </span>
                Data Barang
            </h3>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">

                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">Tambah Barang</h4>
                        <form id="formTambah" class="mb-4">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="nama_barang" class="form-control" placeholder="Nama Barang" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="harga_barang" class="form-control" placeholder="Harga Barang" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="btnTambahBarang" class="btn btn-gradient-primary btn-block">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <h4 class="card-title mt-4">Daftar Barang</h4>
                        <div class="table-responsive">
                            <table id="tableBarang" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($barangs as $index => $barang)
                                    <tr data-id="{{ $barang->idbarang }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $barang->idbarang }}</td>
                                        <td>{{ $barang->nama_barang }}</td>
                                        <td>{{ number_format($barang->harga_barang,0,',','.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary btn-ubah">Ubah</button>
                                            <button class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal untuk Edit --}}
    <div class="modal fade" id="modalEditBarang" tabindex="-1">
        <div class="modal-dialog">
            <form id="formEditBarang">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_idbarang">
                        <div class="mb-3">
                            <label>Nama Barang</label>
                            <input type="text" id="edit_nama_barang" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Harga Barang</label>
                            <input type="number" id="edit_harga_barang" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btnUpdateBarang" class="btn btn-primary">Ubah</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endsection

    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        #tableBarang tbody tr:hover {
            cursor: pointer;
            background-color: #eef;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets/js/barang.js') }}"></script>
    @endpush