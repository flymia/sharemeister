@extends('layouts.userbase')
@section('title', 'Screenshot Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <a href="{{ route('screenshot.list') }}" class="btn btn-sm btn-link text-decoration-none text-muted mb-3">
            <i class="bi bi-arrow-left"></i> Back to Library
        </a>

        {{-- Fehlermeldungen anzeigen (Wichtig für den Delete-Block) --}}
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="row g-0">
                <div class="col-md-8 bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px;">
                    <img src="{{ $screenshot->public_url }}" class="img-fluid shadow" alt="Screenshot" style="max-height: 80vh;">
                </div>

                <div class="col-md-4 p-4 border-start">
                    {{-- ... Properties Sektion bleibt gleich ... --}}
                    <h4 class="fw-bold mb-4">Properties</h4>
                    
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold">File Name</label>
                        <p class="text-truncate">{{ basename($screenshot->image) }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold">Public URL</label>
                        <div class="input-group input-group-sm mt-1">
                            <input type="text" class="form-control" value="{{ $screenshot->public_url }}" id="urlInput" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyUrl()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <hr class="my-4">

                    {{-- Metadata Form --}}
                    <form action="{{ route('screenshot.update-metadata', $screenshot) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="tags" class="text-muted small text-uppercase fw-bold mb-1">Tags</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text text-muted"><i class="bi bi-tags"></i></span>
                                <input type="text" name="tags" id="tags" class="form-control" 
                                    value="{{ $screenshot->tags->pluck('name')->implode(', ') }}">
                            </div>
                        </div>

                        <div class="mb-4 p-2 rounded border-start border-4 {{ $screenshot->is_permanent ? 'border-primary ' : 'border-secondary' }}">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input cursor-pointer" type="checkbox" name="is_permanent" id="is_permanent" 
                                    {{ $screenshot->is_permanent ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label fw-bold small cursor-pointer" for="is_permanent">
                                    <i class="bi {{ $screenshot->is_permanent ? 'bi-shield-lock-fill text-primary' : 'bi-shield-slash text-muted' }} me-1"></i>
                                    Persistent Protection
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100 mb-4 shadow-sm">
                            <i class="bi bi-save me-2"></i>Save Metadata
                        </button>
                    </form>

                    {{-- Info Sektion --}}
                    <div class="d-flex flex-column gap-3 mb-5 pt-3 border-top">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 me-3 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Uploaded on</small>
                                <strong>{{ $screenshot->created_at->format('d M Y, H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Delete Sektion mit Schutz-Logik --}}
                    <div class="d-grid pt-4 border-top">
                        <form action="{{ route('screenshot.delete', $screenshot) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            
                            @if($screenshot->is_permanent)
                                <button type="button" class="btn btn-outline-secondary w-100 btn-sm opacity-50" disabled>
                                    <i class="bi bi-lock-fill me-2"></i>Screenshot is Protected
                                </button>
                                <p class="extra-small text-center text-muted mt-2">Unlock protection to delete this file.</p>
                            @else
                                <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                    <i class="bi bi-trash3 me-2"></i>Delete Screenshot
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyUrl() {
        const copyText = document.getElementById("urlInput");
        copyText.select();
        navigator.clipboard.writeText(copyText.value);
        // Using a modern approach instead of alert() for better UX
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        setTimeout(() => btn.innerHTML = originalContent, 2000);
    }
</script>
@endsection