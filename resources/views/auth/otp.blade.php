@extends('layouts.auth.app')

@section('content')
<div class="container text-center">
    <h3>Masukkan Kode OTP</h3>

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <input type="text" name="otp" maxlength="6" class="form-control text-center mb-3" placeholder="6 Digit OTP" required>
        <button type="submit" class="btn btn-primary">Verifikasi</button>
    </form>
</div>
@endsection