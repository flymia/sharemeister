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
                {{ session('message') }}
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
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .screenshot-img {
            width: 100%;
            height: auto;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            align-items: flex-end; /* Position the content at the bottom */
            padding: 10px; /* Add padding for spacing */
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Show overlay on hover */
        .screenshot-container:hover .overlay {
            opacity: 1;
        }

        /* Zoom-in effect on image */
        .screenshot-container:hover .screenshot-img {
            transform: scale(1.05);
        }

        /* Container for the buttons within the overlay */
        .button-group {
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            gap: 8px; /* Space between buttons */
            width: 100%; /* Make buttons fill the width of the container */
        }

        .button-group a,
        .button-group form {
            display: block;
            width: 100%;
        }
    </style>


    <script>
        function sortScreenshots() {
            const sortValue = document.getElementById('sort').value;
            window.location.href = `{{ route('screenshot.list') }}?sort=${sortValue}`;
        }
    </script>

@endsection
