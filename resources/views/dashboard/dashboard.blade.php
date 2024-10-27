@extends('layouts.userbase')
@section('title', 'Dashboard')
@section('content')

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
@endsection
