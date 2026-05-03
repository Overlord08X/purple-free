@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pemesanan Kantin</h1>

    <div class="row">
        <div class="col-md-6">
            <label for="vendor">Pilih Vendor:</label>
            <select id="vendor" class="form-control">
                <option value="">-- Pilih Vendor --</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('project.vendor') }}" class="btn btn-outline-primary">Kelola Vendor & Menu</a>
    </div>

    <div id="menu-section" style="display: none;">
        <h3>Menu</h3>
        <div id="menu-list" class="row"></div>
    </div>

    <div id="cart-section" style="display: none;">
        <h3>Keranjang</h3>
        <form id="order-form" action="{{ route('pesanan.store') }}" method="POST">
            @csrf
            <input type="hidden" name="vendor_id" id="vendor_id">
            <div id="cart-items"></div>
            <div class="form-group">
                <label>Metode Pembayaran:</label>
                <select name="metode_bayar" class="form-control">
                    <option value="1">Virtual Account</option>
                    <option value="2">QRIS</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Pesan</button>
        </form>
    </div>


<script>
let cart = [];

$('#vendor').change(function() {
    const vendorId = $(this).val();
    if (vendorId) {
        $.get('/pesanan/menus/' + vendorId, function(data) {
            $('#menu-list').empty();
            data.forEach(menu => {
                $('#menu-list').append(`
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>${menu.nama_menu}</h5>
                                <p>Rp ${menu.harga}</p>
                                <button class="btn btn-success add-to-cart" data-id="${menu.idmenu}" data-name="${menu.nama_menu}" data-price="${menu.harga}">Tambah</button>
                            </div>
                        </div>
                    </div>
                `);
            });
            $('#menu-section').show();
        });
    } else {
        $('#menu-section').hide();
    }
});

$(document).on('click', '.add-to-cart', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const price = $(this).data('price');

    const existing = cart.find(item => item.id == id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id, name, price, quantity: 1, catatan: '' });
    }
    updateCart();
});

function updateCart() {
    $('#cart-items').empty();
    $('#vendor_id').val($('#vendor').val());
    cart.forEach((item, index) => {
        $('#cart-items').append(`
            <div class="card mb-2">
                <div class="card-body">
                    <h6>${item.name}</h6>
                    <p>Rp ${item.price} x ${item.quantity} = Rp ${item.price * item.quantity}</p>
                    <input type="hidden" name="items[${index}][menu_id]" value="${item.id}">
                    <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="1" class="form-control mb-2">
                    <textarea name="items[${index}][catatan]" placeholder="Catatan" class="form-control">${item.catatan}</textarea>
                    <button class="btn btn-danger btn-sm remove-item" data-index="${index}">Hapus</button>
                </div>
            </div>
        `);
    });
    if (cart.length > 0) {
        $('#cart-section').show();
    } else {
        $('#cart-section').hide();
    }
}

$(document).on('click', '.remove-item', function() {
    const index = $(this).data('index');
    cart.splice(index, 1);
    updateCart();
});
</script>
@endsection