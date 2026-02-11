@extends('layouts.general')
@section('title', 'Setup Required')
@section('content')
<div class="container py-5 text-center">
    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
        <i class="bi bi-terminal-fill fs-1"></i>
    </div>
    <h1 class="display-5 fw-bold">Instance Not Configured</h1>
    <p class="lead text-muted">Please log in to your server and run the following command to initialize this Sharemeister instance:</p>
    <div class="bg-dark text-light p-3 rounded-3 mb-4 shadow-sm mx-auto" style="max-width: 500px;">
        <code class="text-info">php artisan sharemeister:install</code>
    </div>
    <p class="small text-muted">This is a security measure to ensure you are the owner of this infrastructure.</p>
</div>
@endsection
