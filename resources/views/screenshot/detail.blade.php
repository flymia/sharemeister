@extends('layouts.general')
@section('title', 'Screenshot Details')

@section('content')

    <div class="d-flex justify-content-center">
        <div class="card text-center" style="max-width: 800px;">
            <div class="card-body">
                <h2 class="card-title mb-4">Screenshot Details</h2>

                <!-- Screenshot Image -->
                <a href="{{ 'path/to/your/screenshot.png' }}" target="_blank">
                    <img src="{{ 'path/to/your/screenshot.png' }}" class="img-fluid" alt="Screenshot" style="cursor: pointer; max-height: 500px; width: auto;">
                </a>

                <div class="mt-4">
                    <h5>Metadata:</h5>
                    <p><strong>Upload Time:</strong> {{ '2024-10-27 12:34:56' }}</p>
                    <p><strong>Size:</strong> {{ '123 KB' }}</p>
                </div>

                <div class="mt-4">
                    <form action="{{ '#' }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Screenshot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
