@extends('layouts.userbase')
@section('title', 'Account Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-5">
            <h1 class="display-6 fw-bold">Settings</h1>
            <p class="text-muted">Manage your profile, security, and API access.</p>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-person-badge fs-3 me-3 text-primary"></i>
                    <h5 class="card-title mb-0 fw-bold">Profile Details</h5>
                </div>

                @if(session('message') && !session('apikey'))
                    <div class="alert alert-success border-0 shadow-sm mb-3">
                        <i class="bi bi-check-circle me-2"></i> {{ session('message') }}
                    </div>
                @endif

                <form action="{{ route('account.settings.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">NAME</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $loggedUser->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">EMAIL ADDRESS</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $loggedUser->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-key fs-3 me-3 text-warning"></i>
                    <h5 class="card-title mb-0 fw-bold">API Access</h5>
                </div>

                @if(session('apikey'))
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div>
                                <strong class="d-block">Important: Your New API Key</strong>
                                <p class="small mb-2 text-dark">Copy this key now. For security reasons, we won't show it to you again!</p>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace border-0 shadow-sm" id="apiKeyDisplay" value="{{ session('apikey') }}" readonly>
                                    <button class="btn btn-dark" id="copyKeyBtn" onclick="copyKey()">
                                        <i class="bi bi-clipboard me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('account.settings.sxcu') }}" class="btn btn-sm btn-dark">
                                        <i class="bi bi-download me-1"></i> Download ShareX Config (.sxcu)
                                    </a>
                                    <a href="{{ route('account.settings.bash') }}" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-terminal me-1"></i> Bash Script (.sh)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($loggedUser->tokens->isNotEmpty())
                    <div class="p-3 rounded d-flex justify-content-between align-items-center border">
                        <div>
                            <span class="badge bg-success mb-1">Active Key Found</span>
                            <div class="small text-muted">Last used: {{ $loggedUser->tokens->first()->last_used_at?->diffForHumans() ?? 'Never' }}</div>
                        </div>
                        <form action="{{ route('account.settings.deleteapikey') }}" method="POST" onsubmit="return confirm('Delete API Key? All connected apps will stop working!')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash3 me-1"></i> Revoke Key
                            </button>
                        </form>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0 small">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-3 small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        A ready-to-use ShareX config and upload script can only include your key at the
                        moment it is generated. Regenerate your key to download them again.
                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted small">No API key generated yet. Generate one to use ShareX.</p>
                        <form action="{{ route('account.settings.generateapikey') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning shadow-sm fw-bold">
                                <i class="bi bi-plus-circle me-1"></i> Generate API Key
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-shield-lock fs-3 me-3 text-danger"></i>
                    <h5 class="card-title mb-0 fw-bold">Update Password</h5>
                </div>

                @if(session('password_success'))
                    <div class="alert alert-success border-0 shadow-sm mb-3">
                        <i class="bi bi-check-circle me-2"></i> {{ session('password_success') }}
                    </div>
                @endif

                <form action="{{ route('account.settings.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted text-uppercase">New Password</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required>
                            @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted text-uppercase">Confirm New Password</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-outline-danger px-4">Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-shield-check fs-3 me-3 text-success"></i>
                    <h5 class="card-title mb-0 fw-bold">Two-Factor Authentication</h5>
                </div>

                @if($loggedUser->hasEnabledTwoFactorAuthentication())
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Two-factor authentication is <strong class="mx-1">enabled</strong> on your account.
                    </div>

                    <p class="text-muted small">
                        Scan the QR code with your authenticator app (or enter the setup key), and keep your
                        recovery codes somewhere safe - each can be used once if you lose access to your device.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#twoFactorQr">
                            <i class="bi bi-qr-code me-1"></i> Show setup QR code
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#recoveryCodes">
                            <i class="bi bi-key me-1"></i> Show recovery codes
                        </button>
                    </div>

                    <div class="collapse" id="twoFactorQr">
                        <div class="p-3 mb-3 border rounded bg-light text-center">
                            {!! $loggedUser->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>

                    <div class="collapse" id="recoveryCodes">
                        <div class="p-3 mb-3 border rounded bg-light font-monospace small">
                            @foreach(json_decode(decrypt($loggedUser->two_factor_recovery_codes), true) as $code)
                                <div>{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('two-factor.recovery-codes') }}" method="POST" class="js-confirm-password">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-repeat me-1"></i> Regenerate recovery codes
                            </button>
                        </form>
                        <form action="{{ route('two-factor.disable') }}" method="POST" class="js-confirm-password"
                              onsubmit="return confirm('Disable two-factor authentication?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-shield-x me-1"></i> Disable 2FA
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted small">
                        Add an extra layer of security. Once enabled, you'll enter a code from your authenticator
                        app each time you sign in.
                    </p>
                    <form action="{{ route('two-factor.enable') }}" method="POST" class="js-confirm-password">
                        @csrf
                        <button type="submit" class="btn btn-success shadow-sm fw-bold">
                            <i class="bi bi-shield-lock me-1"></i> Enable Two-Factor Authentication
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-dark text-white p-2">
            <div class="card-body">
                <h6 class="text-uppercase small fw-bold text-muted mb-4">Account Stats</h6>
                    <div class="row text-center">
                        <div class="col-4 border-end border-secondary">
                            <div class="h4 mb-0 fw-bold">{{ $loggedUser->created_at->format('M Y') }}</div>
                            <div class="extra-small text-muted">Member Since</div>
                        </div>
                        <div class="col-4 border-end border-secondary">
                            {{-- Using direct variable from controller --}}
                            <div class="h4 mb-0 fw-bold">{{ $totalUploads }}</div>
                            <div class="extra-small text-muted">Total Uploads</div>
                        </div>
                        <div class="col-4">
                            {{-- Using pre-calculated MB from controller --}}
                            <div class="h4 mb-0 fw-bold">{{ $totalStorageMb }} MB</div>
                            <div class="extra-small text-muted">Used Storage</div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

{{-- Password confirmation modal for sensitive two-factor actions --}}
<div class="modal fade" id="confirmPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock me-2"></i>Confirm your password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">For your security, please confirm your password to continue.</p>
                <input type="password" class="form-control" id="confirmPasswordInput" autocomplete="current-password" placeholder="Password">
                <div class="invalid-feedback d-block" id="confirmPasswordError"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPasswordSubmit">Confirm</button>
            </div>
        </div>
    </div>
</div>

<style>
    .font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important; }
    .extra-small { font-size: 0.7rem; }
</style>

<script>
    function copyKey() {
        const keyInput = document.getElementById("apiKeyDisplay");
        keyInput.select();
        navigator.clipboard.writeText(keyInput.value).then(() => {
            const btn = document.getElementById("copyKeyBtn");
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }

    // Sensitive two-factor forms require a confirmed password within Fortify's window.
    // Confirm it inline via a modal, then submit the original form - avoiding the
    // clunky redirect-and-retry flow of the password.confirm middleware.
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
        const modalEl = document.getElementById('confirmPasswordModal');
        const modal = new bootstrap.Modal(modalEl);
        const input = document.getElementById('confirmPasswordInput');
        const errorEl = document.getElementById('confirmPasswordError');
        const submitBtn = document.getElementById('confirmPasswordSubmit');
        let pendingForm = null;

        document.querySelectorAll('form.js-confirm-password').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === 'true') return; // already confirmed, let it through
                e.preventDefault();
                fetch('{{ route('password.confirmation') }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.confirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        } else {
                            pendingForm = form;
                            errorEl.textContent = '';
                            input.value = '';
                            modal.show();
                        }
                    });
            });
        });

        modalEl.addEventListener('shown.bs.modal', () => input.focus());
        input.addEventListener('keydown', e => { if (e.key === 'Enter') submitBtn.click(); });

        submitBtn.addEventListener('click', function () {
            errorEl.textContent = '';
            fetch('{{ route('password.confirm') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ password: input.value }),
            }).then(r => {
                if (r.ok) {
                    modal.hide();
                    if (pendingForm) {
                        pendingForm.dataset.confirmed = 'true';
                        pendingForm.submit();
                    }
                } else {
                    errorEl.textContent = 'The password you entered is incorrect.';
                }
            });
        });
    })();
</script>
@endsection