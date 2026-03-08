@extends('layouts.app')

@section('title', 'Data Barang (Datatables)')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-package-variant"></i>
                </span>
                Data Barang (Datatables)
            </h3>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">Daftar Barang</h4>

                        <div class="table-responsive">
                            <table id="myTable" class="table table-hover table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- contoh data dummy --}}
                                    <tr>
                                        <td>1</td>
                                        <td>Laptop</td>
                                        <td>15000000</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Mouse</td>
                                        <td>200000</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Keyboard</td>
                                        <td>500000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    @endsection

    {{-- Masukkan CSS DataTables --}}
    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    {{-- Masukkan JS DataTables --}}
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                ordering: true,
                responsive: true
            });
        });
    </script>
    @endpush