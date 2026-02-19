@extends('layouts.userbase')
@section('title', 'Upload')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold">Upload Screenshot</h1>
            <p class="text-muted">Manual upload for your captures. For automated uploads, use our <a href="https://github.com/flymia/sharemeister/wiki/Using-the-API" class="text-primary text-decoration-none">API Guide</a>.</p>
        </div>

        @if(session('success'))
            <div class="card border-success mb-4 shadow-sm">
                <div class="card-body d-flex align-items-center py-3">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <div>
                        <div class="fw-bold">Upload successful!</div>
                        <a href="{{ session('public_link') }}" class="small text-decoration-none" target="_blank">{{ session('public_link') }}</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-5">

                        @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                        <div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

                <form action="{{ route('screenshot.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    
                    <div id="drop-zone" class="upload-area mb-4">
                        <input type="file" id="image" name="image[]" accept=".png, .jpg, .jpeg, .gif" class="d-none" multiple required>
                        <div class="text-center py-4">
                            <div class="upload-icon mb-3">
                                <i class="bi bi-cloud-arrow-up text-primary display-4"></i>
                            </div>
                            <h5 class="fw-bold" id="drop-text">Drag & drop images here</h5>
                            <p class="text-muted small">or click to browse from your computer</p>
                            <span class="badge bg-light text-muted border">
                                PNG, JPG, GIF up to {{ $maxSizeKb / 1024 }}MB
                            </span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn">
                            <i class="bi bi-send me-2"></i>Start Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
    /* Die Upload-Zone nutzt nun Variablen statt fester Farben */
    .upload-area {
        border: 2px dashed var(--bs-border-color);
        border-radius: 1rem;
        transition: all 0.2s ease;
        cursor: pointer;
        background-color: var(--bs-tertiary-bg); /* Passt sich an */
    }
    
    .upload-area:hover, .upload-area.dragover {
        border-color: var(--bs-primary);
        background-color: var(--bs-primary-bg-subtle);
    }

    /* Badge in der Upload-Zone korrigieren */
    .upload-area .badge {
        background-color: var(--bs-secondary-bg) !important;
        color: var(--bs-secondary-color) !important;
        border: 1px solid var(--bs-border-color) !important;
    }

    /* Screenshot-Cards in der Liste */
    .screenshot-card {
        background-color: var(--bs-card-bg);
        color: var(--bs-body-color);
    }

    /* Icons in der Liste/Details */
    .bi {
        /* Falls Icons zu blass sind, geben wir ihnen eine Standard-Farbe des Themes */
        color: var(--bs-body-color);
    }
    
    .text-primary .bi, .btn .bi {
        color: inherit; /* Icons in Buttons oder blauen Texten behalten deren Farbe */
    }

    .extra-small { font-size: 0.75rem; }
    .object-fit-cover { object-fit: cover; }
    .transition { transition: all 0.2s ease-in-out; }
</style>

<script>
    const dropZone = document.getElementById("drop-zone");
    const fileInput = document.getElementById("image");
    const dropText = document.getElementById("drop-text");
    const submitBtn = document.getElementById("submitBtn");
    
    // Inject limit from Laravel config (KB to Bytes)
    const MAX_SIZE_BYTES = {{ $maxSizeKb }} * 1024;

    dropZone.onclick = () => fileInput.click();

    // Fix: Corrected event name 'ondragover'
    dropZone.ondragover = (e) => { 
        e.preventDefault(); 
        dropZone.classList.add("dragover"); 
    };
    
    dropZone.ondragleave = () => dropZone.classList.remove("dragover");
    
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.classList.remove("dragover");
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateUI(fileInput.files);
        }
    };

    fileInput.onchange = () => {
        if (fileInput.files.length) updateUI(fileInput.files);
    };

    function updateUI(files) {
        let tooLarge = false;
        const limitMb = (MAX_SIZE_BYTES / 1024 / 1024).toFixed(0);

        for (let file of files) {
            if (file.size > MAX_SIZE_BYTES) {
                tooLarge = true;
                break;
            }
        }

        if (tooLarge) {
            // Visual warning if client-side validation fails
            dropText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> One or more files are over ${limitMb}MB!</span>`;
            submitBtn.disabled = true;
            submitBtn.classList.replace('btn-primary', 'btn-danger');
        } else {
            // Feedback for successful selection
            dropText.innerHTML = `<span class="text-primary fw-bold">${files.length} file(s) selected</span>`;
            submitBtn.disabled = false;
            submitBtn.classList.replace('btn-danger', 'btn-primary');
        }
    }
</script>
@endsection