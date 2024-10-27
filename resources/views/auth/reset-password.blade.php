@extends('layouts.general')
@section('title', 'Reset Password')
@section('content')

    <div class="container my-5">
        <h1 class="display-5 mb-4 text-center">Reset Password</h1>
        <p class="lead text-center">Please enter your new password:</p>
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

        <form action="{{ route('password.update') }}" method="POST" class="mx-auto" style="max-width: 400px;">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}" required>

            <div class="mb-3">
                <label for="email" class="form-label">E-Mail:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm New Password:</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>

@endsection
