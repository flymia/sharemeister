@extends('layouts.userbase')
@section('title', 'Upload')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold">Upload Screenshot</h1>
            <p class="text-muted">Manual upload for your captures. For automated uploads, use our <a href="#" class="text-primary text-decoration-none">API Guide</a>.</p>
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
                            <h5 class="fw-bold" id="drop-text">Drag & drop image here</h5>
                            <p class="text-muted small">or click to browse from your computer</p>
                            <span class="badge bg-light text-muted border">PNG, JPG, GIF up to 2MB</span>
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
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 1rem;
        transition: all 0.2s ease;
        cursor: pointer;
        background-color: #f8f9fa;
    }
    .upload-area:hover, .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #f0f7ff;
    }
    .upload-icon {
        transition: transform 0.3s ease;
    }
    .upload-area:hover .upload-icon {
        transform: translateY(-5px);
    }
</style>

<script>
    const dropZone = document.getElementById("drop-zone");
    const fileInput = document.getElementById("image");
    const dropText = document.getElementById("drop-text");

    dropZone.onclick = () => fileInput.click();

    dropZone.onsnaddragover = (e) => { e.preventDefault(); dropZone.classList.add("dragover"); };
    dropZone.ondragleave = () => dropZone.classList.remove("dragover");
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.classList.remove("dragover");
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateUI(e.dataTransfer.files[0]);
        }
    };

// Ensure the event listeners pass the entire file list
    fileInput.onchange = () => {
        if (fileInput.files.length) updateUI(fileInput.files);
    };
    // ... also update the ondrop listener similarly

// ... within updateUI function
function updateUI(files) {
    let totalSize = 0;
    let tooLarge = false;

    for (let file of files) {
        totalSize += file.size;
        if (file.size > 2 * 1024 * 1024) { // 2MB
            tooLarge = true;
        }
    }

    if (tooLarge) {
        dropText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> One or more files are over 2MB!</span>`;
        document.getElementById('submitBtn').disabled = true;
    } else {
        dropText.innerHTML = `<span class="text-primary fw-bold">${files.length} files selected</span>`;
        document.getElementById('submitBtn').disabled = false;
    }
}
</script>
@endsection