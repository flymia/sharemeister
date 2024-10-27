<h1>No problem, enter your email address to get your reset password link:</h1>

@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif

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

<form action="{{ route('password.request') }}" method="POST">
    @csrf
    <label for="email">E-Mail:</label>
    <input type="email" name="email" required>
    <input type="submit" title="Reset password">
</form>
