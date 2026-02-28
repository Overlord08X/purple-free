@extends('layouts.app')

@section('title', 'Tag Harga')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-printer"></i>
                </span>
                Cetak Tag Harga
            </h3>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">Daftar Barang</h4>

                        <form method="POST" action="{{ route('tagharga.cetak') }}">
                            @csrf

                            <div class="table-responsive">
                                <table id="tableBarang" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">Pilih</th>
                                            <th>ID</th>
                                            <th>Nama Barang</th>
                                            <th>Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($barang as $b)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="id[]" value="{{ $b->idbarang }}">
                                            </td>
                                            <td>{{ $b->idbarang }}</td>
                                            <td>{{ $b->nama_barang }}</td>
                                            <td>
                                                <label class="badge badge-gradient-success">
                                                    Rp {{ number_format($b->harga_barang) }}
                                                </label>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Data tidak tersedia
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <h4 class="card-title mt-4">Posisi Mulai Cetak</h4>

                            <div class="row">
                                <div class="col-md-3">
                                    <label>Kolom (X)</label>
                                    <input type="number" name="x" min="1" max="5"
                                        class="form-control" placeholder="1 - 5" required>
                                </div>

                                <div class="col-md-3">
                                    <label>Baris (Y)</label>
                                    <input type="number" name="y" min="1" max="8"
                                        class="form-control" placeholder="1 - 8" required>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-gradient-primary btn-block">
                                        <i class="mdi mdi-printer"></i> Cetak PDF
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tableBarang').DataTable();
    });
</script>
@endpush