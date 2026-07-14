@extends('layouts.general')
@section('title', 'Confirm Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-lock fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Confirm your password</h2>
                        <p class="text-muted">This is a secure area. Please confirm your password before continuing.</p>
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

                    <form action="{{ route('password.confirm') }}" method="POST">
                        @csrf

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control border-0 bg-light rounded-3" id="password" placeholder="Password" autocomplete="current-password" autofocus required>
                            <label for="password" class="text-muted">Password</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Confirm
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
