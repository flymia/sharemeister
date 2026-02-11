@extends('layouts.general')
@section('title', 'Reset Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Secure Account</h2>
                        <p class="text-muted">Set your new access credentials</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success border-0 small shadow-sm mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-0 small shadow-sm mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3" id="email" placeholder="name@example.com" required value="{{ old('email', $request->email) }}">
                            <label for="email" class="text-muted">Email address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control border-0 bg-light rounded-3" id="password" placeholder="New Password" required autofocus autocomplete="new-password">
                            <label for="password" class="text-muted">New Password</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-3" id="password_confirmation" placeholder="Confirm New Password" required>
                            <label for="password_confirmation" class="text-muted">Confirm New Password</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Update Password
                            </button>
                        </div>
                    </form>
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