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
                                    <button class="btn btn-dark" onclick="copyKey()">
                                        <i class="bi bi-clipboard me-1"></i> Copy
                                    </button>
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

                    <div class="mt-3">
                        <a href="{{ route('account.settings.sxcu') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i> Download ShareX Config (.sxcu)
                        </a>
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

        <div class="card shadow-sm border-0 bg-dark text-white p-2">
            <div class="card-body">
                <h6 class="text-uppercase small fw-bold text-muted mb-4">Account Stats</h6>
                <div class="row text-center">
                    <div class="col-4 border-end border-secondary">
                        <div class="h4 mb-0 fw-bold">{{ $loggedUser->created_at->format('M Y') }}</div>
                        <div class="extra-small text-muted">Member Since</div>
                    </div>
                    <div class="col-4 border-end border-secondary">
                        <div class="h4 mb-0 fw-bold">{{ $loggedUser->screenshots()->count() }}</div>
                        <div class="extra-small text-muted">Total Uploads</div>
                    </div>
                    <div class="col-4">
                        <div class="h4 mb-0 fw-bold">{{ round($loggedUser->screenshots->sum('file_size_kb') / 1024, 1) }} MB</div>
                        <div class="extra-small text-muted">Used Storage</div>
                    </div>
                </div>
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
            alert("API Key copied to clipboard!");
        });
    }
</script>
@endsection