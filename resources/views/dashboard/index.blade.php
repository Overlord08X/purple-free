@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">

    <div class="page-header">
      <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
          <i class="mdi mdi-home"></i>
        </span>
        Dashboard
      </h3>
    </div>

    <div class="row">

      {{-- TOTAL BUKU --}}
      <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-danger card-img-holder text-white">
          <div class="card-body">
            <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
            <h4 class="font-weight-normal mb-3">
              Total Buku
              <i class="mdi mdi-book-open-page-variant mdi-24px float-end"></i>
            </h4>
            <h2 class="mb-5">{{ $totalBuku }}</h2>
            <h6 class="card-text">Jumlah buku dalam sistem</h6>
          </div>
        </div>
      </div>

      {{-- TOTAL KATEGORI --}}
      <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-info card-img-holder text-white">
          <div class="card-body">
            <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
            <h4 class="font-weight-normal mb-3">
              Total Kategori
              <i class="mdi mdi-format-list-bulleted mdi-24px float-end"></i>
            </h4>
            <h2 class="mb-5">{{ $totalKategori }}</h2>
            <h6 class="card-text">Jumlah kategori dalam sistem</h6>
          </div>
        </div>
      </div>

      {{-- TOTAL BARANG --}}
      <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-success card-img-holder text-white">
          <div class="card-body">
            <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
            <h4 class="font-weight-normal mb-3">
              Total Barang
              <i class="mdi mdi-tag mdi-24px float-end"></i>
            </h4>
            <h2 class="mb-5">{{ $totalBarang }}</h2>
            <h6 class="card-text">Jumlah barang dalam sistem</h6>
          </div>
        </div>
      </div>

    </div>

  </div>
  @endsection