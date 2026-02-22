@extends('layouts.app')

@section('title', 'Data Buku')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-book-open-page-variant"></i>
        </span>
        Data Buku
    </h3>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Tambah Buku</h4>

                {{-- FORM TAMBAH --}}
                <form action="{{ route('buku.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row">

                        <div class="col-md-3">
                            <select name="idkategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->idkategori }}">
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="text" name="kode" class="form-control" placeholder="Kode Buku" required>
                        </div>

                        <div class="col-md-3">
                            <input type="text" name="judul" class="form-control" placeholder="Judul Buku" required>
                        </div>

                        <div class="col-md-2">
                            <input type="text" name="pengarang" class="form-control" placeholder="Pengarang" required>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-gradient-primary btn-block">
                                Tambah
                            </button>
                        </div>

                    </div>
                </form>

                <h4 class="card-title mt-4">Daftar Buku</h4>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th>Kategori</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bukus as $buku)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $buku->idbuku }}</td>
                                    <td>{{ $buku->kode }}</td>
                                    <td>{{ $buku->judul }}</td>
                                    <td>{{ $buku->pengarang }}</td>
                                    <td>
                                        <label class="badge badge-gradient-info">
                                            {{ $buku->kategori->nama_kategori ?? '-' }}
                                        </label>
                                    </td>
                                    <td>
                                        <form action="{{ route('buku.destroy', $buku->idbuku) }}" method="POST" onsubmit="return confirm('Yakin hapus buku?')">
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
                                    <td colspan="7" class="text-center text-muted">
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

@endsection
