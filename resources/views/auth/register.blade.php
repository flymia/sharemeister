@extends('layouts.general')
@section('title', 'Create Account')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-plus-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Join this Sharemeister instance</h2>
                        <p class="text-muted">Set up your screenshot account</p>
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

                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        
                        <div class="form-floating mb-3">
                            <input type="text" name="name" class="form-control border-0 bg-light rounded-3" id="name" placeholder="John Doe" required autofocus value="{{ old('name') }}">
                            <label for="name" class="text-muted">Full Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3" id="email" placeholder="name@example.com" required value="{{ old('email') }}">
                            <label for="email" class="text-muted">Email address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control border-0 bg-light rounded-3" id="password" placeholder="Password" required autocomplete="new-password">
                            <label for="password" class="text-muted">Password</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-3" id="password_confirmation" placeholder="Confirm Password" required>
                            <label for="password_confirmation" class="text-muted">Confirm Password</label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="text-muted small">Already have an account? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Back to Landing Page
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .hover-up {
        transition: transform 0.2s ease-in-out;
    }
    .hover-up:hover {
        transform: translateY(-2px);
    }
    .rounded-4 { border-radius: 1rem !important; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endsection