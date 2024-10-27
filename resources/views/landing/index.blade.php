@extends('layouts.general')
@section('title', 'Welcome to YAAMS!')
@section('content')

    <h1 class="display-4 mb-4">Welcome to YAAMS!</h1>
    <p class="lead">The modern virtual airline management system.</p>
    <hr>
    <p>
        This is a YAAMS instance. Please configure it.
        You can get help in the <a class="text-blue-800" href="https://github.com/YAAMSOrg/YAAMS">wiki here</a>.
    </p>

    <p>Please <a href="{{ route('login') }}">login</a>.</p>

@endsection
