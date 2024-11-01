@extends('layouts.userbase')
@section('title', 'Account Settings')
@section('content')

    <div class="container mt-4 d-flex justify-content-center"> <!-- Center content within the container -->
        <div style="max-width: 800px; width: 100%;"> <!-- Responsive width to center cards -->
            <h1 class="display-5 mb-4 text-center">Account Settings</h1>
            <p class="text-center">Here you can update your profile details, generate an API key, and view your account statistics.</p>

            <!-- Profile Details Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Profile Details</h5>
                    <form action="" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $loggedUser->name }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $loggedUser->email }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- API Key Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">API Key</h5>
                    <p>Generate a new API key for external access.</p>
                    <form action="" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">Generate API Key</button>
                    </form>
                </div>
            </div>

            <!-- Account Statistics Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Account Statistics</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Account creation date:</strong> {{ $loggedUser->created_at->format('Y-m-d') }}</li>
                        <li class="list-group-item"><strong>Uploaded screenshots:</strong> {{ $loggedUser->screenshots()->count() }}</li>
                        <li class="list-group-item"><strong>Total file size:</strong> {{ $loggedUser->screenshots->sum('file_size_kb') }} KB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
