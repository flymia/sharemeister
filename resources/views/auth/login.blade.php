@extends('layouts.general')
@section('title', 'Login')
@section('content')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h1 class="display-4 text-center mb-4">Login</h1>
                <p class="lead text-center mb-4">Please enter your credentials to log in.</p>

                <hr>

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

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">E-Mail:</label>
                        <input type="email" name="email" class="form-control" id="email" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="{{ route('password.request') }}" class="text-primary">Forgot password?</a>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
