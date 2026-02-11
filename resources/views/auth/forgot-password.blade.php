@extends('layouts.general')
@section('title', 'Forgot Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-key-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Forgot Password?</h2>
                        <p class="text-muted">No worries, we'll send you a link to get back into your account.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success border-0 small shadow-sm mb-4 text-center">
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

                    <form action="{{ route('password.request') }}" method="POST">
                        @csrf
                        <div class="form-floating mb-4">
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3" id="email" placeholder="name@example.com" required autofocus value="{{ old('email') }}">
                            <label for="email" class="text-muted">Email address</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Send Reset Link
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">Remembered your password? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection