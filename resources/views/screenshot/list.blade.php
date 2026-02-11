@extends('layouts.userbase')
@section('title', 'Your Screenshots')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-0">Library</h1>
        <p class="text-muted">Manage and organize your captured moments.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <label for="sort" class="small fw-bold text-uppercase text-muted">Sort By</label>
        <select id="sort" class="form-select form-select-sm shadow-sm" onchange="sortScreenshots()" style="width: auto;">
            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest first</option>
            <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Oldest first</option>
        </select>
    </div>
</div>

@if(session('message'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('message') }}
    </div>
@endif

<div class="row g-4" id="screenshot-container">
    @forelse($screenshots as $scr)
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 screenshot-card">
                <div class="ratio ratio-16x9 bg-light rounded-top overflow-hidden">
                    <img src="{{ $scr->publicURL }}" alt="Screenshot" class="object-fit-cover img-hover" />
                </div>
                
                <div class="card-body p-3">
                    <div class="mb-2">
                        <p class="text-truncate small fw-bold mb-0">{{ basename($scr->image) }}</p>
                        <span class="text-muted extra-small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $scr->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('screenshot.details', $scr->id) }}" class="btn btn-light border" title="Details">
                                <i class="bi bi-info-circle"></i>
                            </a>
                            <button onclick="copyToClipboard('{{ $scr->publicURL }}')" class="btn btn-light border" title="Copy Link">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>

                        <form action="{{ route('screenshot.delete', $scr->id) }}" method="POST" onsubmit="return confirm('Delete this screenshot permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 py-5 text-center">
            <div class="py-5">
                <i class="bi bi-cloud-upload display-1 text-muted opacity-25"></i>
                <p class="mt-3 text-muted">No screenshots found. Time to capture something!</p>
                <a href="{{ route('screenshot.upload') }}" class="btn btn-primary px-4 mt-2">Upload Now</a>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-5">
    {{ $screenshots->links('pagination::bootstrap-5') }}
</div>

<style>
    .screenshot-card:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
    .img-hover { transition: transform 0.5s ease; }
    .screenshot-card:hover .img-hover { transform: scale(1.1); }
    .extra-small { font-size: 0.75rem; }
    .object-fit-cover { object-fit: cover; }
    
    /* Toast Styling */
    .copy-toast {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        background: #198754;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.2);
    }
</style>

<script>
    function sortScreenshots() {
        const sortValue = document.getElementById('sort').value;
        window.location.href = `{{ route('screenshot.list') }}?sort=${sortValue}`;
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = $('<div class="copy-toast shadow-lg"><i class="bi bi-check-lg me-2"></i> Link copied!</div>').appendTo('body');
            setTimeout(() => toast.fadeOut(500, function() { $(this).remove(); }), 2000);
        });
    }
</script>
@endsection