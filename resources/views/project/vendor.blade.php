@extends('layouts.app')

@section('title', 'Data Vendor')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-store"></i>
                </span>
                Data Vendor
            </h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tambah Vendor</h4>
                        <form action="{{ route('project.vendor.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama Vendor</label>
                                <input type="text" name="nama_vendor" class="form-control" placeholder="Contoh: Kantin 1" required>
                            </div>
                            <button type="submit" class="btn btn-gradient-primary">Simpan Vendor</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Daftar Vendor</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Vendor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                        <tr>
                                            <td>{{ $vendor->idvendor }}</td>
                                            <td>{{ $vendor->nama_vendor }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Belum ada vendor</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tambah Menu / Barang</h4>
                        <form action="{{ route('project.vendor.menu.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Pilih Vendor</label>
                                <select name="vendor_id" class="form-control" required>
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Menu / Barang</label>
                                <input type="text" name="nama_menu" class="form-control" placeholder="Contoh: Nasi Goreng" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" placeholder="Contoh: 15000" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gambar</label>
                                <input type="file" name="path_gambar" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-gradient-primary">Simpan Menu</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Daftar Menu / Barang</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Vendor</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menus as $menu)
                                        <tr>
                                            <td>{{ $menu->nama_menu }}</td>
                                            <td>Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                                            <td>{{ $menu->vendor->nama_vendor ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('project.vendor.menu.destroy', $menu->idmenu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada menu</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
