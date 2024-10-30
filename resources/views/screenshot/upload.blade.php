@extends('layouts.userbase')
@section('title', 'Screenshot Upload')

@section('content')

    <div class="container mt-4">
        <h1 class="display-5 mb-4">Upload a screenshot</h1>

        <p class="lead">You can upload screenshots manually here. If you want to use the API click <a href="">here.</a></p>

        <!-- Info Alert -->
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                Allowed file types: <strong>PNG, JPG, JPEG, GIF</strong>.<br>
                Maximum size: <strong>2MB</strong>.
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error during upload:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <strong>{{ session('success') }}</strong>
                <p>Public link: <a href="{{ session('public_link') }}" target="_blank">{{ session('public_link') }}</a></p>
            </div>
        @endif

        <form action="{{ route('screenshot.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Drag and Drop Zone -->
            <div id="drop-zone" class="d-flex flex-column align-items-center justify-content-center border border-primary rounded py-5 mb-3"
                 style="cursor: pointer; border-style: dashed; background-color: #f8f9fa;">
                <p class="mb-2 text-muted" id="drop-text">Drag & Drop your screenshot here, or click to select</p>
                <i class="bi bi-cloud-upload-fill" id="drop-icon" style="font-size: 2rem; color: #0d6efd;"></i>
                <input type="file" id="image" name="image" accept=".png, .jpg, .jpeg, .gif" class="d-none" required>
            </div>

            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropZone = document.getElementById("drop-zone");
            const fileInput = document.getElementById("image");
            const dropText = document.getElementById("drop-text");
            const dropIcon = document.getElementById("drop-icon");

            // Open file dialog when clicking on drop zone
            dropZone.addEventListener("click", () => fileInput.click());

            // Handle drag-and-drop events
            dropZone.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropZone.classList.add("bg-primary", "text-white");
            });

            dropZone.addEventListener("dragleave", () => {
                dropZone.classList.remove("bg-primary", "text-white");
            });

            dropZone.addEventListener("drop", (e) => {
                e.preventDefault();
                dropZone.classList.remove("bg-primary", "text-white");

                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    showSelectedFile(fileInput.files[0]);
                }
            });

            // Show file name after selecting a file
            fileInput.addEventListener("change", () => {
                if (fileInput.files.length) {
                    showSelectedFile(fileInput.files[0]);
                }
            });

            function showSelectedFile(file) {
                dropText.textContent = `Selected file: ${file.name}`;
                dropIcon.className = "bi bi-check-circle-fill text-success";
                dropZone.classList.add("bg-light");
            }
        });
    </script>

@endsection
