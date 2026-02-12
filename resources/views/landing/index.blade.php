@extends('layouts.general')
@section('title', 'Sharemeister Instance')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center text-center py-5">
        <div class="col-lg-8">
            <div class="mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-cpu-fill fs-1"></i>
                </div>
            </div>
            <h1 class="display-3 fw-bold mb-3">Sharemeister</h1>
            <p class="lead text-muted mb-5 px-md-5">
                Your private, high-performance screenshot server.

            <div class="d-flex justify-content-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-3 shadow hover-up">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-3 shadow hover-up">
                        Sign In
                    </a>

                    @if (Route::has('register') && env('ALLOW_REGISTRATION', true))
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg px-5 py-3 fw-bold rounded-3 hover-up">
                        Register
                    </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="row g-4 py-5 mt-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-primary mb-3"><i class="bi bi-shield-lock fs-2"></i></div>
                <h5 class="fw-bold">Self-Hosted Privacy</h5>
                <p class="text-muted small mb-0">Your screenshots stay on your hardware. No third-party clouds, no data mining—just your instance, your rules.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-primary mb-3"><i class="bi bi-lightning-charge fs-2"></i></div>
                <h5 class="fw-bold">High Performance</h5>
                <p class="text-muted small mb-0">Optimized for speed. Fast uploads via API and instant retrieval via a clean, light-weight backend.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-primary mb-3"><i class="bi bi-terminal fs-2"></i></div>
                <h5 class="fw-bold">Admin Controlled</h5>
                <p class="text-muted small mb-0">Full CLI access via Artisan commands. Manage storage quotas and user accounts directly from your terminal.</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 pt-5">
        <p class="text-muted small">
            This <strong>Sharemeister instance</strong> is running in your environment. <br>
            Need help? Visit the 
            <a href="https://github.com/flymia/sharemeister" class="text-primary text-decoration-none fw-bold" target="_blank">
                <i class="bi bi-github me-1"></i>Sharemeister Wiki
            </a>.
        </p>
    </div>
</div>

<style>
    .hover-up { transition: transform 0.2s ease; }
    .hover-up:hover { transform: translateY(-3px); }
    .rounded-4 { border-radius: 1.25rem !important; }
    body { background: radial-gradient(circle at top right, rgba(13, 110, 253, 0.03) 0%, rgba(255, 255, 255, 1) 50%); }
</style>
@endsection