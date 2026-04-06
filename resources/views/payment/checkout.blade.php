@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-success text-white me-2">
                    <i class="mdi mdi-cart"></i>
                </span>
                Pilih Barang
            </h3>
        </div>

        <form method="POST" action="/checkout/proses">
            @csrf

            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Daftar Barang</h4>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th width="20%">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barangs as $barang)
                            <tr>
                                <td>{{ $barang->nama_barang }}</td>
                                <td>{{ number_format($barang->harga_barang,0,',','.') }}</td>
                                <td>
                                    <input type="number"
                                        name="items[{{ $barang->idbarang }}]"
                                        class="form-control"
                                        min="0"
                                        value="0">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-gradient-primary mt-3">
                        Lanjutkan Pembayaran
                    </button>

                </div>
            </div>

        </form>

    </div>

@endsection