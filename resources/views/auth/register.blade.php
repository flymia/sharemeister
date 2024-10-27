@extends('layouts.general')
@section('title', 'Register')
@section('content')

    <div class="container my-5">
        <h1 class="display-4 mb-4 text-center">Register</h1>
        <p class="lead text-center">Please create your account.</p>
        <hr>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error during request:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="mx-auto" style="max-width: 400px;">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-Mail:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password:</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p>Already have an account? <a href="{{ route('login') }}" class="text-primary">Login here</a>.</p>
        </div>
    </div>

@endsection
