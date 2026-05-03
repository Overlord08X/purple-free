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

        <form method="POST" action="{{ route('tagharga.cetak') }}">
            @csrf

            <div class="card">
                <div class="card-body">

                    <ul class="nav nav-tabs mb-3" id="tagHargaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="barang-tab" data-bs-toggle="tab" data-bs-target="#barang-pane" type="button" role="tab">Barang</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu-pane" type="button" role="tab">Menu</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="tagHargaTabContent">
                        <div class="tab-pane fade show active" id="barang-pane" role="tabpanel">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="checkAllBarang">
                                        </th>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th width="15%">Qty Cetak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($barang as $b)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                name="items[barang_{{ $b->idbarang }}][id]"
                                                value="{{ $b->idbarang }}"
                                                class="check-item-barang">
                                            <input type="hidden" name="items[barang_{{ $b->idbarang }}][type]" value="barang">
                                        </td>
                                        <td>{{ $b->idbarang }}</td>
                                        <td>{{ $b->nama_barang }}</td>
                                        <td>Rp {{ number_format($b->harga_barang) }}</td>
                                        <td>
                                            <input type="number"
                                                name="items[barang_{{ $b->idbarang }}][qty]"
                                                class="form-control qty-input-barang"
                                                min="1"
                                                value="1"
                                                disabled>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="menu-pane" role="tabpanel">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="checkAllMenu">
                                        </th>
                                        <th>ID</th>
                                        <th>Nama Menu</th>
                                        <th>Vendor</th>
                                        <th>Harga</th>
                                        <th width="15%">Qty Cetak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menu as $m)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                name="items[menu_{{ $m->idmenu }}][id]"
                                                value="{{ $m->idmenu }}"
                                                class="check-item-menu">
                                            <input type="hidden" name="items[menu_{{ $m->idmenu }}][type]" value="menu">
                                        </td>
                                        <td>MNU{{ str_pad($m->idmenu, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $m->nama_menu }}</td>
                                        <td>{{ $m->vendor->nama_vendor ?? '-' }}</td>
                                        <td>Rp {{ number_format($m->harga) }}</td>
                                        <td>
                                            <input type="number"
                                                name="items[menu_{{ $m->idmenu }}][qty]"
                                                class="form-control qty-input-menu"
                                                min="1"
                                                value="1"
                                                disabled>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-3">
                            <label>Kolom (X)</label>
                            <input type="number" name="x" min="1" max="5"
                                class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Baris (Y)</label>
                            <input type="number" name="y" min="1" max="8"
                                class="form-control" required>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                Cetak PDF
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </form>

    </div>

    {{-- SCRIPT LANGSUNG DI SINI (ANTI GAGAL) --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let checkAllBarang = document.getElementById("checkAllBarang");
            let checkAllMenu = document.getElementById("checkAllMenu");
            let checkItemsBarang = document.querySelectorAll(".check-item-barang");
            let checkItemsMenu = document.querySelectorAll(".check-item-menu");

            function toggleQty(checkbox) {
                let row = checkbox.closest("tr");
                let qtyInput = row.querySelector("input[type='number']");

                if (checkbox.checked) {
                    qtyInput.disabled = false;
                } else {
                    qtyInput.disabled = true;
                    qtyInput.value = 1;
                }
            }

            // CHECK ALL BARANG
            checkAllBarang.addEventListener("change", function() {
                checkItemsBarang.forEach(function(item) {
                    item.checked = checkAllBarang.checked;
                    toggleQty(item);
                });
            });

            // CHECK ALL MENU
            checkAllMenu.addEventListener("change", function() {
                checkItemsMenu.forEach(function(item) {
                    item.checked = checkAllMenu.checked;
                    toggleQty(item);
                });
            });

            // PER ITEM
            checkItemsBarang.forEach(function(item) {
                item.addEventListener("change", function() {
                    toggleQty(this);

                    // Sync checkAll
                    let allChecked = Array.from(checkItemsBarang).every(i => i.checked);
                    checkAllBarang.checked = allChecked;
                });
            });

            checkItemsMenu.forEach(function(item) {
                item.addEventListener("change", function() {
                    toggleQty(this);

                    let allChecked = Array.from(checkItemsMenu).every(i => i.checked);
                    checkAllMenu.checked = allChecked;
                });
            });

        });
    </script>

    @endsection