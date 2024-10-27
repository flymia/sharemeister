@extends('layouts.general')
@section('title', 'Dashboard')
@section('content')

    <div class="container my-5">
        <!-- Modern header with user info, links, and logout button -->
        <div class="bg-light rounded shadow-sm p-3 mb-4 d-flex justify-content-between align-items-center">
            <h1 class="display-5 mb-0">Dashboard</h1>
            <div class="d-flex align-items-center gap-4">
                <span class="text-secondary">{{ auth()->user()->name }}</span>
                <div class="vr"></div>
                <a href="#" class="text-decoration-none text-primary">My Screenshots</a>
                <div class="vr"></div>
                <a href="#" class="text-decoration-none text-primary">Settings</a>
                <div class="vr"></div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
        </div>

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

        <!-- Latest uploaded screenshots -->
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3">Latest Uploaded Screenshots</h5>
                <div class="d-flex gap-3 flex-wrap justify-content-center">
                    @for($i = 1; $i <= 6; $i++)
                        <div class="card shadow-sm mb-4" style="width: 180px;">
                            <img src="https://via.placeholder.com/180x100" class="card-img-top" alt="Screenshot {{ $i }}">
                            <div class="card-body text-center">
                                <h6 class="card-title">Screenshot {{ $i }}</h6>
                                <p class="card-text">Uploaded on: <span class="text-muted">Date {{ $i }}</span></p>
                                <a href="#" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

@endsection
