@extends('layouts.general')
@section('title', 'Login')
@section('content')

    <h1 class="display-4 mb-4">Login</h1>
    <p class="lead">Please login.</p>
    <hr>

    @if($errors->any())
        <div class="alert alert-danger">
            Error during request:
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <label for="email">E-Mail:</label>
        <input type="email" name="email">

        <label for="password">Password:</label>
        <input type="password" name="password">

        <a href="{{ route('password.request') }}">Forgot password?</a>
        <input type="submit" value="Login">
    </form>

@endsection
