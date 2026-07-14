@extends('layouts.general')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Two-factor authentication</h2>
                        <p class="text-muted" id="tfa-hint">Enter the code from your authenticator app.</p>
                        <p class="text-muted d-none" id="recovery-hint">Enter one of your emergency recovery codes.</p>
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

                    <form action="{{ route('two-factor.login') }}" method="POST">
                        @csrf

                        <div id="code-wrapper">
                            <div class="form-floating mb-3">
                                <input type="text" name="code" class="form-control border-0 bg-light rounded-3" id="code" placeholder="123456" inputmode="numeric" autocomplete="one-time-code" autofocus>
                                <label for="code" class="text-muted">Authentication code</label>
                            </div>
                        </div>

                        <div id="recovery-wrapper" class="d-none">
                            <div class="form-floating mb-3">
                                <input type="text" name="recovery_code" class="form-control border-0 bg-light rounded-3" id="recovery_code" placeholder="Recovery code" autocomplete="one-time-code">
                                <label for="recovery_code" class="text-muted">Recovery code</label>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm hover-up">
                                Verify
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <button type="button" class="btn btn-link small text-decoration-none" id="toggle-recovery">Use a recovery code instead</button>
                    </div>
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

<script>
    document.getElementById('toggle-recovery').addEventListener('click', function () {
        const codeWrapper = document.getElementById('code-wrapper');
        const recoveryWrapper = document.getElementById('recovery-wrapper');
        const useRecovery = codeWrapper.classList.toggle('d-none');
        recoveryWrapper.classList.toggle('d-none');

        // Disable the hidden field so its empty value is not submitted.
        document.getElementById('code').disabled = useRecovery;
        document.getElementById('recovery_code').disabled = !useRecovery;

        document.getElementById('tfa-hint').classList.toggle('d-none', useRecovery);
        document.getElementById('recovery-hint').classList.toggle('d-none', !useRecovery);
        this.textContent = useRecovery ? 'Use an authentication code instead' : 'Use a recovery code instead';

        (useRecovery ? document.getElementById('recovery_code') : document.getElementById('code')).focus();
    });

    // Recovery field starts disabled so only one code is ever submitted.
    document.getElementById('recovery_code').disabled = true;
</script>
@endsection
