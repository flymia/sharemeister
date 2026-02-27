@extends('layouts.userbase')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-6 fw-bold mb-0">Welcome back, {{ Auth::user()->name }}</h1>
            <p class="text-muted">Here is what's happening with your screenshots.</p>
        </div>
    </div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="text-muted small text-uppercase fw-bold">Total Uploads</div>
            <div class="h4 mb-0 fw-bold text-primary">
                <i class="bi bi-images me-2"></i>{{ $totalCount }}
            </div>
        </div>
    </div>

    <div class="col-md-6"> <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="text-muted small text-uppercase fw-bold">Storage Usage</div>
                <div class="small fw-bold {{ $usagePercent > 90 ? 'text-danger' : 'text-muted' }}">
                    {{ $totalSize }} MB / {{ $limit == -1 ? '∞' : $limit . ' MB' }}
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="h4 mb-0 fw-bold text-primary">
                    <i class="bi bi-hdd-network me-2"></i>{{ $totalSize }} MB
                </div>
                
                <div class="flex-grow-1">
                    @if($limit != -1)
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $usagePercent > 90 ? 'bg-danger' : 'bg-primary' }}" 
                                 role="progressbar" 
                                 style="width: {{ $usagePercent }}%"
                                 aria-valuenow="{{ $usagePercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <div class="extra-small text-muted mt-1 text-end">{{ $usagePercent }}% used</div>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Infinite Storage</span>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <h5 class="mb-3 fw-bold">Recently uploaded</h5>
    
    <div class="row g-4">
        @forelse($screenshots as $screenshot)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <a href="{{ route('screenshot.details', $screenshot) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="position-relative overflow-hidden rounded-top" style="height: 140px;">
                            <img src="{{ $screenshot->public_url }}" 
                                class="card-img-top w-100 h-100 object-fit-cover" 
                                alt="Screenshot">

                            @if($screenshot->is_permanent)
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-primary shadow-sm">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="card-title text-truncate mb-0 small fw-bold" title="{{ basename($screenshot->image) }}">
                                    {{ basename($screenshot->image) }}
                                </h6>
                            </div>
                            
                            <div class="text-muted extra-small mb-3">
                                <i class="bi bi-clock me-1"></i> {{ $screenshot->created_at->diffForHumans() }} <br>
                                <i class="bi bi-file-earmark-binary me-1"></i> {{ $screenshot->file_size_kb }} KB
                            </div>

                            <div class="d-grid gap-2">
                                <div class="btn-group btn-group-sm">
                                    {{-- 'stopPropagation' verhindert, dass die Detailseite aufgeht. 
                                        Das 'target="_blank"' funktioniert dann wieder normal. --}}
                                    <a href="{{ $screenshot->public_url }}" 
                                    target="_blank" 
                                    class="btn btn-outline-primary" 
                                    onclick="event.stopPropagation();"
                                    title="Open RAW">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <button type="button" 
                                            onclick="event.stopPropagation(); copyToClipboard('{{ $screenshot->public_url }}')" 
                                            class="btn btn-outline-secondary" 
                                            title="Copy Link">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-camera-video-off h1 opacity-25"></i>
                    <p>No screenshots yet. Your future uploads will appear here.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        cursor: pointer;
    }
    .transition { transition: all 0.2s ease-in-out; }
    .object-fit-cover { object-fit: cover; }
    .extra-small { font-size: 0.75rem; }
</style>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            // Bootstrap classes for a clean notification
            toast.className = 'alert alert-dark border-0 shadow-lg d-flex align-items-center';
            toast.innerHTML = `
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <span>URL copied to clipboard!</span>
            `;
            container.appendChild(toast);
            
            // Fade out and remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }, 2500);
        });
    }
</script>
@endsection