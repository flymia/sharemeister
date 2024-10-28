@extends('layouts.userbase')
@section('title', 'Dashboard')
@section('content')

    <div class="container mt-4">
        <h1 class="display-5 mb-4">Your Screenshots</h1>

        <div class="d-flex justify-content-end mb-3"> <!-- Flexbox for positioning -->
            <label for="sort" class="form-label me-2">Sort by:</label>
            <select id="sort" class="form-select form-select-sm" onchange="sortScreenshots()" style="width: auto;">
                <option value="latest" selected>Latest</option>
                <option value="oldest">Oldest</option>
                <option value="name">Name</option>
            </select>
        </div>

        <div class="row" id="screenshot-container">
            @foreach($screenshots as $scr)
                <div class="col-md-3 mb-3"> <!-- Bootstrap column for responsive layout -->
                    <div class="screenshot-container position-relative"> <!-- Position relative for absolute positioning -->
                        <img src="{{ $scr->publicURL }}" alt="Screenshot" class="screenshot-img" />
                        <div class="overlay text-center"> <!-- Overlay for hover effect -->
                            <a href="" class="btn btn-primary">View Details</a>
                            <form action="#" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
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

            // Here you could add logic to update the screenshots based on the sort value.
            // This could be an AJAX request or a redirect, depending on how you want to manage the data.

            // Example: Redirect with sorting parameters
            //window.location.href = ` route('screenshot.index') ?sort=${sortValue}`;
        }
    </script>

@endsection
