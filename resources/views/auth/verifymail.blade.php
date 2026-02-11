@extends('layouts.general')
@section('title', 'Verify Email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 text-center">
            <div class="card border-0 shadow-lg rounded-4 p-4">
                <div class="card-body">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-envelope-check fs-1"></i>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Check your inbox</h2>
                    <p class="text-muted mb-4">
                        We've sent a verification link to your email address. 
                        Please click the link to activate your account.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success border-0 small shadow-sm mb-4">
                            A new verification link has been sent to your email!
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm hover-up">
                                Resend Email
                            </button>
                        </form>
                    </div>

                    <hr class="my-4 opacity-25">

                    <p class="small text-muted mb-0">
                        Didn't get the mail? Please check your spam folder.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection