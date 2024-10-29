@extends('layouts.userbase')
@section('title', 'Screenshot Details')

@section('content')

    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-lg border-0" style="max-width: 800px; border-radius: 12px;">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Screenshot Details</h2>

                <!-- Screenshot Image -->
                <div class="text-center mb-4">
                    <a href="{{ $screenshot->publicURL }}" target="_blank">
                        <img src="{{ $screenshot->publicURL }}" class="img-fluid rounded"
                             alt="Screenshot" style="cursor: pointer; max-height: 500px; width: auto; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                    </a>
                </div>

                <!-- Metadata Section -->
                <div class="bg-light p-3 rounded mb-4" style="box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <h5 class="text-muted mb-3">Screenshot Metadata</h5>
                    <p class="mb-1"><strong>Upload Time:</strong> {{ $screenshot->created_at->format('d M Y, H:i') }}</p>
                    <p class="mb-0"><strong>Size:</strong> {{ $screenshot->file_size_kb }} KB</p>
                </div>

                <!-- Delete Button -->
                <div class="text-center">
                    <form action="{{ '#' }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-lg px-4"
                                style="border-radius: 30px; box-shadow: 0 3px 8px rgba(255, 0, 0, 0.3);">
                            Delete Screenshot
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
