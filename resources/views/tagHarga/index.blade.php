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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="checkAll">
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
                                        name="items[{{ $b->idbarang }}][id]"
                                        value="{{ $b->idbarang }}"
                                        class="check-item">
                                </td>
                                <td>{{ $b->idbarang }}</td>
                                <td>{{ $b->nama_barang }}</td>
                                <td>Rp {{ number_format($b->harga_barang) }}</td>
                                <td>
                                    <input type="number"
                                        name="items[{{ $b->idbarang }}][qty]"
                                        class="form-control qty-input"
                                        min="1"
                                        value="1"
                                        disabled>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

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

            let checkAll = document.getElementById("checkAll");
            let checkItems = document.querySelectorAll(".check-item");

            function toggleQty(checkbox) {
                let row = checkbox.closest("tr");
                let qtyInput = row.querySelector(".qty-input");

                if (checkbox.checked) {
                    qtyInput.disabled = false;
                } else {
                    qtyInput.disabled = true;
                    qtyInput.value = 1;
                }
            }

            // CHECK ALL
            checkAll.addEventListener("change", function() {
                checkItems.forEach(function(item) {
                    item.checked = checkAll.checked;
                    toggleQty(item);
                });
            });

            // PER ITEM
            checkItems.forEach(function(item) {
                item.addEventListener("change", function() {
                    toggleQty(this);

                    // Sync checkAll
                    let allChecked = Array.from(checkItems).every(i => i.checked);
                    checkAll.checked = allChecked;
                });
            });

        });
    </script>

    @endsection