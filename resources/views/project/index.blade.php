@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endpush

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
                        {{-- FORM INPUT --}}
                        <form id="formBarang" class="mb-4">
                            <div class="row">
                                <div class="col-md-5">
                                    <label>Nama Barang</label>
                                    <input type="text" id="namaBarang" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Harga Barang</label>
                                    <input type="number" id="hargaBarang" class="form-control" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" id="btnSubmit" class="btn btn-gradient-primary w-100">
                                        Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                        <h4 class="card-title mt-4">Daftar Barang</h4>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tableBarang">
                                <thead>
                                    <tr>
                                        <th>ID Barang</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>ID Barang</label>
                        <input type="text" id="editId" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Nama Barang</label>
                        <input type="text" id="editNama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Harga Barang</label>
                        <input type="number" id="editHarga" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" id="btnDelete">
                        Hapus
                    </button>
                    <button class="btn btn-success" id="btnUpdate">
                        Ubah
                    </button>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets/js/crud.js') }}"></script>
    @endpush