@extends('layouts.auth.app')

@section('content')

<form method="POST" action="{{ route('login') }}" class="pt-3">
    @csrf

    {{-- EMAIL --}}
    <div class="form-group">
        <input id="email"
            type="email"
            class="form-control form-control-lg @error('email') is-invalid @enderror"
            name="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            autofocus
            placeholder="Email">

        @error('email')
        <span class="invalid-feedback">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- PASSWORD --}}
    <div class="form-group">
        <input id="password"
            type="password"
            class="form-control form-control-lg @error('password') is-invalid @enderror"
            name="password"
            required
            autocomplete="current-password"
            placeholder="Password">

        @error('password')
        <span class="invalid-feedback">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- LOGIN BUTTON --}}
    <div class="mt-3 d-grid gap-2">
        <button type="submit"
            class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">SIGN IN
        </button>
    </div>

    {{-- REMEMBER + FORGOT --}}
    <div class="my-2 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input"
                type="checkbox"
                name="remember"
                id="remember"
                {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label text-muted" for="remember">
                Keep me signed in
            </label>
        </div>

        @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}"
            class="text-primary text-decoration-none">
            Forgot password?
        </a>
        @endif
    </div>

    {{-- SOCIAL BUTTON LOGIN GOOGLE --}}
    <div class="mb-3 d-grid gap-2">
        <a href="{{ route('google.login') }}"
            class="btn btn-google d-flex align-items-center justify-content-center"
            style="background:#DB4437; color:white;">
            <i class="mdi mdi-google me-2"></i>
            Sign in with Google
        </a>
    </div>


    {{-- REGISTER LINK --}}
    <div class="text-center mt-4">
        <small class="text-muted">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-primary">
                Create
            </a>
        </small>
    </div>

</form>
</div>
@endsection