@extends('layouts.userbase')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-5 gap-3 flex-wrap">
        <div>
            <h1 class="display-6 fw-bold mb-0">Welcome back, {{ Auth::user()->name }}</h1>
            <p class="text-muted mb-0">Here is what's happening with your screenshots.</p>
        </div>
        <a href="{{ route('screenshot.upload') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-upload me-1"></i> Upload
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small text-uppercase fw-bold">Total Uploads</div>
                <div class="h4 mb-0 fw-bold text-primary">
                    <i class="bi bi-images me-2"></i>{{ $totalCount }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small text-uppercase fw-bold">Permanent</div>
                <div class="h4 mb-0 fw-bold text-primary">
                    <i class="bi bi-shield-lock-fill me-2"></i>{{ $permanentCount }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100">
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Recently uploaded</h5>
        @if($totalCount > 0)
            <a href="{{ route('screenshot.list') }}" class="small text-decoration-none fw-bold">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        @endif
    </div>

    <div class="row g-4">
        @forelse($screenshots as $screenshot)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition position-relative">
                    <a href="{{ route('screenshot.details', $screenshot) }}"
                       class="ratio ratio-16x9 bg-light rounded-top overflow-hidden d-block"
                       aria-label="View details for {{ basename($screenshot->image) }}">
                        <img src="{{ $screenshot->public_url }}"
                             class="object-fit-cover w-100 h-100"
                             loading="lazy"
                             alt="{{ basename($screenshot->image) }}">
                    </a>

                    @if($screenshot->is_permanent)
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-primary shadow-sm" title="Protected">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                        </div>
                    @endif

                    <div class="card-body p-3">
                        <h6 class="card-title text-truncate mb-1 small fw-bold" title="{{ basename($screenshot->image) }}">
                            {{ basename($screenshot->image) }}
                        </h6>

                        <div class="text-muted extra-small mb-3">
                            <i class="bi bi-clock me-1"></i> {{ $screenshot->created_at->diffForHumans() }} <br>
                            <i class="bi bi-file-earmark-binary me-1"></i> {{ $screenshot->file_size_kb }} KB
                        </div>

                        <div class="btn-group btn-group-sm w-100">
                            <a href="{{ $screenshot->public_url }}"
                               target="_blank"
                               class="btn btn-outline-primary"
                               title="Open RAW">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button"
                                    onclick="copyToClipboard('{{ $screenshot->public_url }}')"
                                    class="btn btn-outline-secondary"
                                    title="Copy Link">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="py-5">
                    <i class="bi bi-camera-video-off display-1 text-muted opacity-25"></i>
                    <p class="mt-3 text-muted">No screenshots yet. Your future uploads will appear here.</p>
                    <a href="{{ route('screenshot.upload') }}" class="btn btn-primary btn-sm px-4 mt-2">
                        <i class="bi bi-upload me-1"></i>Upload your first screenshot
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition { transition: all 0.2s ease-in-out; }
</style>
@endsection
