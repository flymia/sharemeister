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
                    <p>Manage your API key.</p>

                    @if(session('message'))
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                {{ session('message') }}
                            </div>
                        </div>
                    @endif

                    @if(session('userHasAPIKey'))
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                You already created an API key on <b>{{ session('apiKeyCreatedAt') }}</b>. The API key only shows once on creation. If you lost your old one, please delete the current API key and create a new one.
                            </div>
                        </div>

                        <form action="{{ route('account.settings.deleteapikey') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">Delete API key</button>
                        </form>
                    @else
                        @if(session('apikey'))
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <p>Successfully generated API key. Be sure to save this key somewhere. It will only show once!</p>

                                    <p class="d-inline-flex gap-1">
                                        <a class="" data-bs-toggle="collapse" href="#apiCollapse" role="button" aria-expanded="false" aria-controls="apiCollapse">
                                            Show Key
                                        </a>
                                    </p>
                                    <div class="collapse" id="apiCollapse">
                                        <div class="card card-body">
                                            <span id="apiKey" style="cursor: pointer; color: blue; text-decoration: underline;">adopkwapodkawdopkadwop</span>
                                            <small id="copyFeedback" style="display: none; color: green; font-style: italic;">Copied to clipboard!</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                            <form action="{{ route('account.settings.generateapikey') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning">Generate API Key</button>
                            </form>
                    @endif
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

    <script>
        document.getElementById("apiKey").onclick = function() {
            const apiKey = document.getElementById("apiKey").textContent;
            navigator.clipboard.writeText(apiKey).then(() => {
                const feedback = document.getElementById("copyFeedback");
                feedback.style.display = "inline";
                setTimeout(() => { feedback.style.display = "none"; }, 2000); // Hide feedback after 2 seconds
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>

@endsection
