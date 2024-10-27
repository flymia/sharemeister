@extends('layouts.general')
@section('title', 'Register')
@section('content')

    <h1 class="display-4 mb-4">Register</h1>
    <p class="lead">Please Register.</p>
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

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <label for="name">Name:</label>
        <input type="name" name="name" required>

        <label for="email">E-Mail:</label>
        <input type="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="password_confirmation">Confirm password:</label>
        <input type="password" name="password_confirmation" required>

        <input type="submit" value="Register">
    </form>

@endsection
