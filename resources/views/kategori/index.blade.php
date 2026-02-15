@extends('layouts.app')

@section('title', 'Data Kategori')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white me-2">
            <i class="mdi mdi-format-list-bulleted"></i>
        </span>
        Data Kategori
    </h3>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Daftar Kategori</h4>
                <p class="card-description">
                    Menampilkan seluruh kategori beserta jumlah buku
                </p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>ID Kategori</th>
                                <th>Nama Kategori</th>
                                <th width="20%">Jumlah Buku</th>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
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
