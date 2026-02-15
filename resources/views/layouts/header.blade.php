<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Purple Admin')</title>

  <!-- plugins:css -->
  <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">

  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">

  <!-- Layout styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

  @yield('style-page')
</head>
<body>
<div class="container-scroller">
