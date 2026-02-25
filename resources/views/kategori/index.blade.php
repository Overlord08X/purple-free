@extends('layouts.app')

@section('title', 'Data Kategori')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-success text-white me-2">
                    <i class="mdi mdi-format-list-bulleted"></i>
                </span>
                Data Kategori
            </h3>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tambah Kategori</h4>
                        {{-- FORM TAMBAH --}}
                        <form action="{{ route('kategori.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="nama_kategori" class="form-control" placeholder="Nama Kategori" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-gradient-success btn-block">
                                        Tambah
                                    </button>
                                </div>
                            </div>
                        </form>

                        <h4 class="card-title mt-4">Daftar Kategori</h4>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>ID</th>
                                        <th>Nama Kategori</th>
                                        <th>Jumlah Buku</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kategoris as $kategori)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kategori->idkategori }}</td>
                                        <td>{{ $kategori->nama_kategori }}</td>
                                        <td>
                                            <label class="badge badge-gradient-primary">
                                                {{ $kategori->bukus->count() }} Buku
                                            </label>
                                        </td>
                                        <td>
                                            <form action="{{ route('kategori.destroy', $kategori->idkategori) }}" method="POST" onsubmit="return confirm('Yakin hapus kategori?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Data tidak tersedia
                                        </td>
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