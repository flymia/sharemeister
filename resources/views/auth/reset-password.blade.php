<h1>Please enter your new password:</h1>

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

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <label for="email">E-Mail</label>
    <input type="email" name="email" required>

    <label for="password">Password</label>
    <input type="password" name="password" required>

    <label for="password_confirmation">Confirm password</label>
    <input type="password" name="password_confirmation" required>

    <input type="hidden" name="token" value="{{ $request->route('token') }}" required>
    <input type="submit" title="Reset password">
</form>

