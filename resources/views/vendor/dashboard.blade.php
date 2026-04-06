@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vendor Dashboard</h1>

    <div class="row">
        <div class="col-md-6">
            <h3>Tambah Menu</h3>
            <form action="{{ route('vendor.menu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Gambar</label>
                    <input type="file" name="path_gambar" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
        </div>

        <div class="col-md-6">
            <h3>Menu Saya</h3>
            @foreach($menus as $menu)
                <div class="card mb-2">
                    <div class="card-body">
                        <h5>{{ $menu->nama_menu }}</h5>
                        <p>Rp {{ number_format($menu->harga) }}</p>
                        <form action="{{ route('vendor.menu.destroy', $menu->idmenu) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <h3>Pesanan Lunas</h3>
    @foreach($pesananLunas as $pesanan)
        <div class="card mb-2">
            <div class="card-body">
                <h5>Pesanan #{{ $pesanan->idpesanan }}</h5>
                <p>Nama: {{ $pesanan->nama }}</p>
                <p>Total: Rp {{ number_format($pesanan->total) }}</p>
                <ul>
                    @foreach($pesanan->detail as $detail)
                        <li>{{ $detail->menu->nama_menu }} x {{ $detail->jumlah }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@endsection