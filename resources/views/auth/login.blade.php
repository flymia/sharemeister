@extends('layouts.general')
@section('title', 'Login')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Welcome back</h2>
                        <p class="text-muted">Access your screenshot dashboard</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger border-0 small shadow-sm mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3" id="email" placeholder="name@example.com" required autofocus value="{{ old('email') }}">
                            <label for="email" class="text-muted">Email address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control border-0 bg-light rounded-3" id="password" placeholder="Password" required>
                            <label for="password" class="text-muted">Password</label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small text-muted" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small text-primary fw-bold text-decoration-none">Forgot password?</a>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Sign In
                            </button>
                        </div>
                    </form>

                    @if (Route::has('register') && env('ALLOW_REGISTRATION', true))
                        <div class="text-center">
                            <p class="text-muted small">New to the platform? <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Create account</a></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 { border-radius: 1rem !important; }
    .bg-light { background-color: #f8f9fa !important; }
    .hover-up { transition: transform 0.2s ease; }
    .hover-up:hover { transform: translateY(-2px); }
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
</style>
@endsection