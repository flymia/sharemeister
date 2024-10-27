@extends('layouts.general')
@section('title', 'Forgot Password')
@section('content')

    <div class="container my-5">
        <h1 class="display-5 mb-4 text-center">Forgot Password</h1>
        <p class="lead text-center">No problem, enter your email address to get your reset password link:</p>
        <hr>

        @if (session('status'))
            <div class="alert alert-success text-center">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error during request:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.request') }}" method="POST" class="mx-auto" style="max-width: 400px;">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">E-Mail:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p>Remembered your password? <a href="{{ route('login') }}" class="text-primary">Login here</a>.</p>
        </div>
    </div>

@endsection
