@extends('layouts.userbase')
@section('title', 'Screenshot Upload')

@section('content')

    <div class="container mt-4">
        <h1 class="display-5 mb-4">Upload Screenshot</h1>

        <p class="lead">Please upload your screenshot.</p>

        <!-- Info Alert -->
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                Allowed file types: <strong>PNG, JPG, JPEG</strong>.<br>
                Maximum size: <strong>2MB</strong>.
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error during upload:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('screenshot.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="screenshot" class="form-label">Select Screenshot:</label>
                <input type="file" class="form-control" id="screenshot" name="screenshot" accept=".png, .jpg, .jpeg" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

@endsection
