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

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Daftar Buku</h4>
                <p class="card-description">
                    Menampilkan seluruh data buku beserta kategori
                </p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>ID Buku</th>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th>Kategori</th>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="text-muted py-3">
                                            Data tidak tersedia
                                        </div>
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
