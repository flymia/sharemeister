@extends('layouts.userbase')
@section('title', 'Your Screenshots')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-0">Library</h1>
        <p class="text-muted mb-0">Manage and organize your captured moments.</p>
        <span class="small text-body-secondary">{{ $screenshots->total() }} {{ Str::plural('screenshot', $screenshots->total()) }}</span>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <form method="GET" action="{{ route('screenshot.list') }}" role="search" class="d-flex">
            <input type="hidden" name="sort" value="{{ $sort }}">
            @if($currentTag)<input type="hidden" name="tag" value="{{ $currentTag }}">@endif
            <div class="input-group input-group-sm shadow-sm" style="width: 220px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="search" name="q" value="{{ $search }}" class="form-control border-start-0 ps-0"
                       placeholder="Search filename..." aria-label="Search screenshots by filename">
            </div>
        </form>

        <div class="d-flex align-items-center gap-2">
            <label for="sort" class="small fw-bold text-uppercase text-muted">Sort By</label>
            <select id="sort" class="form-select form-select-sm shadow-sm" onchange="sortScreenshots()" style="width: auto;">
                <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Newest first</option>
                <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Oldest first</option>
            </select>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="small fw-bold text-uppercase text-muted d-block mb-2">Filter by Tag</label>
    <div class="d-flex flex-wrap gap-2 tag-filter-bar">
        {{-- Link to reset the tag filter but keep the current sorting --}}
        <a href="{{ route('screenshot.list', ['sort' => $sort]) }}" 
           class="btn btn-sm {{ !request('tag') ? 'btn-dark shadow-sm' : 'btn-outline-secondary' }}">
            All
        </a>
        
        @foreach($allTags as $tag)
            {{-- Each tag link preserves the sorting order --}}
            <a href="{{ route('screenshot.list', ['tag' => $tag->slug, 'sort' => $sort]) }}" 
               class="btn btn-sm {{ request('tag') == $tag->slug ? 'btn-dark shadow-sm' : 'btn-outline-secondary' }}">
                #{{ $tag->name }}
            </a>
        @endforeach
    </div>
</div>

@if(session('message'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('message') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
    </div>
@endif

<div class="row g-4" id="screenshot-container">
    @forelse($screenshots as $scr)
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 screenshot-card">
                <a href="{{ route('screenshot.details', $scr) }}"
                   class="ratio ratio-16x9 bg-light rounded-top overflow-hidden d-block"
                   aria-label="View details for {{ basename($scr->image) }}">
                    <img src="{{ $scr->publicURL }}" alt="{{ basename($scr->image) }}"
                         class="object-fit-cover img-hover" loading="lazy" />
                </a>

                @if($scr->is_permanent)
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-primary shadow-sm" title="Protected">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                        </div>
                @endif

                <div class="card-body p-3">
                    <div class="mb-2">
                        <p class="text-truncate small fw-bold mb-0" title="{{ basename($scr->image) }}">
                            {{ basename($scr->image) }}
                        </p>
                        <span class="text-body-secondary extra-small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $scr->created_at->diffForHumans() }}
                        </span>
                        @if($scr->file_size_kb)
                            <span class="text-body-secondary extra-small ms-2">
                                <i class="bi bi-file-earmark-binary me-1"></i>{{ $scr->file_size_kb }} KB
                            </span>
                        @endif
                    </div>

                    {{-- Tags Badges --}}
                    <div class="mt-2 mb-3">
                        @foreach($scr->tags as $tag)
                            <a href="{{ route('screenshot.list', ['tag' => $tag->slug, 'sort' => $sort]) }}" 
                               class="badge bg-light text-dark border text-decoration-none extra-small me-1">
                               #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('screenshot.details', $scr) }}" class="btn btn-light border" title="Details" aria-label="View details">
                                <i class="bi bi-info-circle"></i>
                            </a>
                            <button type="button" onclick="copyToClipboard('{{ $scr->publicURL }}')" class="btn btn-light border" title="Copy Link" aria-label="Copy link">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>

                        @if($scr->is_permanent)
                            {{-- Deactivated button --}}
                            <button type="button" class="btn btn-sm btn-outline-secondary border-0 opacity-50"
                                    title="Protected: Disable protection in details to delete" aria-label="Protected screenshot" disabled>
                                <i class="bi bi-lock-fill"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 js-delete-btn"
                                    data-action="{{ route('screenshot.delete', $scr) }}"
                                    data-name="{{ basename($scr->image) }}"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    title="Delete" aria-label="Delete screenshot">
                                <i class="bi bi-trash3"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 py-5 text-center">
            <div class="py-5">
                @if($currentTag || $search)
                    <i class="bi bi-search display-1 text-muted opacity-25"></i>
                    <p class="mt-3 text-muted">No screenshots match your filters.</p>
                    <a href="{{ route('screenshot.list') }}" class="btn btn-outline-primary btn-sm px-4 mt-2">Clear all filters</a>
                @else
                    <i class="bi bi-images display-1 text-muted opacity-25"></i>
                    <p class="mt-3 text-muted">You haven't uploaded any screenshots yet.</p>
                    <a href="{{ route('screenshot.upload') }}" class="btn btn-primary btn-sm px-4 mt-2">
                        <i class="bi bi-upload me-1"></i>Upload your first screenshot
                    </a>
                @endif
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-5">
    {{-- The appends() call in the controller ensures pagination links include tags/sort --}}
    {{ $screenshots->links('pagination::bootstrap-5') }}
</div>

{{-- Shared delete confirmation modal (populated per-card via JS) --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete screenshot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Delete <strong id="deleteModalName">this screenshot</strong> permanently? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteModalForm" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .screenshot-card { transition: all 0.2s ease-in-out; }
    .screenshot-card:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
    .img-hover { transition: transform 0.5s ease; }
    .screenshot-card:hover .img-hover { transform: scale(1.1); }

    /* Keep the tag filter bar from growing unbounded when a user has many tags */
    .tag-filter-bar { max-height: 6.5rem; overflow-y: auto; }

    /* Restore a visible focus ring on the clickable thumbnail (hover CSS aside) */
    .screenshot-card a.ratio:focus-visible { outline: 3px solid var(--bs-primary); outline-offset: 2px; }
</style>

<script>
    /**
     * English comment: Redirect with current tag and new sort value
     */
    function sortScreenshots() {
        const sortValue = document.getElementById('sort').value;
        const urlParams = new URLSearchParams(window.location.search);
        
        urlParams.set('sort', sortValue);
        // Important: Keep the page at 1 when sorting changes to avoid empty results
        urlParams.delete('page'); 

        window.location.href = `{{ route('screenshot.list') }}?${urlParams.toString()}`;
    }

    // Populate the shared delete modal with the clicked screenshot's form action + name
    document.querySelectorAll('.js-delete-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('deleteModalForm').action = btn.dataset.action;
            document.getElementById('deleteModalName').textContent = btn.dataset.name;
        });
    });
</script>
@endsection