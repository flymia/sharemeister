@extends('layouts.general')
@section('title', 'Welcome to Sharemeister!')
@section('content')

    <div class="container text-center my-5">
        <h1 class="display-3 font-weight-bold mb-4">Welcome to Sharemeister!</h1>
        <p class="lead mb-5">The simple, user-friendly screenshot server to meet your needs.</p>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <p class="mb-4">
                    This instance of Sharemeister is ready for configuration. If you need assistance,
                    visit the <a href="https://github.com/flymia/sharemeister" class="text-decoration-none text-primary">Sharemeister Wiki</a>.
                </p>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Register</a>
        </div>
    </div>

@endsection
