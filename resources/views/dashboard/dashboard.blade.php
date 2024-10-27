<h1>Du bist eingeloggt und heißt {{ auth()->user()->name }}</h1>

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

<form action="{{ route('logout') }}" method="POST">
    @csrf
    <p>Log dich aus.</p>
    <input type="submit">
</form>
