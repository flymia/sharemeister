@extends('layouts.userbase')
@section('title', 'Dashboard')
@section('content')

    <div class="container mt-4">
        <h1 class="display-5 mb-4">Your Screenshots</h1>

        <!-- Alert when deleted -->
        @if(session('message'))
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                The screenshot was deleted.
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-end mb-3"> <!-- Flexbox for positioning -->
            <label for="sort" class="form-label me-2">Sort by:</label>
            <select id="sort" onchange="sortScreenshots()">
                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date (Newest First)</option>
                <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Date (Oldest First)</option>

                <!-- Maybe for the future -->
                <!-- Sort by size ? -->
            </select>
        </div>

        <div class="row" id="screenshot-container">
            @if($screenshots->isEmpty())
                <p class="text-center">No screenshots available. Start by uploading your first screenshot!</p>
            @else
                @foreach($screenshots as $scr)
                    <div class="col-md-3 mb-3"> <!-- Bootstrap column for responsive layout -->
                        <div class="screenshot-container position-relative"> <!-- Position relative for absolute positioning -->
                            <img src="{{ $scr->publicURL }}" alt="Screenshot" class="screenshot-img" />
                            <div class="overlay text-center"> <!-- Overlay for hover effect -->
                                <a href="{{ route('screenshot.details', $scr->id) }}" class="btn btn-primary">View Details</a>
                                <a href="" class="btn btn-secondary">Copy link</a>
                                <form action="{{ route('screenshot.delete', $scr->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $screenshots->links('pagination::bootstrap-5') }} <!-- Bootstrap pagination links -->
        </div>
    </div>

    <style>
        .screenshot-container {
            width: 100%; /* Adjust width */
            overflow: hidden; /* Hide overflow */
            position: relative; /* For positioning images */
        }

        .screenshot-img {
            width: 100%; /* Set image to full width */
            height: auto; /* Automatically adjust height */
            max-height: 200px; /* Set maximum height */
            object-fit: cover; /* Maintain aspect ratio */
        }

        .overlay {
            position: absolute; /* Position overlay absolutely */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            color: white; /* Text color */
            display: flex; /* Flexbox for centering */
            flex-direction: column; /* Stack buttons vertically */
            justify-content: center; /* Center vertically */
            align-items: center; /* Center horizontally */
            opacity: 0; /* Start hidden */
            transition: opacity 0.3s ease; /* Fade effect */
        }

        .screenshot-container:hover .overlay {
            opacity: 1; /* Show overlay on hover */
        }

    </style>

    <script>
        function sortScreenshots() {
            const sortValue = document.getElementById('sort').value;
            window.location.href = `{{ route('screenshot.list') }}?sort=${sortValue}`;
        }
    </script>

@endsection
