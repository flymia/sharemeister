@extends('layouts.userbase')
@section('title', 'Upload')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold">Upload Screenshot</h1>
            <p class="text-muted">Manual upload for your captures. For automated uploads, use our <a href="https://github.com/flymia/sharemeister/wiki/Using-the-API" class="text-primary text-decoration-none">API Guide</a>.</p>
        </div>

        {{-- No-JS fallback flash (the plain <form> below still works without JavaScript) --}}
        @if(session('success'))
            <div class="card border-success mb-4 shadow-sm">
                <div class="card-body d-flex align-items-center py-3">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <div class="fw-bold">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

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

                {{-- The form is kept for progressive enhancement: if JS is disabled it posts
                     to the classic store() route. With JS on, submit is intercepted and each
                     file is uploaded individually via XHR to screenshot.upload.ajax. --}}
                <form action="{{ route('screenshot.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div id="drop-zone" class="upload-area mb-4">
                        <input type="file" id="image" name="image[]" accept=".png, .jpg, .jpeg, .gif" class="d-none" multiple required>
                        <div class="text-center py-4">
                            <div class="upload-icon mb-3">
                                <i class="bi bi-cloud-arrow-up text-primary display-4"></i>
                            </div>
                            <h5 class="fw-bold" id="drop-text">Drag &amp; drop images here</h5>
                            <p class="text-muted small mb-1">or click to browse, or paste from your clipboard</p>
                            <span class="badge bg-light text-muted border">
                                PNG, JPG, GIF up to {{ $maxSizeKb / 1024 }}MB
                            </span>
                        </div>
                    </div>

                    {{-- Upload queue (JS-managed). Hidden until at least one file is added. --}}
                    <div id="queue-wrapper" class="d-none mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-muted" id="queue-summary">0 files</span>
                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0" id="clear-done-btn">Clear finished</button>
                        </div>
                        <div id="queue-list" class="d-flex flex-column gap-2"></div>
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
        border: 2px dashed var(--bs-border-color);
        border-radius: 1rem;
        transition: all 0.2s ease;
        cursor: pointer;
        background-color: var(--bs-tertiary-bg);
    }

    .upload-area:hover, .upload-area.dragover {
        border-color: var(--bs-primary);
        background-color: var(--bs-primary-bg-subtle);
    }

    .upload-area .badge {
        background-color: var(--bs-secondary-bg) !important;
        color: var(--bs-secondary-color) !important;
        border: 1px solid var(--bs-border-color) !important;
    }

    /* Queue rows */
    .queue-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.75rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-tertiary-bg);
    }
    .queue-thumb {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 0.5rem;
        object-fit: cover;
        background-color: var(--bs-secondary-bg);
    }
    .queue-body { flex: 1 1 auto; min-width: 0; }
    .queue-name {
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .queue-item .progress { height: 6px; margin-top: 0.35rem; }
    .queue-meta { font-size: 0.72rem; }
    .queue-link {
        font-size: 0.72rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        max-width: 100%;
    }
    .queue-action { flex: 0 0 auto; }

    .bi { color: var(--bs-body-color); }
    .text-primary .bi, .btn .bi, .text-success .bi, .text-danger .bi, .text-warning .bi { color: inherit; }
</style>

<script>
(function () {
    const dropZone   = document.getElementById("drop-zone");
    const fileInput  = document.getElementById("image");
    const dropText   = document.getElementById("drop-text");
    const submitBtn  = document.getElementById("submitBtn");
    const form       = document.getElementById("uploadForm");
    const queueWrap  = document.getElementById("queue-wrapper");
    const queueList  = document.getElementById("queue-list");
    const queueSummary = document.getElementById("queue-summary");
    const clearDoneBtn = document.getElementById("clear-done-btn");

    const MAX_SIZE_BYTES = {{ $maxSizeKb }} * 1024;
    const LIMIT_MB       = (MAX_SIZE_BYTES / 1024 / 1024).toFixed(0);
    const UPLOAD_URL     = "{{ route('screenshot.upload.ajax') }}";
    const CSRF_TOKEN     = form.querySelector('input[name="_token"]').value;
    const MAX_CONCURRENT = 3;

    let nextId = 1;
    const items = new Map(); // id -> { id, file, previewUrl, status, xhr, row, els }

    // JS manages the queue and validation, so the native `required` on the empty
    // file input must not block form submission (files live in our queue, not the
    // input). The attribute stays in the HTML for the no-JS fallback path.
    fileInput.removeAttribute("required");

    // ---- File intake (browse, drag-drop, paste) -------------------------------

    dropZone.onclick = () => fileInput.click();

    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add("dragover"); };
    dropZone.ondragleave = () => dropZone.classList.remove("dragover");
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.classList.remove("dragover");
        if (e.dataTransfer.files.length) addFiles(e.dataTransfer.files);
    };

    fileInput.onchange = () => {
        if (fileInput.files.length) addFiles(fileInput.files);
        // Reset so selecting the same file again still fires change.
        fileInput.value = "";
    };

    document.addEventListener("paste", (e) => {
        const files = [];
        for (const item of (e.clipboardData?.items || [])) {
            if (item.type && item.type.startsWith("image/")) {
                const f = item.getAsFile();
                if (f) files.push(f);
            }
        }
        if (files.length) { e.preventDefault(); addFiles(files); }
    });

    function addFiles(fileList) {
        for (const file of fileList) {
            if (!file.type.startsWith("image/")) continue;
            const id = nextId++;
            const item = {
                id,
                file,
                previewUrl: URL.createObjectURL(file),
                status: "pending",
                xhr: null,
            };
            items.set(id, item);
            renderItem(item);

            if (file.size > MAX_SIZE_BYTES) {
                setError(item, `File is over ${LIMIT_MB}MB`);
            }
        }
        queueWrap.classList.remove("d-none");
        updateSummary();
    }

    // ---- Rendering ------------------------------------------------------------

    function renderItem(item) {
        const row = document.createElement("div");
        row.className = "queue-item";
        row.dataset.id = item.id;
        row.innerHTML = `
            <img class="queue-thumb" src="${item.previewUrl}" alt="">
            <div class="queue-body">
                <div class="queue-name">${escapeHtml(item.file.name || "pasted-image")}</div>
                <div class="progress d-none" role="progressbar">
                    <div class="progress-bar" style="width:0%"></div>
                </div>
                <div class="queue-meta text-muted mt-1">${humanSize(item.file.size)}</div>
            </div>
            <div class="queue-action">
                <button type="button" class="btn btn-sm btn-link text-muted p-0 remove-btn" title="Remove">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>`;
        queueList.appendChild(row);

        item.row = row;
        item.els = {
            progress:  row.querySelector(".progress"),
            bar:       row.querySelector(".progress-bar"),
            meta:      row.querySelector(".queue-meta"),
            action:    row.querySelector(".queue-action"),
            removeBtn: row.querySelector(".remove-btn"),
        };

        item.els.removeBtn.addEventListener("click", () => removeItem(item));
    }

    function removeItem(item) {
        if (item.status === "uploading" && item.xhr) item.xhr.abort();
        URL.revokeObjectURL(item.previewUrl);
        item.row.remove();
        items.delete(item.id);
        if (items.size === 0) queueWrap.classList.add("d-none");
        updateSummary();
    }

    function setError(item, message) {
        item.status = "error";
        item.els.progress.classList.add("d-none");
        item.els.meta.className = "queue-meta text-danger mt-1";
        item.els.meta.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i>${escapeHtml(message)}`;
        item.els.action.innerHTML = `
            <button type="button" class="btn btn-sm btn-link text-muted p-0 remove-btn" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>`;
        item.els.action.querySelector(".remove-btn").addEventListener("click", () => removeItem(item));
        updateSummary();
    }

    function setDone(item, link, duplicate) {
        item.status = duplicate ? "duplicate" : "done";
        item.els.progress.classList.add("d-none");
        item.els.meta.className = "queue-meta mt-1 d-flex align-items-center gap-2";
        const badge = duplicate
            ? `<span class="text-warning"><i class="bi bi-arrow-repeat me-1"></i>Already uploaded</span>`
            : `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Done</span>`;
        item.els.meta.innerHTML = `${badge}<a href="${link}" target="_blank" class="queue-link text-primary text-decoration-none">${escapeHtml(link)}</a>`;
        item.els.action.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" title="Copy link">
                <i class="bi bi-clipboard"></i>
            </button>`;
        const copyBtn = item.els.action.querySelector(".copy-btn");
        copyBtn.addEventListener("click", () => copyLink(link, copyBtn));
        updateSummary();
    }

    function copyLink(link, btn) {
        navigator.clipboard?.writeText(link).then(() => {
            btn.innerHTML = `<i class="bi bi-check-lg"></i>`;
            btn.classList.replace("btn-outline-secondary", "btn-success");
            setTimeout(() => {
                btn.innerHTML = `<i class="bi bi-clipboard"></i>`;
                btn.classList.replace("btn-success", "btn-outline-secondary");
            }, 1500);
        });
    }

    // ---- Upload orchestration -------------------------------------------------

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        startUploads();
    });

    function startUploads() {
        const pending = [...items.values()].filter(i => i.status === "pending");
        if (pending.length === 0) return;

        submitBtn.disabled = true;

        let index = 0, active = 0;
        // Concurrency pool: keep up to MAX_CONCURRENT uploads in flight; as each
        // finishes it decrements `active` and pulls the next pending file.
        const pump = () => {
            while (active < MAX_CONCURRENT && index < pending.length) {
                active++;
                uploadItem(pending[index++], () => { active--; pump(); });
            }
            if (active === 0) submitBtn.disabled = false; // all finished
        };
        pump();
    }

    function uploadItem(item, onDone) {
        item.status = "uploading";
        item.els.progress.classList.remove("d-none");
        item.els.bar.style.width = "0%";
        item.els.removeBtn?.setAttribute("disabled", "disabled");

        const data = new FormData();
        data.append("image", item.file, item.file.name || "pasted-image.png");
        data.append("_token", CSRF_TOKEN);

        const xhr = new XMLHttpRequest();
        item.xhr = xhr;
        xhr.open("POST", UPLOAD_URL);
        xhr.setRequestHeader("Accept", "application/json");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                item.els.bar.style.width = Math.round((e.loaded / e.total) * 100) + "%";
            }
        };

        xhr.onload = () => {
            let res = {};
            try { res = JSON.parse(xhr.responseText); } catch (_) {}
            if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                setDone(item, res.public_link, res.duplicate);
            } else {
                setError(item, extractError(res, xhr.status));
            }
            onDone();
        };
        xhr.onerror = () => { setError(item, "Network error"); onDone(); };
        xhr.onabort  = () => { onDone(); };

        xhr.send(data);
    }

    // ---- Helpers --------------------------------------------------------------

    function extractError(res, status) {
        if (res && res.errors && res.errors.image) return res.errors.image[0];
        if (res && res.message) return res.message;
        return `Upload failed (${status})`;
    }

    function updateSummary() {
        const all = [...items.values()];
        const done = all.filter(i => i.status === "done" || i.status === "duplicate").length;
        const uploading = all.some(i => i.status === "uploading");
        queueSummary.textContent = `${done}/${all.length} uploaded`;
        submitBtn.disabled = uploading || all.filter(i => i.status === "pending").length === 0;
    }

    clearDoneBtn.addEventListener("click", () => {
        for (const item of [...items.values()]) {
            if (item.status === "done" || item.status === "duplicate" || item.status === "error") {
                URL.revokeObjectURL(item.previewUrl);
                item.row.remove();
                items.delete(item.id);
            }
        }
        if (items.size === 0) queueWrap.classList.add("d-none");
        updateSummary();
    });

    function humanSize(bytes) {
        if (bytes < 1024) return bytes + " B";
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + " KB";
        return (bytes / 1024 / 1024).toFixed(1) + " MB";
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => (
            { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]
        ));
    }
})();
</script>
@endsection
